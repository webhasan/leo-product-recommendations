<?php
/**
 * Milestone notices (Free only).
 *
 * One dismissible notice when something worth knowing happens:
 * - the popup has been shown 100 times,
 * - the first paid order with a product the popup suggested,
 * - one month of use, with the store's own numbers.
 *
 * The counts are kept in one option in this site's database. Nothing is sent anywhere.
 * Counting stops once the notices it is for have been shown, and nothing runs when Pro is active.
 *
 * @package LoeCoder\Plugin\ProductRecommendations
 * @since   3.7.0
 */

namespace LoeCoder\Plugin\ProductRecommendations;

if (!defined('ABSPATH')) {
	exit;
}

class Milestones {

	const OPTION = 'lpr_milestones';

	const SESSION_KEY = 'lpr_rec_ids';

	const ORDER_META = '_lpr_rec_total';

	const COUNTED_META = '_lpr_rec_counted';

	const VIEWS_GOAL = 100;

	/** Days between two notices, so they never come one after another. */
	const GAP_DAYS = 2;

	/**
	 * @var Product_Recommendations
	 */
	private $base;

	public function __construct($base) {
		$this->base = $base;

		// Popup shown and products added from it.
		add_action('lpr_popup_shown', array($this, 'count_view'));
		add_action('woocommerce_add_to_cart', array($this, 'count_add'), 10, 6);
		add_action('woocommerce_cart_item_removed', array($this, 'forget_removed'), 10, 2);

		// Orders with a suggested product. Counted once the order is paid.
		add_action('woocommerce_checkout_order_processed', array($this, 'mark_order'), 10, 3);
		add_action('woocommerce_store_api_checkout_order_processed', array($this, 'mark_order'));
		add_action('woocommerce_order_status_processing', array($this, 'count_order'), 10, 2);
		add_action('woocommerce_order_status_completed', array($this, 'count_order'), 10, 2);

		if (is_admin()) {
			add_action('admin_init', array($this, 'start_clock'));
			add_action('admin_notices', array($this, 'render_notice'));
			add_action('wp_ajax_lpr_milestone_done', array($this, 'ajax_done'));
		}
	}

	/* ---------- data ---------- */

	private function data() {
		$data = get_option(self::OPTION, array());
		return wp_parse_args(is_array($data) ? $data : array(), array(
			'since'    => 0,
			'views'    => 0,
			'adds'     => 0,
			'orders'   => 0,
			'sales'    => 0.0,
			'order_id' => 0,
			'done'     => array(),
			'last'     => 0,
			'month'    => array(),
			'existing' => 0,
		));
	}

	private function save($data) {
		update_option(self::OPTION, $data, false);
	}

	private function off() {
		return $this->base->is_pro_activated();
	}

	/** Views and adds are only needed until the month notice is shown. */
	private function counting($data) {
		return !$this->off() && empty($data['done']['month']);
	}

	/**
	 * New install: called from the activation hook, before anything else runs.
	 * The clock starts now and the month notice comes after 30 days.
	 */
	public static function activated() {
		if (false === get_option(self::OPTION)) {
			update_option(self::OPTION, array('since' => time(), 'existing' => 0), false);
		}
	}

	/**
	 * Sites that update from an older version never run the activation hook, so they have
	 * no start date yet. Counting starts on their first admin visit after the update, and
	 * because they already know the plugin, their summary comes after 14 days instead of 30.
	 */
	public function start_clock() {
		$data = $this->data();
		if (empty($data['since'])) {
			$data['since'] = time();
			$data['existing'] = 1;
			$this->save($data);
		}
	}

	/* ---------- counting ---------- */

	public function count_view() {
		$data = $this->data();
		if (!$this->counting($data)) {
			return;
		}
		$data['views']++;
		$this->save($data);
	}

	/**
	 * A product added from the popup: the popup's buttons send lpr_source=popup with the
	 * normal WooCommerce add to cart request.
	 */
	public function count_add($cart_item_key, $product_id, $quantity = 1, $variation_id = 0, $variation = array(), $cart_item_data = array()) {
		$source = isset($_REQUEST['lpr_source']) ? sanitize_key(wp_unslash($_REQUEST['lpr_source'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- only read to label the item, WooCommerce handles the request.
		if ('popup' !== $source || $this->off()) {
			return;
		}

		$data = $this->data();
		if ($this->counting($data)) {
			$data['adds']++;
			$this->save($data);
		}

		if (function_exists('WC') && WC()->session) {
			$ids = (array) WC()->session->get(self::SESSION_KEY, array());
			$ids[] = (int) ($variation_id ? $variation_id : $product_id);
			WC()->session->set(self::SESSION_KEY, array_slice(array_values(array_unique(array_map('absint', $ids))), -50));
		}
	}

	/**
	 * A product taken out of the cart is no longer "added from the popup", unless another
	 * line of it is still there.
	 *
	 * @param string   $cart_item_key Removed line.
	 * @param \WC_Cart $cart          Cart.
	 */
	public function forget_removed($cart_item_key, $cart) {
		if (!function_exists('WC') || !WC()->session || !$cart || empty($cart->removed_cart_contents[$cart_item_key])) {
			return;
		}
		$ids = array_map('absint', (array) WC()->session->get(self::SESSION_KEY, array()));
		if (!$ids) {
			return;
		}
		$removed = $cart->removed_cart_contents[$cart_item_key];
		$id      = (int) (!empty($removed['variation_id']) ? $removed['variation_id'] : $removed['product_id']);
		foreach ($cart->get_cart() as $item) {
			if ((int) (!empty($item['variation_id']) ? $item['variation_id'] : $item['product_id']) === $id) {
				return;
			}
		}
		WC()->session->set(self::SESSION_KEY, array_values(array_diff($ids, array($id))));
	}

	/**
	 * At checkout: if the order has products added from the popup, save their total on the order.
	 *
	 * @param int|\WC_Order $order Order ID (classic checkout) or order (block checkout).
	 */
	public function mark_order($order) {
		if ($this->off() || !function_exists('WC') || !WC()->session) {
			return;
		}
		$ids = array_map('absint', (array) WC()->session->get(self::SESSION_KEY, array()));
		if (!$ids) {
			return;
		}
		$order = is_a($order, 'WC_Order') ? $order : wc_get_order($order);
		if (!$order) {
			return;
		}

		$total = 0.0;
		$found = false;
		foreach ($order->get_items() as $item) {
			$pid = (int) $item->get_product_id();
			$vid = (int) $item->get_variation_id();
			if (in_array($pid, $ids, true) || ($vid && in_array($vid, $ids, true))) {
				$found = true;
				$total += (float) $item->get_total();
			}
		}
		if (!$found) {
			return;
		}

		$order->update_meta_data(self::ORDER_META, wc_format_decimal($total));
		$order->save();
		WC()->session->set(self::SESSION_KEY, array());
	}

	/** Paid order with a suggested product: count it once. */
	public function count_order($order_id, $order = null) {
		if ($this->off()) {
			return;
		}
		$order = is_a($order, 'WC_Order') ? $order : wc_get_order($order_id);
		if (!$order || '' === (string) $order->get_meta(self::ORDER_META) || $order->get_meta(self::COUNTED_META)) {
			return;
		}
		$order->update_meta_data(self::COUNTED_META, 1);
		$order->save();

		$data = $this->data();
		$data['orders']++;
		$data['sales'] = (float) $data['sales'] + (float) $order->get_meta(self::ORDER_META);
		if (empty($data['order_id'])) {
			$data['order_id'] = $order->get_id();
		}
		$this->save($data);
	}

	/* ---------- notices ---------- */

	/** The one milestone to show now, or ''. */
	private function due($data) {
		$done = (array) $data['done'];
		if ($data['last'] && time() - (int) $data['last'] < self::GAP_DAYS * DAY_IN_SECONDS) {
			return '';
		}
		if ($data['orders'] >= 1 && empty($done['order'])) {
			return 'order';
		}
		if ($data['views'] >= self::VIEWS_GOAL && empty($done['views'])) {
			return 'views';
		}
		$age = $data['since'] ? time() - (int) $data['since'] : 0;
		// New stores after 30 days, existing ones after 14. Small stores wait longer,
		// so the numbers mean something.
		$first = $data['existing'] ? 14 : 30;
		$late = $data['existing'] ? 45 : 60;
		if (empty($done['month']) && $age >= $first * DAY_IN_SECONDS && ($data['views'] >= self::VIEWS_GOAL || $age >= $late * DAY_IN_SECONDS)) {
			return 'month';
		}
		return '';
	}

	/** Admin screens where a notice may show. Never on the editor or other plugins' pages. */
	private function screen_ok() {
		$screen = function_exists('get_current_screen') ? get_current_screen() : null;
		if (!$screen) {
			return false;
		}
		$id = (string) $screen->id;
		return in_array($id, array('dashboard', 'plugins', 'toplevel_page_lpr-settings', 'edit-product', 'edit-shop_order', 'woocommerce_page_wc-orders'), true)
			|| 0 === strpos($id, 'woocommerce_page_');
	}

	private function link($sec) {
		return admin_url('admin.php?page=lpr-settings&sec=' . $sec);
	}

	public function render_notice() {
		if ($this->off() || !current_user_can('manage_options') || !$this->screen_ok()) {
			return;
		}
		$data = $this->data();
		$key = $this->due($data);
		if (!$key) {
			return;
		}

		$pro = array($this->link('lpr-places-settings'), __('See what Pro adds', 'leo-product-recommendations'));
		$text = '';
		$main = $pro;
		$extra = null;

		if ('views' === $key) {
			$text = '<strong>' . esc_html(sprintf(
				/* translators: %s: number of times */
				__('Your popup has been shown %s times.', 'leo-product-recommendations'),
				number_format_i18n(self::VIEWS_GOAL)
			)) . '</strong> ' . esc_html__('Pro shows your suggestions in 6 more places, from the product page to the thank-you page.', 'leo-product-recommendations');
		} elseif ('order' === $key) {
			$text = '<strong>' . esc_html__('A customer just bought something your popup suggested.', 'leo-product-recommendations') . '</strong>';
			$order = $data['order_id'] ? wc_get_order($data['order_id']) : null;
			if ($order) {
				$main = array($order->get_edit_order_url(), __('View order', 'leo-product-recommendations'));
				$extra = $pro;
			}
		} else {
			// Freeze the numbers the first time, so the message stays the same until dismissed.
			if (empty($data['month'])) {
				$data['month'] = array(
					'views'  => (int) $data['views'],
					'adds'   => (int) $data['adds'],
					'orders' => (int) $data['orders'],
					'sales'  => (float) $data['sales'],
					'days'     => max(1, (int) floor((time() - (int) $data['since']) / DAY_IN_SECONDS)),
					'existing' => (int) $data['existing'],
				);
				$this->save($data);
			}
			list($text, $main, $extra) = $this->month_message($data['month'], $pro);
		}

		$nonce = wp_create_nonce('lpr_milestone_done');
		?>
		<div class="notice notice-info is-dismissible lpr-milestone" id="lpr-milestone" data-key="<?php echo esc_attr($key); ?>" data-nonce="<?php echo esc_attr($nonce); ?>">
			<p><?php echo wp_kses($text, array('strong' => array(), 'span' => array('class' => array()), 'bdi' => array())); ?></p>
			<p>
				<a class="button button-primary lpr-milestone__go" href="<?php echo esc_url($main[0]); ?>"><?php echo esc_html($main[1]); ?></a>
				<?php if ($extra) : ?>
					<a class="lpr-milestone__go" href="<?php echo esc_url($extra[0]); ?>" style="margin-left:10px;"><?php echo esc_html($extra[1]); ?></a>
				<?php endif; ?>
			</p>
		</div>
		<?php
		$js = 'jQuery(function($){var $n=$("#lpr-milestone");if(!$n.length)return;'
			. 'function done(){var f=new FormData();f.append("action","lpr_milestone_done");f.append("key",$n.data("key"));f.append("nonce",$n.data("nonce"));'
			. 'if(navigator.sendBeacon){navigator.sendBeacon(' . wp_json_encode(admin_url('admin-ajax.php')) . ',f);}else{$.ajax({url:' . wp_json_encode(admin_url('admin-ajax.php')) . ',type:"POST",data:f,processData:false,contentType:false});}}'
			. '$n.on("click",".notice-dismiss, .lpr-milestone__go",done);});';
		wp_print_inline_script_tag($js);
	}

	/**
	 * The month notice, chosen by how the popup did.
	 *
	 * @return array Text (HTML), main button, extra link.
	 */
	private function month_message($m, $pro) {
		$m = wp_parse_args($m, array('views' => 0, 'adds' => 0, 'orders' => 0, 'sales' => 0, 'days' => 30, 'existing' => 0));
		$matches = array($this->link('lpr-global-settings'), __('Pick better matches', 'leo-product-recommendations'));
		$views = '<strong>' . esc_html(number_format_i18n($m['views'])) . '</strong>';
		$months = max(1, (int) floor($m['days'] / 30));

		// New stores: "this month". Stores that updated: the real number of days we counted.
		$period = (!$m['existing'] && $m['days'] < 45)
			? __('this month', 'leo-product-recommendations')
			/* translators: %s: number of days */
			: sprintf(__('in the last %s days', 'leo-product-recommendations'), number_format_i18n($m['days']));
		$period = esc_html($period);

		if ($m['existing']) {
			$used = __('Thanks for using Leo Product Recommendations.', 'leo-product-recommendations');
		} elseif (1 === $months) {
			$used = __('You’ve used Leo Product Recommendations for a month.', 'leo-product-recommendations');
		} else {
			/* translators: %s: number of months */
			$used = sprintf(__('You’ve used Leo Product Recommendations for %s months.', 'leo-product-recommendations'), number_format_i18n($months));
		}
		$lead = '<strong>' . esc_html($used) . '</strong> ';

		$orders = '<strong>' . esc_html(sprintf(
			/* translators: %s: number of orders */
			_n('%s order', '%s orders', $m['orders'], 'leo-product-recommendations'),
			number_format_i18n($m['orders'])
		)) . '</strong>';

		if ($m['orders'] >= 3) {
			$text = $m['sales'] > 0
				/* translators: 1: number of orders, 2: sales amount, 3: time period such as "this month" */
				? sprintf(esc_html__('Your popup helped with %1$s and %2$s in sales %3$s.', 'leo-product-recommendations'), $orders, '<strong>' . $this->money($m['sales']) . '</strong>', $period)
				/* translators: 1: number of orders, 2: time period such as "this month" */
				: sprintf(esc_html__('Your popup helped with %1$s %2$s.', 'leo-product-recommendations'), $orders, $period);
			$text .= ' ' . esc_html__('Right now it shows only after add to cart. Pro adds 6 more places, from the product page to the thank-you page.', 'leo-product-recommendations');
			return array($lead . $text, $pro, null);
		}

		if ($m['orders'] >= 1) {
			$text = (1 === (int) $m['orders'] && !$m['existing'])
				? sprintf(
					/* translators: %s: "your first order" */
					esc_html__('Your popup has already helped with %s.', 'leo-product-recommendations'),
					'<strong>' . esc_html__('your first order', 'leo-product-recommendations') . '</strong>'
				)
				/* translators: 1: number of orders, 2: time period such as "this month" */
				: sprintf(esc_html__('Your popup has already helped with %1$s %2$s.', 'leo-product-recommendations'), $orders, $period);
			$text .= ' ' . esc_html__('Imagine the same suggestions in the cart and at checkout, where shoppers are ready to pay.', 'leo-product-recommendations');
			return array($lead . $text, $pro, null);
		}

		if ($m['views'] >= self::VIEWS_GOAL && $m['adds'] > 0) {
			$adds = '<strong>' . esc_html(sprintf(
				/* translators: %s: number of products */
				_n('%s suggested product', '%s suggested products', $m['adds'], 'leo-product-recommendations'),
				number_format_i18n($m['adds'])
			)) . '</strong>';
			/* translators: 1: times shown, 2: time period such as "this month", 3: number of products */
			$text = sprintf(esc_html__('Your popup was shown %1$s times %2$s, and shoppers added %3$s to their carts.', 'leo-product-recommendations'), $views, $period, $adds);
			$text .= ' ' . esc_html__('Pro keeps those suggestions in front of them in the cart and at checkout, so more of them reach the order.', 'leo-product-recommendations');
			return array($lead . $text, $pro, null);
		}

		if ($m['views'] >= self::VIEWS_GOAL) {
			/* translators: 1: times shown, 2: time period such as "this month" */
			$text = sprintf(esc_html__('Your popup put your products in front of shoppers %1$s times %2$s.', 'leo-product-recommendations'), $views, $period);
			$text .= ' ' . esc_html__('Want to get more from it? Matching each product with items that go with it gets more clicks, and Pro adds 6 more places to show them.', 'leo-product-recommendations');
			return array($text, $matches, $pro);
		}

		// Low traffic: help first, no sales pitch.
		/* translators: 1: times shown, 2: time period such as "this month" */
		$text = sprintf(esc_html__('Your popup was shown %1$s times %2$s.', 'leo-product-recommendations'), $views, $period);
		$text .= ' ' . esc_html__('Matching each product with items that go with it helps shoppers find more to buy.', 'leo-product-recommendations');
		return array($text, $matches, null);
	}

	/** Store currency, no decimals for whole amounts, no trailing space after the symbol. */
	private function money($amount) {
		$amount = (float) $amount;
		$html = wc_price($amount, array('decimals' => floor($amount) == $amount ? 0 : wc_get_price_decimals()));
		return trim(str_replace(array('&nbsp;</span>', "\xc2\xa0</span>"), '</span>', $html));
	}

	public function ajax_done() {
		$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
		if (!$nonce || !wp_verify_nonce($nonce, 'lpr_milestone_done') || !current_user_can('manage_options')) {
			wp_send_json_error(null, 403);
		}
		$key = isset($_POST['key']) ? sanitize_key(wp_unslash($_POST['key'])) : '';
		if (!in_array($key, array('views', 'order', 'month'), true)) {
			wp_send_json_error(null, 400);
		}
		$data = $this->data();
		$data['done'][$key] = time();
		$data['last'] = time();
		$this->save($data);
		wp_send_json_success();
	}
}
