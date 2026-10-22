<?php
/**
 * Simple settings and live preview on the LPR Settings page.
 *
 * Nothing here runs on the store front end.
 *
 * @package LoeCoder\Plugin\ProductRecommendations
 * @since   3.6.0
 */

namespace LoeCoder\Plugin\ProductRecommendations;

if (!defined('ABSPATH')) {
	exit;
}

class Settings_UI {

	/**
	 * @var Product_Recommendations
	 */
	private $base;

	public function __construct($base) {
		$this->base = $base;

		add_action('admin_enqueue_scripts', array($this, 'enqueue'), 5);
		add_action('wp_ajax_lpr_preview_products', array($this, 'preview_products'));
		add_action('wp_ajax_lpr_search_products', array($this, 'search_products'));
	}

	public function enqueue() {
		$screen = function_exists('get_current_screen') ? get_current_screen() : null;

		// The Pro license page uses the same card look (styles only).
		if ($screen && 'lpr-settings_page_lpr-license' === $screen->id) {
			wp_enqueue_style('lpr-settings-ui', $this->base->get_url('assets/css/settings-ui.css'), array(), $this->base->script_version() . '.' . (int) @filemtime($this->base->get_path('assets/css/settings-ui.css')));
			return;
		}

		if (!$screen || 'toplevel_page_lpr-settings' !== $screen->id) {
			return;
		}

		$version = $this->base->script_version();
		$css_ver = $version . '.' . (int) @filemtime($this->base->get_path('assets/css/settings-ui.css'));
		$js_ver  = $version . '.' . (int) @filemtime($this->base->get_path('assets/js/settings-ui.js'));

		wp_enqueue_style('lpr-settings-ui', $this->base->get_url('assets/css/settings-ui.css'), array(), $css_ver);
		wp_register_script('lpr-settings-ui', $this->base->get_url('assets/js/settings-ui.js'), array('jquery', 'wp-i18n'), $js_ver, true);
		wp_set_script_translations('lpr-settings-ui', 'leo-product-recommendations');

		$sections = array();
		foreach ($this->base->settings_pages() as $page) {
			if (!empty($page['sections']) && empty($page['hidden'])) {
				foreach ($page['sections'] as $section) {
					if (empty($section['template']) && array_key_exists('fields', $section) && empty($section['fields'])) {
						continue; // empty tab, not shown
					}
					$sections[] = $section['id'];
				}
				break;
			}
		}

		wp_localize_script('lpr-settings-ui', 'lprSettingsUI', array(
			'optionName'      => $this->base->get_settings_id(),
			'ajaxUrl'         => admin_url('admin-ajax.php'),
			'nonce'           => wp_create_nonce('lpr-preview-products'),
			'firstSection'    => $sections ? $sections[0] : 'lpr-general-settings',
			'section'         => $this->active_section($sections),
			'previewSections' => array_values(array_intersect(array('lpr-general-settings', 'lpr-style-settings', 'lpr-drawer-settings', 'lpr-places-settings'), $sections)),
			'drawer'          => in_array('lpr-drawer-settings', $sections, true),
			'testUrl'         => $this->test_url(),
			'currency'        => array(
				'symbol'   => html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, get_bloginfo('charset')),
				'format'   => html_entity_decode(get_woocommerce_price_format(), ENT_QUOTES, get_bloginfo('charset')),
				'decimals' => wc_get_price_decimals(),
				'decimal'  => wc_get_price_decimal_separator(),
				'thousand' => wc_get_price_thousand_separator(),
			),
		));

		wp_enqueue_script('lpr-settings-ui');

		// The settings script (editors, color pickers) starts after the preview layout is in place.
		$settings = wp_scripts()->query('lpr-settings', 'registered');
		if ($settings && !in_array('lpr-settings-ui', $settings->deps, true)) {
			$settings->deps[] = 'lpr-settings-ui';
		}
	}

	private function active_section($sections) {
		$sec = isset($_GET['sec']) ? sanitize_key(wp_unslash($_GET['sec'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return in_array($sec, $sections, true) ? $sec : ($sections ? $sections[0] : '');
	}

	private function test_url() {
		$ids = $this->sample_ids(1);
		return $ids ? get_permalink($ids[0]) : '';
	}

	private function sample_ids($limit) {
		if (!function_exists('wc_get_products')) {
			return array();
		}

		return wc_get_products(array(
			'status'       => 'publish',
			'visibility'   => 'catalog',
			'stock_status' => 'instock',
			'limit'        => $limit,
			'orderby'      => 'date',
			'order'        => 'DESC',
			'return'       => 'ids',
		));
	}

	/**
	 * Product search for the store-wide product picker (select2 format).
	 */
	public function search_products() {
		if (!current_user_can('manage_options') || !check_ajax_referer('lpr-preview-products', 'nonce', false)) {
			wp_send_json_error(null, 403);
		}

		$term = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$ids  = get_posts(array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			's'              => $term,
			'posts_per_page' => 20,
			'fields'         => 'ids',
			'orderby'        => $term ? 'relevance' : 'title',
			'order'          => 'ASC',
		));

		$results = array();
		foreach ($ids as $id) {
			$product = wc_get_product($id);
			if ($product) {
				$image     = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
				$results[] = array(
					'id'    => $id,
					'text'  => wp_strip_all_tags($product->get_name()),
					'image' => $image ? esc_url_raw($image) : wc_placeholder_img_src('thumbnail'),
				);
			}
		}

		wp_send_json(array('results' => $results));
	}

	/**
	 * Products for the preview: name, price and image of a few products from the store.
	 */
	public function preview_products() {
		if (!current_user_can('manage_options') || !check_ajax_referer('lpr-preview-products', 'nonce', false)) {
			wp_send_json_error(null, 403);
		}

		$out = array();
		foreach ($this->sample_ids(9) as $id) {
			$product = wc_get_product($id);
			if (!$product) {
				continue;
			}

			$image = wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_thumbnail');

			$out[] = array(
				'name'  => html_entity_decode(wp_strip_all_tags($product->get_name()), ENT_QUOTES, get_bloginfo('charset')),
				'price' => wp_kses_post($product->get_price_html()),
				'image' => $image ? esc_url_raw($image) : '',
				'amount' => (float) wc_get_price_to_display($product),
			);
		}

		wp_send_json_success($out);
	}
}
