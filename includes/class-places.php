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

		// Cart block: right after the block.
		add_filter('render_block_woocommerce/cart', array($this, 'cart_block'), 10, 2);
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
				'label' => __('Show recommendations under the cart', 'leo-product-recommendations'),
				'type' => 'checkbox',
				'switch' => true,
				'default' => 1,
				'description' => __('Products that go with everything in the cart, under the cart table. Products already in the cart are left out.', 'leo-product-recommendations'),
			),
			array(
				'id' => 'cart_recs_layout',
				'group' => 'cart',
				'title' => __('Layout', 'leo-product-recommendations'),
				'type' => 'radio',
				'options' => array(
					'grid' => __('Grid', 'leo-product-recommendations'),
					'slider' => __('Slider', 'leo-product-recommendations'),
				),
				'default' => 'grid',
				'description' => __('Grid shows all products at once. Slider keeps them in one row that shoppers swipe or scroll with the arrows.', 'leo-product-recommendations'),
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
				'description' => __('Grid: up to 4 in a row, more wrap to a second row. Slider: 4 in view, the rest one swipe away.', 'leo-product-recommendations'),
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
		wp_enqueue_script('lpr-places', $this->base->get_url('assets/js/places.js'), array('jquery', 'wc-add-to-cart'), $ver, true);
		wp_localize_script('lpr-places', 'lprPlaces', array(
			'datalayer' => $this->base->datalayer_enabled(),
		));
	}

	/* ---------- cart page ---------- */

	public function cart_classic() {
		if ($this->printed_cart || !$this->cart_enabled()) {
			return;
		}

		$this->printed_cart = true;
		$this->render_cart();
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

		$number = absint($this->base->get_setting('cart_recs_number'));
		$number = $number ? min(8, max(2, $number)) : 4;

		$map = $this->recommendations_for(self::cart_product_ids(), $number);
		if (!$map) {
			return;
		}

		$heading = trim(wp_strip_all_tags((string) $this->base->get_setting('cart_recs_heading')));
		$heading = '' !== $heading ? $heading : __('Complete your order', 'leo-product-recommendations');

		$this->render(array(
			'place'     => 'cart',
			'list_id'   => 'lpr_cart',
			'list_name' => 'Cart page recommendations',
			'heading'   => $heading,
			'map'       => $map,
			'columns'   => min(4, count($map)),
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
			'exclude'     => array(),
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
			$slider ? ' style="--lpr-per-view:' . (int) max(1, min(4, count($args['map']))) . '"' : ''
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
			$product = wc_get_product($id); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

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
		$product = $original_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		if ($original_post) {
			setup_postdata($original_post);
		} else {
			wp_reset_postdata();
		}
	}
}
