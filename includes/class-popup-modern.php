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
		$hex = (string) $hex;
		if (preg_match('/^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})/i', $hex, $m)) {
			return array(min(255, (int) $m[1]), min(255, (int) $m[2]), min(255, (int) $m[3]));
		}
		$hex = ltrim($hex, '#');
		if (8 === strlen($hex) || 4 === strlen($hex)) {
			$hex = substr($hex, 0, 4 === strlen($hex) ? 3 : 6);
		}
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
	public static function readable($accent, $bg = '#ffffff') {
		$accent = self::hex($accent) ? self::hex($accent) : '#1d2327';
		$bg     = self::hex($bg) ? self::hex($bg) : '#ffffff';
		$on     = self::contrast($accent, '#ffffff') >= 4.5 ? '#ffffff' : (self::contrast($accent, '#1d2327') >= self::contrast($accent, '#ffffff') ? '#1d2327' : '#ffffff');
		// Mid-tone colours (bright red, for example) reach 4.5:1 only with pure black.
		if (self::contrast($accent, $on) < 4.5 && self::contrast($accent, '#000000') > self::contrast($accent, $on)) {
			$on = '#000000';
		}
		// On a dark popup the accent gets lighter instead of darker.
		$dark   = self::luminance(self::rgb($bg)) < 0.18;
		$rgb    = self::rgb($accent);
		$ink    = $accent;
		for ($i = 0; $i < 20 && self::contrast($ink, $bg) < 4.5; $i++) {
			$rgb = array_map(function ($v) use ($dark) {
				return $dark ? (int) ceil($v + (255 - $v) * 0.15) : (int) floor($v * 0.88);
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

	/** A color as #rrggbb (rgb() and rgba() too, the alpha left out), or '' when it isn't one. */
	public static function hex($color) {
		$color = trim((string) $color);
		if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $color) || preg_match('/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*[\d.]+%?\s*)?\)$/i', $color)) {
			$rgb = self::rgb($color);
			return sprintf('#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
		}
		return '';
	}

	/** A color that is safe to print in CSS (hex, rgb(), rgba()), or ''. */
	public static function css_color($color) {
		$color = trim((string) $color);
		if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $color)) {
			return $color;
		}
		if (preg_match('/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*(0|1|0?\.\d+|1\.0+|\d{1,3}%)\s*)?\)$/i', $color)) {
			return $color;
		}
		return '';
	}

	/**
	 * The popup colors the owner set (Pro, "All Popup Colors" for the modern design).
	 * Empty ones keep the built-in look.
	 *
	 * @return array<string, string> setting id => color
	 */
	public static function colors($base) {
		$ids = array('pm_bg_color', 'pm_text_color', 'pm_muted_color', 'pm_line_color', 'pm_check_color', 'pm_save_color', 'pm_add_color', 'pm_cart_bg', 'pm_cart_text', 'pm_checkout_text', 'pm_close_color', 'pm_nav_color', 'pm_image_bg', 'pm_overlay_color');
		$out = array();
		if ($base->is_pro_activated()) {
			foreach ($ids as $id) {
				$out[$id] = self::css_color($base->get_setting($id));
			}
		}

		/**
		 * Modern popup colors. Empty values keep the built-in look.
		 *
		 * @since 3.7.1
		 * @param array $colors setting id => CSS color
		 */
		$out = (array) apply_filters('lpr_popup_modern_colors', $out);
		foreach ($ids as $id) {
			$out[$id] = isset($out[$id]) ? self::css_color($out[$id]) : '';
		}
		return $out;
	}

	/** CSS custom properties for the whole popup: the accent plus the colors the owner set. */
	public static function popup_style($base) {
		$c   = self::colors($base);
		$bg  = $c['pm_bg_color'] ? $c['pm_bg_color'] : '#ffffff';
		$acc = self::readable(self::accent($base), $bg);
		$add = $c['pm_add_color'] ? self::readable($c['pm_add_color'], $bg) : $acc;

		$vars = array(
			'--lpr-pm-accent'    => $acc['accent'],
			'--lpr-pm-on-accent' => $c['pm_checkout_text'] ? $c['pm_checkout_text'] : $acc['on'],
			'--lpr-pm-accent-ink' => $acc['ink'],
			'--lpr-pm-add'       => $add['accent'],
			'--lpr-pm-on-add'    => $add['on'],
			'--lpr-pm-add-ink'   => $add['ink'],
		);
		$map = array(
			'pm_bg_color'    => '--lpr-pm-bg',
			'pm_text_color'  => '--lpr-pm-ink',
			'pm_muted_color' => '--lpr-pm-muted',
			'pm_line_color'  => '--lpr-pm-line',
			'pm_check_color' => '--lpr-pm-check',
			'pm_save_color'  => '--lpr-pm-save',
			'pm_cart_bg'     => '--lpr-pm-cart-bg',
			'pm_cart_text'   => '--lpr-pm-cart-text',
			'pm_close_color' => '--lpr-pm-close',
			'pm_nav_color'   => '--lpr-pm-nav',
			'pm_image_bg'    => '--lpr-pm-img-bg',
		);
		foreach ($map as $id => $var) {
			if ($c[$id]) {
				$vars[$var] = $c[$id];
			}
		}
		if ($c['pm_check_color']) {
			$vars['--lpr-pm-on-check'] = self::readable($c['pm_check_color'])['on'];
		}

		$css = '';
		foreach ($vars as $var => $value) {
			$css .= $var . ': ' . $value . '; ';
		}
		return trim($css);
	}

	/** Page overlay color for the modern popup, or '' for the default. */
	public static function overlay_color($base) {
		$c = self::colors($base);
		return $c['pm_overlay_color'];
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

	/**
	 * Option menus for a variable product: one menu per option (Color, Size), so shoppers
	 * pick each one like on the product page. variation-picker.js finds the matching
	 * variation, puts its ID on the Add button and shows its price.
	 *
	 * With one option the menu also shows each choice's price. A variation with an "Any"
	 * value (Size: any) offers every value of that option; the picked values go with the add
	 * (ajax_add_any_variation()). Empty when nothing can be bought.
	 *
	 * @param \WC_Product   $product
	 * @param string        $select_class Extra class for the menus (the place's own styling).
	 * @param callable|null $price_html   Price HTML of a variation, when the place shows its own
	 *                                    (Pro's cart drawer: the add-on saving). Default get_price_html().
	 * @param callable|null $include      Which variations to offer (Pro's checkout offer: those under
	 *                                    its price limit). Gets the variation, returns bool.
	 * @return string
	 */
	public static function variation_picker($product, $select_class = '', $price_html = null, $include = null) {
		if (!$product || !$product->is_type('variable')) {
			return '';
		}

		$attributes = $product->get_variation_attributes(); // name => values
		if (!$attributes) {
			return '';
		}

		// attribute_pa_size => all its values, for variations with an "Any" value.
		$all_values = array();
		foreach ($attributes as $name => $options) {
			$all_values['attribute_' . sanitize_title($name)] = array_map('strval', (array) $options);
		}

		$variations = array();
		$used       = array();
		foreach ($product->get_available_variations('objects') as $variation) {
			if (!$variation || !$variation->is_purchasable() || !$variation->is_in_stock()) {
				continue;
			}
			if (is_callable($include) && !call_user_func($include, $variation)) {
				continue;
			}
			$values = $variation->get_variation_attributes(); // attribute_pa_color => black, '' = any
			foreach ($values as $key => $value) {
				if ('' === (string) $value) {
					foreach (isset($all_values[$key]) ? $all_values[$key] : array() as $any) {
						if (!isset($used[$key][$any])) {
							$used[$key][$any] = $variation;
						}
					}
				}
			}
			$variations[] = array(
				'id' => $variation->get_id(),
				'a'  => $values,
				'p'  => is_callable($price_html) ? (string) call_user_func($price_html, $variation) : $variation->get_price_html(),
			);
			foreach ($values as $key => $value) {
				if ('' !== (string) $value) {
					$used[$key][(string) $value] = $variation;
				}
			}
		}

		if (!$variations) {
			return '';
		}

		$single = 1 === count($attributes);
		$menus  = '';
		foreach ($attributes as $name => $options) {
			$key   = 'attribute_' . sanitize_title($name);
			$label = wc_attribute_label($name, $product);
			$items = '';

			// Taxonomy options in the store's term order, with their names.
			if (taxonomy_exists($name)) {
				$terms = wc_get_product_terms($product->get_id(), $name, array('fields' => 'all'));
				$pairs = array();
				foreach ($terms as $term) {
					if (in_array($term->slug, (array) $options, true)) {
						$pairs[$term->slug] = $term->name;
					}
				}
			} else {
				$pairs = array_combine((array) $options, (array) $options);
			}

			foreach ($pairs as $value => $text) {
				if (!isset($used[$key][(string) $value])) {
					continue; // no variation in stock with this value
				}
				if ($single) {
					$price = html_entity_decode(wp_strip_all_tags(wc_price(wc_get_price_to_display($used[$key][(string) $value]))), ENT_QUOTES, get_bloginfo('charset'));
					$text .= ' (' . $price . ')';
				}
				$items .= '<option value="' . esc_attr($value) . '">' . esc_html($text) . '</option>';
			}

			if ('' === $items) {
				return '';
			}

			$first = $single ? self::choose_label($product) : sprintf(/* translators: %s: option name, for example "Color" */ __('Choose %s', 'leo-product-recommendations'), $label);
			$menus .= sprintf(
				'<select class="lpr-vpick__select %1$s" data-attribute="%2$s" aria-label="%3$s"><option value="">%4$s</option>%5$s</select>',
				esc_attr($select_class),
				esc_attr($key),
				esc_attr(sprintf(/* translators: 1: option name, 2: product name */ __('%1$s for %2$s', 'leo-product-recommendations'), $label, $product->get_name())),
				esc_html($first),
				$items
			);
		}

		return '<div class="lpr-vpick" data-variations="' . esc_attr(wp_json_encode($variations)) . '">' . $menus . '</div>';
	}

	/**
	 * WooCommerce's AJAX add to cart sends only the variation ID, so a variation with an
	 * "Any" value cannot be added with it. When the option menus send the picked values
	 * (lpr_attributes), add the variation with them here, and answer like WooCommerce does.
	 */
	public static function ajax_add_any_variation() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- same as WooCommerce's own add to cart request.
		if (empty($_POST['lpr_attributes']) || !is_array($_POST['lpr_attributes']) || empty($_POST['product_id'])) {
			return; // WooCommerce handles it.
		}

		$variation_id = absint($_POST['product_id']);
		$variation    = wc_get_product($variation_id);
		if (!$variation || !$variation->is_type('variation')) {
			return;
		}
		$product_id = $variation->get_parent_id();
		$parent     = wc_get_product($product_id);
		$quantity   = empty($_POST['quantity']) ? 1 : wc_stock_amount(wp_unslash($_POST['quantity'])); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- wc_stock_amount() makes it a number.
		$posted     = wp_unslash($_POST['lpr_attributes']); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- each value is cleaned below.
		// phpcs:enable

		// The variation's own values, then the picked value for each "Any" one (cleaned as WooCommerce does).
		$attributes = array();
		foreach ($variation->get_variation_attributes() as $key => $value) {
			if ('' !== (string) $value) {
				$attributes[$key] = $value;
				continue;
			}
			if (!isset($posted[$key]) || !is_scalar($posted[$key])) {
				continue;
			}
			$name  = substr($key, 10); // after "attribute_"
			$clean = taxonomy_exists(wc_sanitize_taxonomy_name($name)) ? sanitize_title($posted[$key]) : html_entity_decode(wc_clean($posted[$key]), ENT_QUOTES, get_bloginfo('charset'));
			if ('' !== $clean) {
				$attributes[$key] = $clean;
			}
		}

		$status = get_post_status($product_id);
		$valid  = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $attributes);

		if ($parent && $valid && 'publish' === $status && false !== WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $attributes)) {
			do_action('woocommerce_ajax_added_to_cart', $product_id);
			if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
				wc_add_to_cart_message(array($product_id => $quantity), true);
			}
			\WC_AJAX::get_refreshed_fragments(); // sends the answer and stops
		}

		wp_send_json(array(
			'error'       => true,
			'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id),
		));
	}

	/**
	 * First line of a variation menu, named after the product's options:
	 * "Choose Color", "Choose Color / Size".
	 *
	 * @param \WC_Product $product
	 * @return string
	 */
	public static function choose_label($product) {
		$names = array();
		if ($product && $product->is_type('variable')) {
			foreach (array_keys($product->get_variation_attributes()) as $attribute) {
				$names[] = wc_attribute_label($attribute, $product);
			}
		}
		$names = array_values(array_filter(array_unique($names)));
		if (!$names) {
			return __('Choose an option', 'leo-product-recommendations');
		}
		/* translators: %s: option names of a product, for example "Color" or "Color / Size" */
		$label = sprintf(__('Choose %s', 'leo-product-recommendations'), implode(' / ', $names));

		/**
		 * First line of a variation menu in the popup and the cart page list.
		 *
		 * @since 3.7.1
		 * @param string      $label
		 * @param \WC_Product $product
		 */
		return (string) apply_filters('lpr_variation_menu_label', $label, $product);
	}

	private static function button($product, $variable_add_to_cart) {
		$add = __('Add', 'leo-product-recommendations');

		if ($product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock() && !$product->is_sold_individually()) {
			return sprintf(
				'<a href="%1$s" data-quantity="1" data-product_id="%2$d" data-product_sku="%3$s" data-lpr_source="popup" class="button add_to_cart_button ajax_add_to_cart lpr-pm-card__add" rel="nofollow" aria-label="%4$s">%5$s</a>',
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
				'<a href="%1$s" data-quantity="1" data-product_id="%2$d" data-lpr_source="popup" class="button add_to_cart_button ajax_add_to_cart lpr-pm-card__add" rel="nofollow">%3$s</a>',
				esc_url($product->add_to_cart_url()),
				$product->get_id(),
				esc_html($add)
			);
		}

		if ($variable_add_to_cart && $product->is_type('variable')) {
			$picker = self::variation_picker($product, 'lpr-pm-card__select');
			if ('' !== $picker) {
				return $picker . sprintf(
					'<a href="%1$s" data-quantity="1" data-product_id="" data-lpr_source="popup" class="button add_to_cart_button ajax_add_to_cart lpr-pm-card__add is-disabled" rel="nofollow" aria-disabled="true">%2$s</a>',
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
