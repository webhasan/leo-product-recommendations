<?php
namespace LoeCoder\Plugin\ProductRecommendations;
/**
 * Class for single page ajax add to cart
 *
 * @since      1.0.0
 * @author     LeoCoder
 */

if (!defined('ABSPATH')) {
	exit;
}

class Ajax_Add_To_Cart {
	private $data = array();

	public function __construct($request) {
		$this->data = $request;
		$this->add_to_cart_init();
	}

	public function add_to_cart_init() {

		if (!isset($this->data['product_id']) || !is_numeric(wp_unslash($this->data['product_id']))) {
			// phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$this->response_add_to_cart_fail('Bad request.', 400);
		}

		wc_nocache_headers();

		$product_id = apply_filters('woocommerce_add_to_cart_product_id', absint(wp_unslash($this->data['product_id'])));
		$adding_to_cart = wc_get_product($product_id);

		if (!$adding_to_cart) {
			wp_send_json_error('Product not found', 404);
		}

		$add_to_cart_handler = apply_filters('woocommerce_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart);

		if ('variable' === $add_to_cart_handler || 'variation' === $add_to_cart_handler) {
			$this->add_to_cart_variable_product($product_id);
		} elseif ('grouped' === $add_to_cart_handler) {
			$this->add_to_cart_grouped_product($product_id);
		} elseif (has_action('woocommerce_add_to_cart_handler_' . $add_to_cart_handler)) {
			do_action('woocommerce_add_to_cart_handler_' . $add_to_cart_handler, false); // Custom handler.
		} else {
			$this->add_to_cart_simple_product($product_id);
		}
	}

	public function add_to_cart_variable_product($product_id) {
		try {
			$variation_id = empty($this->data['variation_id']) ? '' : absint(wp_unslash($this->data['variation_id'])); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			$quantity = empty($this->data['quantity']) ? 1 : wc_stock_amount(wp_unslash($this->data['quantity'])); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
			$missing_attributes = array();
			$variations = array();
			$adding_to_cart = wc_get_product($product_id);

			// If the $product_id was in fact a variation ID, update the variables.
			if ($adding_to_cart->is_type('variation')) {
				$variation_id = $product_id;
				$product_id = $adding_to_cart->get_parent_id();
				$adding_to_cart = wc_get_product($product_id);

				if (!$adding_to_cart) {
					$this->response_add_to_cart_fail(__('Product not found', 'leo-product-recommendations'), 404);
				}
			}

			// Gather posted attributes.
			$posted_attributes = array();

			foreach ($adding_to_cart->get_attributes() as $attribute) {
				if (!$attribute['is_variation']) {
					continue;
				}
				$attribute_key = 'attribute_' . sanitize_title($attribute['name']);

				if (isset($this->data[$attribute_key])) {
					// phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
					if ($attribute['is_taxonomy']) {
						// Don't use wc_clean as it destroys sanitized characters.
						$value = sanitize_title(wp_unslash($this->data[$attribute_key])); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
					} else {
						$value = html_entity_decode(wc_clean(wp_unslash($this->data[$attribute_key])), ENT_QUOTES, get_bloginfo('charset')); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
					}

					$posted_attributes[$attribute_key] = $value;
				}
			}

			// If no variation ID is set, attempt to get a variation ID from posted attributes.
			if (empty($variation_id)) {
				$data_store = \WC_Data_Store::load('product');
				$variation_id = $data_store->find_matching_product_variation($adding_to_cart, $posted_attributes);
			}

			// Do we have a variation ID?
			if (empty($variation_id)) {
				$this->response_add_to_cart_fail(__('Please choose product options', 'woocommerce'));
			}

			// Check the data we have is valid.
			$variation_data = wc_get_product_variation_attributes($variation_id);

			foreach ($adding_to_cart->get_attributes() as $attribute) {
				if (!$attribute['is_variation']) {
					continue;
				}

				// Get valid value from variation data.
				$attribute_key = 'attribute_' . sanitize_title($attribute['name']);
				$valid_value = isset($variation_data[$attribute_key]) ? $variation_data[$attribute_key] : '';

				/**
				 * If the attribute value was posted, check if it's valid.
				 *
				 * If no attribute was posted, only error if the variation has an 'any' attribute which requires a value.
				 */
				if (isset($posted_attributes[$attribute_key])) {
					$value = $posted_attributes[$attribute_key];

					// Allow if valid or show error.
					if ($valid_value === $value) {
						$variations[$attribute_key] = $value;
					} elseif ('' === $valid_value && in_array($value, $attribute->get_slugs(), true)) {
						// If valid values are empty, this is an 'any' variation so get all possible values.
						$variations[$attribute_key] = $value;
					} else {
						/* translators: %s: Attribute name. */
						$this->response_add_to_cart_fail(sprintf(__('Invalid value posted for %s', 'woocommerce'), wc_attribute_label($attribute['name'])));
					}
				} elseif ('' === $valid_value) {
					$missing_attributes[] = wc_attribute_label($attribute['name']);
				}
			}
			if (!empty($missing_attributes)) {
				/* translators: %s: Attribute name. */
				$this->response_add_to_cart_fail(sprintf(_n('%s is a required field', '%s are required fields', count($missing_attributes), 'woocommerce'), wc_format_list_of_items($missing_attributes)));
			}
		} catch (\Exception $e) {
			$this->response_add_to_cart_fail($e->getMessage());
		}

		$passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations);

		if ($passed_validation && false !== WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variations)) {
			do_action( 'woocommerce_ajax_added_to_cart', $product_id );
			$this->response_add_to_cart_success(wc_add_to_cart_message(array($product_id => $quantity), true, true));
		}

		$this->response_add_to_cart_fail(__('Unable to add to cart. Something went wrong.', 'leo-product-recommendations'));
	}

	public function add_to_cart_grouped_product($product_id) {

		$was_added_to_cart = false;
		$added_to_cart = array();
		$items = isset($this->data['quantity']) && is_array($this->data['quantity']) ? wp_unslash($this->data['quantity']) : array(); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if (!empty($items)) {
			$quantity_set = false;

			foreach ($items as $item => $quantity) {
				if ($quantity <= 0) {
					continue;
				}
				$quantity_set = true;

				// Add to cart validation.
				$passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $item, $quantity);

				// Suppress total recalculation until finished.
				remove_action('woocommerce_add_to_cart', array(WC()->cart, 'calculate_totals'), 20, 0);

				if ($passed_validation && false !== WC()->cart->add_to_cart($item, $quantity)) {
					do_action( 'woocommerce_ajax_added_to_cart', $product_id );
					$was_added_to_cart = true;
					$added_to_cart[$item] = $quantity;
				} else {
					$this->response_add_to_cart_fail('There is no enough stock.', 400);
				}

				add_action('woocommerce_add_to_cart', array(WC()->cart, 'calculate_totals'), 20, 0);
			}

			if (!$was_added_to_cart && !$quantity_set) {
				$this->response_add_to_cart_fail(__('Please choose the quantity of items you wish to add to your cart.', 'woocommerce'));
			} elseif ($was_added_to_cart) {
				$message = wc_add_to_cart_message($added_to_cart, false, true);
				WC()->cart->calculate_totals();

				$this->response_add_to_cart_success($message);
			}
		} elseif ($product_id) {
			$this->response_add_to_cart_fail(__('Please choose a product to add to your cart.', 'woocommerce'));
		}
	}

	public function add_to_cart_simple_product($product_id) {

		$quantity = empty($this->data['quantity']) ? 1 : wc_stock_amount(wp_unslash($this->data['quantity'])); // phpcs:ignore WordPress.Security.NonceVerification.NoNonceVerification
		$passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);

		if ($passed_validation && false !== WC()->cart->add_to_cart($product_id, $quantity)) {
			do_action( 'woocommerce_ajax_added_to_cart', $product_id );
			$this->response_add_to_cart_success(wc_add_to_cart_message(array($product_id => $quantity), true, true));
		}

		$this->response_add_to_cart_fail(__('Unable to add to cart. Something went wrong.', 'woocommerce'));
	}

	/**
	 * Ajax responser when successfully added to cart.
	 *
	 * @param string $message
	 * @param integer $status
	 * @return void
	 */
	public function response_add_to_cart_success($message, $status = 200) {
		$fragment_data = $this->get_refreshed_fragments();
		wp_send_json_success(array(
			'message' => $message,
			'fragments' => $fragment_data['fragments'],
			'cart_hash' => $fragment_data['cart_hash'],
		), $status);
	}

	/**
	 * Ajax response for unsuccessful add to cart.
	 *
	 * @param [type] $message
	 * @param integer $status
	 * @return void
	 */
	public function response_add_to_cart_fail($message, $status = 200) {
		wp_send_json_error(array(
			'message' => $message,
		), $status);
	}

	public static function get_refreshed_fragments() {
		ob_start();

		woocommerce_mini_cart();

		$mini_cart = ob_get_clean();

		$data = array(
			'fragments' => apply_filters(
				'woocommerce_add_to_cart_fragments',
				array(
					'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
				)
			),
			'cart_hash' => WC()->cart->get_cart_hash(),
		);

		return $data;
	}
}
