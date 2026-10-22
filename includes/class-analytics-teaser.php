<?php
/**
 * LPR Settings > Analytics page in the free version.
 *
 * Shows a preview of the Pro analytics dashboard (sample data) and a link to Pro.
 * Nothing is tracked or stored here. When Pro with the analytics module is active,
 * Pro adds the real page under the same slug and this one is not registered.
 *
 * @package LoeCoder\Plugin\ProductRecommendations
 * @since   3.5.0
 */

namespace LoeCoder\Plugin\ProductRecommendations;

if (!defined('ABSPATH')) {
	exit;
}

class Analytics_Teaser {

	const SLUG = 'lpr-analytics';

	const PRO_URL = 'https://leocoder.com/leo-product-recommendations-for-woocommerce/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=upgrade&utm_content=analytics-teaser';

	/**
	 * @var Product_Recommendations
	 */
	private $base;

	private $hook = '';

	public function __construct($base) {
		$this->base = $base;

		add_action('admin_menu', array($this, 'menu'), 30);
		add_action('admin_enqueue_scripts', array($this, 'enqueue'));
	}

	/**
	 * Pro is active but too old to have the analytics module (before Pro 3.3.0).
	 */
	private function pro_needs_update() {
		return $this->base->is_pro_activated() && !defined('LPR_ANALYTICS_FILE');
	}

	public function menu() {
		// Pro with analytics registers the real page (or turned it off on purpose).
		if ($this->base->is_pro_activated() && defined('LPR_ANALYTICS_FILE')) {
			return;
		}

		$this->hook = add_submenu_page(
			'lpr-settings',
			__('Recommendation Analytics', 'leo-product-recommendations'),
			__('Analytics', 'leo-product-recommendations') . ' <span class="lpr-menu-pro">' . esc_html__('PRO', 'leo-product-recommendations') . '</span>',
			'manage_options',
			self::SLUG,
			array($this, 'render'),
			1
		);

		add_action('admin_head', array($this, 'menu_badge_css'));
	}

	public function menu_badge_css() {
		echo '<style>#adminmenu .lpr-menu-pro{display:inline-block;margin-left:4px;padding:0 5px;border-radius:3px;background:#d63638;color:#fff;font-size:9px;font-weight:600;line-height:16px;vertical-align:1px}</style>';
	}

	public function enqueue($hook) {
		if (!$this->hook || $hook !== $this->hook) {
			return;
		}

		wp_enqueue_style('lpr-analytics-teaser', $this->base->get_url('assets/css/analytics-teaser.css'), array(), $this->base->script_version());
	}

	public function render() {
		$update   = $this->pro_needs_update();
		$image    = $this->base->get_url('assets/images/pro-analytics.jpg');
		$features = array(
			__('Revenue and orders from recommended products, and their share of your store sales', 'leo-product-recommendations'),
			__('Adds to cart right from a recommendation, and after a shopper clicks through to the product page', 'leo-product-recommendations'),
			__('Results for each place: popup, cart drawer, cart page, free shipping bar, product page, checkout and thank-you page', 'leo-product-recommendations'),
			__('Best product pairs: which products shoppers buy together', 'leo-product-recommendations'),
			__('Average order value of orders with and without a recommended product', 'leo-product-recommendations'),
			__('Top recommended products, date ranges and CSV export', 'leo-product-recommendations'),
		);
		?>
		<div class="wrap lpr-teaser">
			<h1 class="lpr-teaser__title">
				<?php esc_html_e('Recommendation Analytics', 'leo-product-recommendations'); ?>
				<span class="lpr-teaser__badge"><?php esc_html_e('PRO', 'leo-product-recommendations'); ?></span>
			</h1>

			<div class="lpr-teaser__intro">
				<div class="lpr-teaser__text">
					<?php if ($update) : ?>
						<p class="lpr-teaser__lead"><?php esc_html_e('Analytics comes with Leo Product Recommendations Pro 3.3.0. Update Pro to start counting what your recommendations sell.', 'leo-product-recommendations'); ?></p>
					<?php else : ?>
						<p class="lpr-teaser__lead"><?php esc_html_e('Find out how much your recommendations really sell, and which places sell the most. The analytics dashboard is part of Leo Product Recommendations Pro.', 'leo-product-recommendations'); ?></p>
					<?php endif; ?>

					<ul class="lpr-teaser__list">
						<?php foreach ($features as $feature) : ?>
							<li><?php echo esc_html($feature); ?></li>
						<?php endforeach; ?>
					</ul>

					<p class="lpr-teaser__privacy"><?php esc_html_e('The data stays in your own store database. Nothing is sent to us or to any other service.', 'leo-product-recommendations'); ?></p>
				</div>

				<div class="lpr-teaser__cta">
					<?php if ($update) : ?>
						<a class="button button-primary button-hero" href="<?php echo esc_url(admin_url('plugins.php')); ?>"><?php esc_html_e('Update Pro', 'leo-product-recommendations'); ?></a>
					<?php else : ?>
						<a class="button button-primary button-hero" href="<?php echo esc_url(self::PRO_URL); ?>" target="_blank" rel="noopener"><?php esc_html_e('Get Pro', 'leo-product-recommendations'); ?></a>
						<span class="lpr-teaser__cta-note"><?php esc_html_e('Includes all Pro features', 'leo-product-recommendations'); ?></span>
					<?php endif; ?>
				</div>
			</div>

			<div class="lpr-teaser__preview">
				<?php if ($update) : ?>
					<img src="<?php echo esc_url($image); ?>" width="1600" height="1362" alt="<?php esc_attr_e('Preview of the Pro analytics dashboard with sample data', 'leo-product-recommendations'); ?>">
				<?php else : ?>
					<a href="<?php echo esc_url(self::PRO_URL); ?>" target="_blank" rel="noopener">
						<img src="<?php echo esc_url($image); ?>" width="1600" height="1362" alt="<?php esc_attr_e('Preview of the Pro analytics dashboard with sample data', 'leo-product-recommendations'); ?>">
					</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
