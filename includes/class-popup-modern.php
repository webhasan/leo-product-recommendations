<?php
/**
 * Modern popup design (3.6.0).
 *
 * The popup after add to cart, drawn with its own compact product cards instead of the
 * theme's shop cards, so it looks the same in every theme: what was added, the cart
 * subtotal, a row of recommended products and clear Checkout and View cart buttons.
 * On phones it slides up from the bottom.
 *
 * Existing stores keep the classic design until they switch in the Popup tab.
 *
 * @package LoeCoder\Plugin\ProductRecommendations
 * @since   3.6.0
 */

namespace LoeCoder\Plugin\ProductRecommendations;

if (!defined('ABSPATH')) {
	exit;
}

class Popup_Modern {

	/** Heading the classic design shipped with: the modern design uses its own instead. */
	const OLD_DEFAULT_HEADING = 'You may purchase following [item, items] with the %title%';

	/**
	 * Which design the popup uses: modern or classic.
	 *
	 * Stores that saved settings before this option existed keep the classic design.
	 *
	 * @param Product_Recommendations $base
	 */
	public static function design($base) {
		$settings = $base->get_settings();
		if (!empty($settings) && is_array($settings) && !array_key_exists('popup_design', $settings)) {
			$design = 'classic';
		} else {
			$design = 'classic' === $base->get_setting('popup_design') ? 'classic' : 'modern';
		}

		/**
		 * Popup design: modern or classic.
		 *
		 * @since 3.6.0
		 * @param string $design
		 */
		return 'classic' === apply_filters('lpr_popup_design', $design) ? 'classic' : 'modern';
	}

	public static function is_modern($base) {
		return 'modern' === self::design($base);
	}

	/**
	 * Accent color (main button, Add buttons). Pro's button color when set.
	 */
	public static function accent($base) {
		$color = $base->is_pro_activated() ? (string) $base->get_setting('checkout_bg') : '';
		$color = sanitize_hex_color($color) ? $color : '#1d2327';
		return (string) apply_filters('lpr_popup_accent_color', $color);
	}

	/* ---------- readable colors ---------- */

	private static function rgb($hex) {
		$hex = ltrim((string) $hex, '#');
		if (3 === strlen($hex)) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		return 6 === strlen($hex) ? array(hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))) : array(29, 35, 39);
	}

	private static function luminance($rgb) {
		$c = array_map(function ($v) {
			$v = $v / 255;
			return $v <= 0.03928 ? $v / 12.92 : pow(($v + 0.055) / 1.055, 2.4);
		}, $rgb);
		return 0.2126 * $c[0] + 0.7152 * $c[1] + 0.0722 * $c[2];
	}

	public static function contrast($a, $b) {
		$la = self::luminance(self::rgb($a));
		$lb = self::luminance(self::rgb($b));
		return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
	}

	/**
	 * Readable colors for an accent the owner picked:
	 * on  = text on the accent (white, or dark when white would be hard to read),
	 * ink = the accent darkened until it reads well as text on white (Add buttons, links).
	 *
	 * @return array{accent: string, on: string, ink: string}
	 */
	public static function readable($accent) {
		$accent = sanitize_hex_color($accent) ? $accent : '#1d2327';
		$on     = self::contrast($accent, '#ffffff') >= 4.5 ? '#ffffff' : (self::contrast($accent, '#1d2327') >= self::contrast($accent, '#ffffff') ? '#1d2327' : '#ffffff');
		// Mid-tone colours (bright red, for example) reach 4.5:1 only with pure black.
		if (self::contrast($accent, $on) < 4.5 && self::contrast($accent, '#000000') > self::contrast($accent, $on)) {
			$on = '#000000';
		}
		$rgb    = self::rgb($accent);
		$ink    = $accent;
		for ($i = 0; $i < 20 && self::contrast($ink, '#ffffff') < 4.5; $i++) {
			$rgb = array_map(function ($v) {
				return (int) floor($v * 0.88);
			}, $rgb);
			$ink = sprintf('#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
		}
		return array('accent' => $accent, 'on' => $on, 'ink' => $ink);
	}

	/** CSS custom properties for the popup's accent. */
	public static function accent_style($base) {
		$c = self::readable(self::accent($base));
		return '--lpr-pm-accent: ' . $c['accent'] . '; --lpr-pm-on-accent: ' . $c['on'] . '; --lpr-pm-accent-ink: ' . $c['ink'] . ';';
	}

	/* ---------- data ---------- */

	/**
	 * The cart line that was just added for this product (the last one).
	 */
	public static function added_item($product_id) {
		if (!function_exists('WC') || !WC()->cart) {
			return null;
		}
		$found = null;
		foreach (WC()->cart->get_cart() as $item) {
			if ((int) $item['product_id'] === (int) $product_id || (int) $item['variation_id'] === (int) $product_id) {
				$found = $item;
			}
		}
		return $found;
	}

	/**
	 * Heading over the recommended products.
	 */
	public static function heading($base, $product_id, $template_data) {
		$name = wp_strip_all_tags(get_the_title($product_id));

		// The heading the owner set (product or default). The old stock text gets the new wording.
		$raw = (string) $base->get_setting('default_heading');
		$own = isset($template_data['modal_heading']) ? trim(wp_strip_all_tags($template_data['modal_heading'])) : '';
		$old = array(self::OLD_DEFAULT_HEADING, __('You may purchase following [item, items] with the %title%', 'leo-product-recommendations'));
		$is_stock = 'default_heading_description' !== $base->get_setting('heading_type') && ('' === trim($raw) || in_array(trim($raw), $old, true));
		$pr_data  = $base->get_pr_data($product_id);
		$has_own  = !$base->is_active_global($product_id) && !empty($pr_data['heading']) && '' !== trim($pr_data['heading']);

		if ($own && ($has_own || !$is_stock)) {
			return $template_data['modal_heading'];
		}

		/* translators: %s: product name */
		return '<h3 class="lpr-pm__recs-title">' . esc_html(sprintf(__('Goes well with %s', 'leo-product-recommendations'), $name)) . '</h3>';
	}

	/* ---------- output ---------- */

	public static function card($base, $product, $variable_add_to_cart) {
		if (!$product) {
			return '';
		}
		$id    = $product->get_id();
		$link  = $product->get_permalink();
		$image = $product->get_image('woocommerce_thumbnail', array('class' => 'lpr-pm-card__img', 'loading' => 'lazy'));

		$html  = '<li class="lpr-pm-card single-lpr product" data-lpr-product="' . esc_attr($id) . '">';
		$html .= '<a class="lpr-pm-card__media" href="' . esc_url($link) . '" tabindex="-1" aria-hidden="true">' . $image . '</a>';
		$html .= '<a class="lpr-pm-card__name" href="' . esc_url($link) . '">' . esc_html($product->get_name()) . '</a>';
		$html .= '<span class="lpr-pm-card__price price">' . $product->get_price_html() . '</span>';
		$html .= '<div class="lpr-pm-card__action">' . self::button($product, $variable_add_to_cart) . '</div>';
		$html .= '</li>';

		/**
		 * HTML of one product card in the modern popup.
		 *
		 * @since 3.6.0
		 */
		return (string) apply_filters('lpr_popup_modern_card_html', $html, $product);
	}

	private static function button($product, $variable_add_to_cart) {
		$add = __('Add', 'leo-product-recommendations');

		if ($product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock() && !$product->is_sold_individually()) {
			return sprintf(
				'<a href="%1$s" data-quantity="1" data-product_id="%2$d" data-product_sku="%3$s" class="button add_to_cart_button ajax_add_to_cart lpr-pm-card__add" rel="nofollow" aria-label="%4$s">%5$s</a>',
				esc_url($product->add_to_cart_url()),
				$product->get_id(),
				esc_attr($product->get_sku()),
				esc_attr(sprintf(/* translators: %s: product name */ __('Add %s to your cart', 'leo-product-recommendations'), $product->get_name())),
				esc_html($add)
			);
		}

		if ($product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock()) {
			// Sold individually: the AJAX button still works, quantity 1.
			return sprintf(
				'<a href="%1$s" data-quantity="1" data-product_id="%2$d" class="button add_to_cart_button ajax_add_to_cart lpr-pm-card__add" rel="nofollow">%3$s</a>',
				esc_url($product->add_to_cart_url()),
				$product->get_id(),
				esc_html($add)
			);
		}

		if ($variable_add_to_cart && $product->is_type('variable')) {
			$options = '';
			foreach ($product->get_available_variations('objects') as $variation) {
				if (!$variation || !$variation->is_purchasable() || !$variation->is_in_stock()) {
					continue;
				}
				$attributes = $variation->get_variation_attributes(false);
				if (in_array('', $attributes, true)) {
					$options = ''; // "Any" values need the product page.
					break;
				}
				$options .= sprintf(
					'<option value="%1$d">%2$s (%3$s)</option>',
					$variation->get_id(),
					esc_html(wc_get_formatted_variation($variation, true, false, false)),
					esc_html(html_entity_decode(wp_strip_all_tags(wc_price(wc_get_price_to_display($variation))), ENT_QUOTES, get_bloginfo('charset')))
				);
			}
			if ($options) {
				return sprintf(
					'<select class="lpr-pm-card__select" aria-label="%1$s"><option value="">%2$s</option>%3$s</select><a href="%4$s" data-quantity="1" data-product_id="" class="button add_to_cart_button ajax_add_to_cart lpr-pm-card__add is-disabled" rel="nofollow" aria-disabled="true">%5$s</a>',
					esc_attr(sprintf(/* translators: %s: product name */ __('Options for %s', 'leo-product-recommendations'), $product->get_name())),
					esc_html__('Choose an option', 'leo-product-recommendations'),
					$options,
					esc_url($product->get_permalink()),
					esc_html($add)
				);
			}
		}

		$label = $product->is_type('variable') ? __('Choose options', 'leo-product-recommendations') : __('View product', 'leo-product-recommendations');
		return '<a href="' . esc_url($product->get_permalink()) . '" class="lpr-pm-card__add is-link">' . esc_html($label) . '</a>';
	}

	/**
	 * Subtotal and what Pro adds under it (free shipping bar). Refreshed after adds in the popup.
	 */
	public static function summary_html() {
		if (!function_exists('WC') || !WC()->cart) {
			return '';
		}
		ob_start();
		/**
		 * Inside the modern popup, under the added product (Pro: free shipping bar).
		 *
		 * @since 3.6.0
		 */
		do_action('lpr_popup_modern_summary');
		$extra = trim(ob_get_clean());

		$count = WC()->cart->get_cart_contents_count();
		$html  = '<div class="lpr-pm__subtotal"><span>' . esc_html(sprintf(
			/* translators: %d: number of items in the cart */
			_n('Cart subtotal (%d item)', 'Cart subtotal (%d items)', $count, 'leo-product-recommendations'),
			$count
		)) . '</span> <strong>' . wp_kses_post(WC()->cart->get_cart_subtotal()) . '</strong></div>';

		return $html . $extra;
	}

	/**
	 * admin-ajax lpr_popup_summary: the summary after the cart changed.
	 */
	public static function ajax_summary() {
		$nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '';
		if (!$nonce || !wp_verify_nonce($nonce, 'lc-ajax-modal')) {
			wp_send_json_error(array('message' => 'Bad request'), 400);
		}
		if (WC()->cart) {
			WC()->cart->calculate_totals();
		}
		nocache_headers();
		wp_send_json_success(array(
			'html'  => self::summary_html(),
			'count' => WC()->cart ? WC()->cart->get_cart_contents_count() : 0,
		));
	}
}
