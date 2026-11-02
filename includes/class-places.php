<?php
/**
 * More places for recommendations (beside the popup).
 *
 * The code for the cart page ("Complete your order") and the shared helpers live here;
 * all places are a Pro feature, so nothing shows unless Pro is active.
 *
 * Each place prints a .lpr-place block with the theme's own product cards, plus a
 * JSON data block for the GA4 dataLayer events and the Pro analytics.
 *
 * @package LoeCoder\Plugin\ProductRecommendations
 * @since   3.6.0
 */

namespace LoeCoder\Plugin\ProductRecommendations;

if (!defined('ABSPATH')) {
	exit;
}

class Places {

	/**
	 * @var Product_Recommendations
	 */
	private $base;

	/**
	 * @var bool
	 */
	private $printed_cart = false;

	public function __construct($base) {
		$this->base = $base;

		add_action('wp_enqueue_scripts', array($this, 'enqueue'), 15);

		// Classic cart: under the cart table, inside the cart form, so WooCommerce refreshes
		// it together with the cart after every change.
		add_action('woocommerce_after_cart_table', array($this, 'cart_classic'));

		// Classic cart, list layout: beside the cart totals, in the slot WooCommerce keeps
		// for its own cross-sells.
		add_action('woocommerce_cart_collaterals', array($this, 'cart_classic_side'), 5);

		// Cart block: right after the block (the list layout is moved under the cart items by places.js).
		add_filter('render_block_woocommerce/cart', array($this, 'cart_block'), 10, 2);

		// WooCommerce's own "You may be interested in..." cross-sells would show next to ours.
		add_action('wp', array($this, 'hide_wc_cross_sells'));
		add_filter('render_block_woocommerce/cart-cross-sells-block', array($this, 'hide_wc_cross_sells_block'), 10, 2);

		// The list layout reloads itself after an add, so the next product moves up.
		add_action('wc_ajax_lpr_cart_recs', array($this, 'ajax_cart_recs'));

		// Products the shopper removed are not suggested again in this visit.
		add_action('woocommerce_cart_item_removed', array($this, 'remember_removed'), 10, 2);
		add_action('woocommerce_cart_item_restored', array($this, 'forget_restored'), 10, 2);
		add_action('woocommerce_cart_emptied', array($this, 'forget_all_removed'));
	}

	/**
	 * Settings fields for the cart page. Pro adds them to its More Places tab.
	 *
	 * @return array
	 */
	public static function cart_settings_fields() {
		return array(
			array(
				'id' => 'cart_recs',
				'group' => 'cart',
				'title' => __('Cart Page', 'leo-product-recommendations'),
				'label' => __('Show recommendations on the cart page', 'leo-product-recommendations'),
				'type' => 'checkbox',
				'switch' => true,
				'default' => 1,
				'description' => __('Products that go with everything in the cart. Products already in the cart are left out, and products removed during the visit come last. Replaces WooCommerce\'s own "You may be interested in..." list on the cart page.', 'leo-product-recommendations'),
			),
			array(
				'id' => 'cart_recs_layout',
				'group' => 'cart',
				'title' => __('Layout', 'leo-product-recommendations'),
				'type' => 'radio',
				'options' => array(
					'list' => __('Compact list', 'leo-product-recommendations'),
					'grid' => __('Grid', 'leo-product-recommendations'),
					'slider' => __('Slider', 'leo-product-recommendations'),
				),
				'default' => 'list',
				'description' => __('Compact list shows short rows next to the cart totals (under the cart items in the cart block and on phones), so the checkout button stays in view. Grid and Slider show your theme\'s product cards under the cart table.', 'leo-product-recommendations'),
			),
			array(
				'id' => 'cart_recs_heading',
				'group' => 'cart',
				'title' => __('Heading', 'leo-product-recommendations'),
				'type' => 'text',
				'default' => '',
				'description' => __('Leave empty for "Complete your order".', 'leo-product-recommendations'),
			),
			array(
				'id' => 'cart_recs_number',
				'group' => 'cart',
				'title' => __('Number of Products', 'leo-product-recommendations'),
				'type' => 'stepper',
				'min' => 2,
				'max' => 8,
				'default' => 4,
				'description' => __('The most to show. Fewer show when the cart already holds the other matches. Grid: up to 4 in a row, more wrap to a second row. Slider: 4 in view, the rest one swipe away.', 'leo-product-recommendations'),
			),
		);
	}

	public function cart_enabled() {
		/**
		 * Turn the cart page recommendations on or off in code.
		 *
		 * @since 3.6.0
		 * @param bool $enabled
		 */
		// More Places, the cart page included, is a Pro feature. The helpers here are shared with Pro.
		$enabled = $this->base->is_pro_activated() && (bool) $this->base->get_setting('cart_recs');
		return (bool) apply_filters('lpr_cart_recommendations_enabled', $enabled);
	}

	/**
	 * Script and styles where a place can show.
	 */
	public function enqueue() {
		if (!function_exists('is_cart')) {
			return;
		}

		/**
		 * Load the places script on this page. Pro turns it on for its own places.
		 *
		 * @since 3.6.0
		 * @param bool $load
		 */
		$load = apply_filters('lpr_places_load_assets', $this->cart_enabled() && $this->base->is_cart_or_checkout_page() && !is_checkout());

		if (!$load) {
			return;
		}

		$version = $this->base->script_version();
		$ver     = $version . '.' . (int) @filemtime($this->base->get_path('assets/js/places.js'));

		wp_enqueue_style('lpr-places', $this->base->get_url('assets/css/places.css'), array(), $version . '.' . (int) @filemtime($this->base->get_path('assets/css/places.css')));
		wp_enqueue_script('wc-add-to-cart');
		wp_enqueue_script('lpr-variation-picker', $this->base->get_url('assets/js/variation-picker.js'), array('jquery'), $version . '.' . (int) @filemtime($this->base->get_path('assets/js/variation-picker.js')), true);
		wp_enqueue_script('lpr-places', $this->base->get_url('assets/js/places.js'), array('jquery', 'wc-add-to-cart', 'lpr-variation-picker'), $ver, true);
		wp_localize_script('lpr-places', 'lprPlaces', array(
			'datalayer' => $this->base->datalayer_enabled(),
			'added'     => __('Added', 'leo-product-recommendations'),
		));
	}

	/* ---------- cart page ---------- */

	/**
	 * Cart page layout: list, grid or slider.
	 *
	 * @return string
	 */
	public function cart_layout() {
		$layout = (string) $this->base->get_setting('cart_recs_layout');

		// Stores that saved their settings before the list layout (3.7.1) keep the grid.
		$settings = get_option('lc_lpr_settings');
		if (is_array($settings) && $settings && !isset($settings['cart_recs_layout'])) {
			$layout = 'grid';
		}

		$layout = in_array($layout, array('list', 'grid', 'slider'), true) ? $layout : 'list';

		/**
		 * Grid, slider or list for a place.
		 *
		 * @since 3.6.0
		 * @param string $layout grid|slider|list
		 * @param array  $args   The render arguments (here only the place).
		 */
		return (string) apply_filters('lpr_place_layout', $layout, array('place' => 'cart'));
	}

	/**
	 * Our cart recommendations replace WooCommerce's cross-sells on the cart page, so the
	 * shopper sees one list (ours include the cross-sells when "linked products" is on).
	 * Filter lpr_cart_hide_wc_cross_sells to keep both.
	 */
	private function hiding_wc_cross_sells() {
		/**
		 * Hide WooCommerce's "You may be interested in..." cross-sells on the cart page while
		 * the cart page recommendations are on.
		 *
		 * @since 3.7.1
		 * @param bool $hide
		 */
		return $this->cart_enabled() && (bool) apply_filters('lpr_cart_hide_wc_cross_sells', true);
	}

	public function hide_wc_cross_sells() {
		if (function_exists('is_cart') && is_cart() && $this->hiding_wc_cross_sells()) {
			remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display');
		}
	}

	public function hide_wc_cross_sells_block($content, $block) {
		return !is_admin() && $this->hiding_wc_cross_sells() ? '' : $content;
	}

	public function cart_classic() {
		if ($this->printed_cart || !$this->cart_enabled() || 'list' === $this->cart_layout()) {
			return;
		}

		$this->printed_cart = true;
		$this->render_cart();
	}

	public function cart_classic_side() {
		if ($this->printed_cart || !$this->cart_enabled() || 'list' !== $this->cart_layout()) {
			return;
		}

		$this->printed_cart = true;
		$this->render_cart();
	}

	/**
	 * The list layout again, after an add or a cart change (wc-ajax=lpr_cart_recs).
	 */
	public function ajax_cart_recs() {
		$html = '';

		if ($this->cart_enabled() && 'list' === $this->cart_layout()) {
			ob_start();
			$this->render_cart(!empty($_GET['block'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read only, the shopper's own cart.
			$html = trim((string) ob_get_clean());
		}

		nocache_headers();
		wp_send_json_success(array('html' => $html));
	}

	/* ---------- removed products ---------- */

	const REMOVED = 'lpr_removed_products';

	/**
	 * @param string   $key
	 * @param \WC_Cart $cart
	 */
	public function remember_removed($key, $cart) {
		if (!function_exists('WC') || !WC()->session || !$cart || empty($cart->removed_cart_contents[$key]['product_id'])) {
			return;
		}
		$ids   = array_map('absint', (array) WC()->session->get(self::REMOVED, array()));
		$ids[] = (int) $cart->removed_cart_contents[$key]['product_id'];
		WC()->session->set(self::REMOVED, array_slice(array_values(array_unique($ids)), -30));
	}

	/**
	 * Undo: the product can be suggested again.
	 *
	 * @param string   $key
	 * @param \WC_Cart $cart
	 */
	public function forget_restored($key, $cart) {
		if (!function_exists('WC') || !WC()->session || !$cart) {
			return;
		}
		$item = $cart->get_cart_item($key);
		if (empty($item['product_id'])) {
			return;
		}
		$ids = array_map('absint', (array) WC()->session->get(self::REMOVED, array()));
		WC()->session->set(self::REMOVED, array_values(array_diff($ids, array((int) $item['product_id']))));
	}

	public function forget_all_removed() {
		if (function_exists('WC') && WC()->session) {
			WC()->session->set(self::REMOVED, null);
		}
	}

	/**
	 * Products removed from the cart in this visit.
	 *
	 * @return int[]
	 */
	public static function removed_product_ids() {
		if (!function_exists('WC') || !WC()->session) {
			return array();
		}
		return array_values(array_filter(array_map('absint', (array) WC()->session->get(self::REMOVED, array()))));
	}

	public function cart_block($content, $block) {
		if ($this->printed_cart || !$this->cart_enabled() || is_admin() || wp_is_json_request()) {
			return $content;
		}

		ob_start();
		$this->render_cart(true);
		$html = ob_get_clean();

		if ('' === $html) {
			return $content;
		}

		$this->printed_cart = true;

		return $content . $html;
	}

	private function render_cart($block = false) {
		if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
			return;
		}

		$layout = $this->cart_layout();
		$number = absint($this->base->get_setting('cart_recs_number'));
		$number = $number ? min(8, max(2, $number)) : 4;

		$cart_ids = self::cart_product_ids();
		$removed  = self::removed_product_ids();
		$sources  = count($cart_ids) > 6 ? 20 : 6;

		// Products removed in this visit go last, not out: a shopper who cleared most of a
		// big cart should still see recommendations. A big cart can also hold most picks of
		// its 6 newest products, so the older ones are asked too.
		$map = $this->recommendations_for($cart_ids, $number, array('exclude' => $removed, 'sources' => $sources)); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- an option of recommendations_for(), not a WP_Query argument.
		if (count($map) < $number && $removed) {
			foreach ($this->recommendations_for($cart_ids, $number, array('sources' => $sources)) as $id => $source) {
				if (count($map) >= $number) {
					break;
				}
				if (!isset($map[$id])) {
					$map[$id] = $source;
				}
			}
		}

		if (!$map) {
			return;
		}

		$heading = trim(wp_strip_all_tags((string) $this->base->get_setting('cart_recs_heading')));
		$heading = '' !== $heading ? $heading : __('Complete your order', 'leo-product-recommendations');

		if ('list' === $layout) {
			$this->render_list(array(
				'place'     => 'cart',
				'list_id'   => 'lpr_cart',
				'list_name' => 'Cart page recommendations',
				'heading'   => $heading,
				'map'       => $map,
				'class'     => $block ? 'lpr-place--block' : '',
				'refresh'   => \WC_AJAX::get_endpoint('lpr_cart_recs'),
			));
			return;
		}

		$this->render(array(
			'place'     => 'cart',
			'list_id'   => 'lpr_cart',
			'list_name' => 'Cart page recommendations',
			'heading'   => $heading,
			'map'       => $map,
			'columns'   => 4, // fewer products keep the card size, they don't stretch
			'layout'    => $this->base->get_setting('cart_recs_layout'),
			'class'     => $block ? 'lpr-place--block' : '',
		));
	}

	/**
	 * Parent product IDs in the cart, the most recently added first.
	 *
	 * @return int[]
	 */
	public static function cart_product_ids() {
		$ids = array();

		if (function_exists('WC') && WC()->cart) {
			foreach (array_reverse(WC()->cart->get_cart()) as $item) {
				$ids[] = (int) $item['product_id'];
			}
		}

		return array_values(array_unique($ids));
	}

	/* ---------- shared helpers ---------- */

	/**
	 * Recommendations for several products together. Each product's list votes for
	 * its picks (a higher place in the list counts more), so a product recommended
	 * for three cart items beats one recommended for one. Ties go to the more
	 * important source product.
	 *
	 * @param int[] $source_ids Products to recommend for, most important first.
	 * @param int   $limit
	 * @param array $args       exclude (int[]), max_price (float), simple_only (bool), sources (max products to use).
	 * @return array recommended product ID => the product it was recommended for
	 */
	public function recommendations_for($source_ids, $limit, $args = array()) {
		$args = wp_parse_args($args, array(
			'exclude'     => array(), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- an option of this method, not a WP_Query argument.
			'max_price'   => 0,
			'simple_only' => false,
			'sources'     => 6,
		));

		$source_ids = array_slice(array_values(array_unique(array_filter(array_map('absint', (array) $source_ids)))), 0, max(1, (int) $args['sources']));
		$exclude    = array_map('absint', array_merge($source_ids, self::cart_product_ids(), (array) $args['exclude']));

		// Reciprocal rank fusion: score += 1 / (K + rank) for every list the product is in.
		$k      = 2;
		$scores = array();
		$best   = array(); // product => array(source, its share of the score)
		$first  = array(); // product => position of its first appearance, for stable ties

		foreach ($source_ids as $order => $source) {
			$ids  = array_values(array_unique(array_map('absint', (array) $this->base->get_recommended_products_id($source))));
			$rank = 0;
			foreach ($ids as $id) {
				if (!$id || in_array($id, $exclude, true)) {
					continue;
				}
				$share = 1 / ($k + $rank);
				$rank++;

				$scores[$id] = (isset($scores[$id]) ? $scores[$id] : 0) + $share;
				if (!isset($best[$id]) || $share > $best[$id][1]) {
					$best[$id] = array($source, $share);
				}
				if (!isset($first[$id])) {
					$first[$id] = $order * 1000 + $rank;
				}
			}
		}

		uksort($scores, function ($a, $b) use ($scores, $first) {
			if (abs($scores[$a] - $scores[$b]) > 1e-9) {
				return $scores[$a] < $scores[$b] ? 1 : -1;
			}
			return $first[$a] - $first[$b];
		});

		$map = array();
		foreach (array_keys($scores) as $id) {
			if (!$this->can_show($id, $args)) {
				continue;
			}
			$map[$id] = $best[$id][0];
			if (count($map) >= $limit) {
				break;
			}
		}

		/**
		 * Filters recommendations made for several products together (cart, checkout, thank-you page).
		 *
		 * @since 3.6.0
		 * @param array $map        Recommended product ID => source product ID.
		 * @param int[] $source_ids
		 * @param array $args
		 */
		return (array) apply_filters('lpr_place_recommendations', $map, $source_ids, $args);
	}

	private function can_show($id, $args) {
		$product = wc_get_product($id);

		if (!$product || !$product->is_visible() || !$product->is_in_stock() || !$this->base->is_public_product($id)) {
			return false;
		}

		if (!empty($args['simple_only']) && !($product->is_type('simple') && $product->is_purchasable())) {
			return false;
		}

		if (!empty($args['max_price']) && (float) wc_get_price_to_display($product) > (float) $args['max_price']) {
			return false;
		}

		return true;
	}

	/**
	 * Print a place as a compact list: small image, name, price and an Add button per row.
	 *
	 * @param array $args place, list_id, list_name, heading, map, class, refresh
	 */
	public function render_list($args) {
		$args = wp_parse_args($args, array(
			'place'     => '',
			'list_id'   => '',
			'list_name' => '',
			'heading'   => '',
			'map'       => array(),
			'class'     => '',
			'refresh'   => '',
		));

		if (empty($args['map'])) {
			return;
		}

		$rows = '';
		foreach ($args['map'] as $id => $source) {
			$product = wc_get_product($id);
			if (!$product) {
				continue;
			}
			$link = $product->get_permalink();

			$row  = sprintf('<li class="lpr-list__item" data-lpr-product="%d" data-lpr-source="%d">', (int) $id, (int) $source);
			$row .= '<a class="lpr-list__media" href="' . esc_url($link) . '" tabindex="-1" aria-hidden="true">' . $product->get_image('woocommerce_gallery_thumbnail', array('class' => 'lpr-list__img', 'loading' => 'lazy', 'alt' => '')) . '</a>';
			$row .= '<div class="lpr-list__info">';
			$row .= '<a class="lpr-list__name" href="' . esc_url($link) . '">' . esc_html($product->get_name()) . '</a>';
			$row .= '<span class="lpr-list__price price">' . wp_kses_post($product->get_price_html()) . '</span>';
			$row .= '</div>';
			$row .= '<div class="lpr-list__action">' . self::list_button($product) . '</div>';
			// Option menus on their own line under the name, as wide as the row allows.
			$row .= self::list_select($product);
			$row .= '</li>';

			/**
			 * HTML of one row in a compact list (cart page).
			 *
			 * @since 3.7.1
			 * @param string      $row
			 * @param \WC_Product $product
			 * @param array       $args
			 */
			$rows .= (string) apply_filters('lpr_place_list_row_html', $row, $product, $args);
		}

		if ('' === $rows) {
			return;
		}

		printf(
			'<section class="lpr-place lpr-place--%1$s lpr-place--list %2$s" data-lpr-place="%1$s" data-lpr-layout="list" data-lpr-refresh="%3$s" aria-label="%4$s" style="%5$s">',
			esc_attr($args['place']),
			esc_attr($args['class']),
			esc_url($args['refresh']),
			esc_attr($args['heading']),
			esc_attr(Popup_Modern::accent_style($this->base))
		);
		echo '<h2 class="lpr-place__title">' . esc_html($args['heading']) . '</h2>';
		echo '<ul class="lpr-list">' . $rows . '</ul>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.

		if ($this->base->datalayer_enabled()) {
			$this->base->print_datalayer_data(reset($args['map']), array_keys($args['map']), array(
				'id'      => $args['list_id'],
				'name'    => $args['list_name'],
				'sources' => $args['map'],
			));
		}

		echo '</section>';
	}

	/**
	 * Option menus for a variable product whose variations can be added from a list.
	 *
	 * @param \WC_Product $product
	 * @return string
	 */
	private static function list_select($product) {
		static $cache = array();
		$id = $product->get_id();
		if (!isset($cache[$id])) {
			$cache[$id] = Popup_Modern::variation_picker($product, 'lpr-list__select');
		}
		return $cache[$id];
	}

	/**
	 * Add button of a list row. WooCommerce's AJAX add to cart handles the click.
	 *
	 * @param \WC_Product $product
	 * @return string
	 */
	private static function list_button($product) {
		$add = __('Add', 'leo-product-recommendations');

		if ($product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock()) {
			return sprintf(
				'<a href="%1$s" data-quantity="1" data-product_id="%2$d" data-product_sku="%3$s" class="add_to_cart_button ajax_add_to_cart lpr-list__add" rel="nofollow" aria-label="%4$s"><span>%5$s</span></a>',
				esc_url($product->add_to_cart_url()),
				$product->get_id(),
				esc_attr($product->get_sku()),
				esc_attr(sprintf(/* translators: %s: product name */ __('Add %s to your cart', 'leo-product-recommendations'), $product->get_name())),
				esc_html($add)
			);
		}

		if ('' !== self::list_select($product)) {
			return sprintf(
				'<a href="%1$s" data-quantity="1" data-product_id="" class="add_to_cart_button ajax_add_to_cart lpr-list__add is-disabled" rel="nofollow" aria-disabled="true" aria-label="%2$s"><span>%3$s</span></a>',
				esc_url($product->get_permalink()),
				esc_attr(sprintf(/* translators: %s: product name */ __('Add %s to your cart', 'leo-product-recommendations'), $product->get_name())),
				esc_html($add)
			);
		}

		$label = $product->is_type('variable') ? __('Choose options', 'leo-product-recommendations') : __('View', 'leo-product-recommendations');
		return '<a href="' . esc_url($product->get_permalink()) . '" class="lpr-list__add is-link">' . esc_html($label) . '</a>';
	}

	/**
	 * Print a place with the theme's product cards.
	 *
	 * @param array $args place, list_id, list_name, heading, map, columns, class, intro
	 */
	public function render($args) {
		$args = wp_parse_args($args, array(
			'place'     => '',
			'list_id'   => '',
			'list_name' => '',
			'heading'   => '',
			'intro'     => '',
			'map'       => array(),
			'columns'   => 4,
			'layout'    => 'grid',
			'class'     => '',
		));

		if (empty($args['map'])) {
			return;
		}

		/**
		 * Grid or slider for a place.
		 *
		 * @since 3.6.0
		 * @param string $layout grid|slider
		 * @param array  $args   The render arguments.
		 */
		$layout = apply_filters('lpr_place_layout', 'slider' === $args['layout'] ? 'slider' : 'grid', $args);
		$slider = 'slider' === $layout;

		global $product, $post;
		$original_product = $product;
		$original_post    = $post;

		$ids = array_keys($args['map']);

		printf(
			'<section class="lpr-place lpr-place--%1$s lpr-place--%4$s %2$s" data-lpr-place="%1$s" data-lpr-layout="%4$s" aria-label="%3$s"%5$s>',
			esc_attr($args['place']),
			esc_attr($args['class']),
			esc_attr($args['heading']),
			esc_attr($layout),
			'' // the slider shows 4 at a time (CSS), however many products there are
		);

		if ($slider) {
			$arrow = '<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
			echo '<div class="lpr-place__head">';
			echo '<h2 class="lpr-place__title">' . esc_html($args['heading']) . '</h2>';
			echo '<div class="lpr-place__nav" hidden>';
			echo '<button type="button" class="lpr-place__prev" aria-label="' . esc_attr__('Previous products', 'leo-product-recommendations') . '">' . $arrow . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
			echo '<button type="button" class="lpr-place__next" aria-label="' . esc_attr__('Next products', 'leo-product-recommendations') . '">' . $arrow . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG.
			echo '</div></div>';
		} else {
			echo '<h2 class="lpr-place__title">' . esc_html($args['heading']) . '</h2>';
		}

		if ($args['intro']) {
			echo '<p class="lpr-place__intro">' . esc_html($args['intro']) . '</p>';
		}

		echo '<div class="woocommerce lpr-place__products">';

		wc_setup_loop(array(
			'name'         => 'lpr_' . $args['place'],
			'columns'      => max(1, (int) $args['columns']),
			'is_shortcode' => true,
			'total'        => count($ids),
		));

		woocommerce_product_loop_start();

		foreach ($ids as $id) {
			$post = get_post($id); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			if (!$post) {
				continue;
			}
			setup_postdata($post);
			$product = wc_get_product($id); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

			ob_start();
			wc_get_template_part('content', 'product');
			$card = ob_get_clean();

			// Which product each card was recommended for (analytics, dataLayer).
			$card = preg_replace(
				'/<li\b/',
				sprintf('<li data-lpr-product="%d" data-lpr-source="%d"', (int) $id, (int) $args['map'][$id]),
				$card,
				1
			);

			echo $card; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce template output.
		}

		woocommerce_product_loop_end();
		wc_reset_loop();

		echo '</div>';

		if ($this->base->datalayer_enabled()) {
			$this->base->print_datalayer_data(reset($args['map']), $ids, array(
				'id'      => $args['list_id'],
				'name'    => $args['list_name'],
				'sources' => $args['map'],
			));
		}

		echo '</section>';

		$post    = $original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$product = $original_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		if ($original_post) {
			setup_postdata($original_post);
		} else {
			wp_reset_postdata();
		}
	}
}
