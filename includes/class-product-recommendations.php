<?php
namespace LoeCoder\Plugin\ProductRecommendations;
use LoeCoder\Plugin\Deactivation_Feedback;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * The Plugin Base Class
 *
 * @since      1.0.0
 * @author     LeoCoder
 */

final class Product_Recommendations {
	/**
	 * Cookie that carries a product added with a normal form post to the next page load.
	 *
	 * @since 3.4.0
	 */
	const ADDED_PRODUCT_COOKIE = 'lpr_added_product';

	/**
	 * Instance of class
	 *
	 * @var instance
	 */
	static protected $instance;

	/**
	 * All settings page values
	 *
	 * @var array
	 * @since      1.5.1
	 */
	static protected $settings;

	/**
	 * Per-request cache of the settings schema (pages, sections, fields).
	 *
	 * @var array|null
	 * @since 3.4.0
	 */
	static protected $settings_pages_cache = null;

	/**
	 * Per-request cache of all fields keyed by field id.
	 *
	 * @var array|null
	 * @since 3.4.0
	 */
	static protected $field_map_cache = null;

	/**
	 * Array of all products recommendations meta data
	 *
	 * @var array
	 */
	static protected $pr_meta = array();

	/**
	 * Linked products (Upsells/Cross-sells) per product, cached for the request.
	 *
	 * @since 3.5.0
	 */
	static protected $linked_cache = array();

	/**
	 * Where each recommended product came from, per source product, for this request:
	 * source ID => array(product ID => 'smart' or 'own').
	 *
	 * @var array
	 */
	static protected $origins = array();

	/**
	 * Settings that turn on WooCommerce linked products as a source.
	 *
	 * @since 3.5.0
	 */
	const LINKED_SETTINGS = array('global_linked_upsells', 'global_linked_cross_sells');

	/**
	 * More places for recommendations (cart page, Pro places).
	 *
	 * @since 3.6.0
	 * @var Places|null
	 */
	public $places = null;

	/**
	 * Plugin root file __FILE__
	 *
	 * @var string
	 */
	static protected $__FILE__;

	/**
	 * Pro Plugin __FILE__
	 * Root file of plugin pro version
	 * @var string
	 * @since      1.0.0
	 */
	static protected $__FILE__PRO__ = WP_PLUGIN_DIR . '/leo-product-recommendations-pro/leo-product-recommendations-pro.php';

	/**
	 * Plugin setting id used to save setting data
	 *
	 * @var string
	 * @since      1.0.0
	 */
	static protected $setting_id = 'lc_lpr_settings';

	/**
	 * Class constructor, initialize everything
	 * @since      1.0.0
	 */
	private function __construct($__FILE__) {
		self::$__FILE__ = $__FILE__;
		register_activation_hook(self::$__FILE__, array($this, 'on_activation'));
		register_deactivation_hook(self::$__FILE__, array($this, 'on_deactivation'));
		add_action('plugins_loaded', array($this, 'on_plugins_loaded'));
	}

	/**
	 * Action after plugin activate
	 * Enable ajax add to cart and disable redirect to cart page.
	 *
	 * @since      1.0.0
	 */
	public function on_activation() {
		update_option('woocommerce_enable_ajax_add_to_cart', 'yes');
		update_option('woocommerce_cart_redirect_after_add', 'no');
		$this->add_default_settings();
	}

	/**
	 * Action after plugin deactivate
	 *
	 * @since      1.0.0
	 * @return void
	 */
	public function on_deactivation() {
		//do nothing
	}

	/**
	 * Action after plugin deactivate
	 *
	 * @since      1.0.0
	 * @return void
	 */
	public function add_default_settings() {
		$settings = $this->get_settings();
		$settings = !empty($settings) ? $settings : array();
		$default_settings = $this->get_default_settings();

		// Linked products are on for new installs only. A store that already has settings
		// keeps its current popups until the owner turns them on (also on reactivation).
		if (!empty($settings)) {
			foreach (self::LINKED_SETTINGS as $key) {
				if (!array_key_exists($key, $settings)) {
					$settings[$key] = '';
				}
			}
			// The modern popup is for new installs; a store with settings keeps the classic one.
			if (!array_key_exists('popup_design', $settings)) {
				$settings['popup_design'] = 'classic';
			}
		}
		update_option($this->get_settings_id(), array_merge($default_settings, $settings));
	}

	/**
	 * Setup plugin once all other plugins are loaded
	 *
	 * @since      1.0.0
	 * @return void
	 */
	public function on_plugins_loaded() {
		if (!$this->has_satisfied_dependencies()) {
			add_action('admin_notices', array($this, 'render_dependencies_notice'));
			return;
		}

		// before going to action
		// used this hook in pro plugin
		do_action('lpr_before_action');

		$this->includes();
		$this->hooks();

		// Deactivation feedback. Its labels are translated, so it waits for init
		// (WordPress 6.7+ warns when translations load earlier).
		add_action('init', array($this, 'deactivation_feedback'));
	}

	/**
	 * Returns true if all dependencies for the plugin are loaded
	 *
	 * @since      1.0.0
	 * @return bool
	 */
	protected function has_satisfied_dependencies() {
		$dependency_errors = $this->get_dependency_errors();
		return 0 === count($dependency_errors);
	}

	/**
	 * Get an array of dependency error messages
	 *
	 * @since      1.0.0
	 * @return array all dependency error message.
	 */
	protected function get_dependency_errors() {
		$errors = array();
		$wordpress_version = get_bloginfo('version');
		$minimum_wordpress_version = $this->get_min_wp();
		$minimum_woocommerce_version = $this->get_min_wc();
		$minium_php_version = $this->get_min_php();

		$wordpress_minimum_met = version_compare($wordpress_version, $minimum_wordpress_version, '>=');
		$woocommerce_minimum_met = class_exists('WooCommerce') && version_compare(WC_VERSION, $minimum_woocommerce_version, '>=');
		$php_minimum_met = version_compare(phpversion(), $minium_php_version, '>=');

		if (!$woocommerce_minimum_met) {

			$errors[] = sprintf(
				/* translators: 1. link of plugin, 2. plugin version. */
				__('Leo Product Recommendations requires <a href="%1$s">WooCommerce</a> %2$s or greater to be installed and active.', 'leo-product-recommendations'),
				'https://wordpress.org/plugins/woocommerce/',
				$minimum_woocommerce_version
			);
		}

		if (!$wordpress_minimum_met) {
			$errors[] = sprintf(
				/* translators: 1. link of wordpress 2. version of WordPress. */
				__('Leo Product Recommendations requires <a href="%1$s">WordPress</a> %2$s or greater to be installed and active.', 'leo-product-recommendations'),
				'https://wordpress.org/',
				$minimum_wordpress_version
			);
		}

		if (!$php_minimum_met) {
			$errors[] = sprintf(
				/* translators: 1. version of php */
				__('Leo Product Recommendations needs <strong>PHP %s</strong> or newer. Please update PHP on your server.', 'leo-product-recommendations'),
				$minium_php_version
			);
		}

		return $errors;
	}

	/**
	 * Notify about plugin dependency
	 *
	 * @since      1.0.0
	 * @return void
	 */
	public function render_dependencies_notice() {
		$message = $this->get_dependency_errors();
		printf('<div class="error"><p>%s</p></div>', wp_kses_post(implode(' ', $message)));
	}

	/**
	 * Include add-on features with this plugin
	 *
	 * @since      1.0.0
	 * @return void
	 */
	public function includes() {
		require_once $this->get_path('includes/class-popup-modern.php');

		$this->admin_ajax(); // handle all admin ajax request

		$this->plugin_action_links(); // add action link, example: Go Pro->, Settings->

		// One settings framework for Free and Pro. An older Pro (3.1.x and below) ships its
		// own copy of the framework, so Free must stay out of its way in that case.
		if ($this->use_shared_settings()) {
			$this->plugin_settings($this);
		}
	}

	/**
	 * Handle ajax request 
	 * for product recommendations selection panel
	 * @since      1.0.0
	 * @return void
	 */
	public function admin_ajax() {
		if (!class_exists(Admin_Ajax::class)) {
			require_once $this->get_path('includes/class-admin-ajax.php');
		}
		new Admin_Ajax();
	}

	/**
	 * Add plugin action link
	 * @since      1.0.0
	 */
	public function plugin_action_links() {
		add_action('plugin_action_links_' . plugin_basename(self::$__FILE__), function ($links) {
			$link_before = array(
				'settings' => '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=lpr-settings')) . '">' . __('Settings', 'leo-product-recommendations') . '</a>',
				'documentation' => '<a href="' . esc_url('https://leocoder.com/docs/woocommerce-product-recommendations/add-recommended-products/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=docs&utm_content=plugins-list') . '" target="_blank" rel="noopener noreferrer nofollow">' . __('Docs', 'leo-product-recommendations') . '</a>',
			);

			if (!$this->has_pro_plugin()) {
				$link_after = array(
					'go-pro' => '<a href="' . esc_url('https://leocoder.com/leo-product-recommendations-for-woocommerce/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=upgrade&utm_content=plugins-list') . '" target="_blank" rel="noopener noreferrer nofollow" style="color: red; font-weight: bold;">' . __('Go Pro', 'leo-product-recommendations') . '</a>',
				);
				return array_merge($link_before, $links, $link_after);
			}

			return array_merge($link_before, $links);
		});
	}

	/**
	 * Plugin settings page
	 * @since      1.0.0
	 * @return void
	 */
	public function plugin_settings($self) {
		if (!class_exists(Settings_Page::class)) {
			require_once $this->get_path('includes/class-settings-page.php');
		}

		new Settings_Page($self);
	}

	/**
	 * Added all action hooks
	 *
	 * @since      1.0.0
	 * @return void
	 */
	public function hooks() {
		// Include required admin scripts
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));

		// LPR Settings > Analytics: preview of the Pro dashboard. No tracking in the free version.
		if (is_admin()) {
			require_once $this->get_path('includes/class-analytics-teaser.php');
			new Analytics_Teaser($this);

			// Simple settings and live preview on the settings page.
			require_once $this->get_path('includes/class-settings-ui.php');
			new Settings_UI($this);
		}

		// More places for recommendations: cart page (Pro adds more).
		require_once $this->get_path('includes/class-places.php');
		$this->places = new Places($this);

		// One-time safety copy of the settings before the shared framework saves anything.
		add_action('admin_init', array($this, 'maybe_backup_settings'));

		// Include required front-end scripts
		add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts')); 

		// Add recommendations tabs in product data table
		add_action('woocommerce_product_data_tabs', array($this, 'product_data_tabs'));
		add_action('woocommerce_product_data_panels', array($this, 'product_data_panels'));
		add_action('save_post', array($this, 'on_save_post'));

		// Fetch popup modal data
		add_action('wp_ajax_lpr_get_popup_data', array($this, 'lpr_get_popup_data'));
		add_action('wp_ajax_nopriv_lpr_get_popup_data', array($this, 'lpr_get_popup_data'));
		// Modern popup: subtotal and free shipping bar after adding from the popup
		add_action('wp_ajax_lpr_popup_summary', array(Popup_Modern::class, 'ajax_summary'));
		add_action('wp_ajax_nopriv_lpr_popup_summary', array(Popup_Modern::class, 'ajax_summary'));
		// Tracking data for the popup products (GTM4WP), built in a front-end request
		add_action('wc_ajax_lpr_tracking_data', array($this, 'lpr_tracking_data'));

		// Ajax add to cart
		add_action('wp_ajax_lc_ajax_add_to_cart', array($this, 'ajax_add_to_cart'));
		add_action('wp_ajax_nopriv_lc_ajax_add_to_cart', array($this, 'ajax_add_to_cart'));

		// Remember a product added with a normal form post so the popup opens after the reload
		add_action('woocommerce_add_to_cart', array($this, 'remember_added_product'), 10, 2);

		// fix woocommerce nonce issue logged out user
		add_filter('nonce_user_logged_out', array($this, 'nonce_fix'), 100, 2);

		// include popup modal template in footer
		add_action('after_setup_theme', array($this, 'include_templates')); 

		// load css from plugin style setting
		if (!$this->is_pro_activated()) {
			add_action('wp_head', array($this, 'settings_css')); 
		}

		// cart item count for popup cart
		add_filter('woocommerce_add_to_cart_fragments', array($this, 'cart_items_count'));
	}

	/**
	 * Enqueue all backend admin related scripts and styles
	 *
	 * @since      1.0.0
	 * @return void
	 */
	public function admin_enqueue_scripts() {
		$version = $this->script_version();
		$screen = get_current_screen();
		$screen_id = $screen ? (string) $screen->id : '';
		$is_product = 'product' === $screen_id;
		$is_settings = false !== strpos($screen_id, 'lpr-settings');

		// Only the product editor and the plugin's own pages use these.
		if (!$is_product && !$is_settings) {
			return;
		}
		wp_enqueue_editor();

		// Product edit screen: the Recommendations tab gets the same look as the settings (Free and Pro).
		if ($screen && 'product' === $screen->id) {
			wp_enqueue_style('lpr-panel-modern', $this->get_url('assets/css/panel-modern.css'), array(), $version . '.' . (int) @filemtime($this->get_path('assets/css/panel-modern.css')));
		}

		if (!$this->is_pro_activated() && $is_product) {
			$panel_asset = $this->script_asset('panel', array('wp-element', 'wp-components', 'wp-i18n', 'jquery'));
			wp_enqueue_script('selection-panel-script', $this->get_url('assets/js/panel.min.js'), $panel_asset['dependencies'], $panel_asset['version'], true);
			wp_set_script_translations( 'selection-panel-script', 'leo-product-recommendations' );
			wp_localize_script('selection-panel-script', 'lc_pr_panel_data', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('lc-panel-security'),
				'pro_image' => $this->get_url('assets/images/pro-feature.jpg'),
				'pro_link' => esc_url('https://leocoder.com/leo-product-recommendations-for-woocommerce/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=upgrade&utm_content=product-panel'),
			));
			wp_enqueue_style('selection-panel-style', $this->get_url('assets/css/panel.css'), '', $version);
		}

		//load script in setting page
		if ($screen_id === 'toplevel_page_lpr-settings') {
			// color picker
			wp_enqueue_script('spectrum', $this->get_url('assets/js/color-picker/spectrum.js'), array('jquery'), $version, true);
			wp_enqueue_style('lpr-spectrum', $this->get_url('assets/js/color-picker/spectrum.css'), array(), $version);

			//help popup
			wp_enqueue_script('lity-popup', $this->get_url('assets/js/lity-popup/lity.min.js'), array('jquery'), '2.4.1', true);
			wp_enqueue_style('lity-popup', $this->get_url('assets/js/lity-popup/lity.min.css'), array(), '2.4.1');

			//select 2 
			wp_enqueue_script('lpr-select2', $this->get_url('assets/js/select2/select2.min.js'), array('spectrum'), $version, true);
			wp_enqueue_style('lpr-select2', $this->get_url('assets/js/select2/select2.min.css'), array(), $version);

			//setting 
			$settings_asset = $this->script_asset('settings', array('jquery', 'spectrum', 'wp-i18n'));
			wp_enqueue_script('lpr-settings', $this->get_url('assets/js/settings.min.js'), $settings_asset['dependencies'], $settings_asset['version'], true);
			wp_enqueue_style('lpr-settings', $this->get_url('assets/css/settings.css'), array(), $version);


		}
	}

	/**
	 * Enqueue all front-end related scripts and styles
	 *
	 * @since      1.0.0
	 * @return void
	 */
	public function wp_enqueue_scripts() {
		$version = $this->script_version();
		$settings = $this->get_settings();
		$layout_type = ($this->is_pro_activated() && !empty($settings['layout_type'])) ? $settings['layout_type'] : 'grid';

		$modal_asset = $this->script_asset('modal', array('jquery', 'wp-i18n'));
		wp_enqueue_script('lpr-modal', $this->get_url('assets/js/modal.min.js'), $modal_asset['dependencies'], $modal_asset['version'], true);
		wp_set_script_translations( 'lpr-modal', 'leo-product-recommendations' );

		wp_localize_script('lpr-modal', 'lc_ajax_modal', array(
			'url' => admin_url('admin-ajax.php'),
			/**
			 * Load tracking data for the popup products from a front-end request.
			 * On by default when GTM4WP is active.
			 */
			'datalayer' => $this->datalayer_enabled(),
			'tracking_url' => (class_exists('WC_AJAX') && apply_filters('lpr_popup_tracking_data', defined('GTM4WP_VERSION'))) ? \WC_AJAX::get_endpoint('lpr_tracking_data') : '',
			'nonce' => wp_create_nonce('lc-ajax-modal'),
			'popup_view_times' => $this->get_setting('popup_view_times'),
			'layout_type' => $layout_type,
			'added_cookie' => array(
				'name' => self::ADDED_PRODUCT_COOKIE,
				'path' => COOKIEPATH ? COOKIEPATH : '/',
				'domain' => COOKIE_DOMAIN ? COOKIE_DOMAIN : '',
			),
			/**
			 * Show the after-reload popup on the cart and checkout pages too
			 * (for stores that redirect to the cart after adding). Off by default.
			 */
			'popup_on_cart' => (bool) apply_filters('lpr_popup_after_reload_on_cart', false),
			'is_cart_page' => $this->is_cart_or_checkout_page(),
			'design' => Popup_Modern::design($this),
		));

		// if (is_product()) { // disabled condition so that it work for quick view
		$ajax_add_to_cart_asset = $this->script_asset('ajax-add-to-cart', array('jquery', 'wp-i18n'));
		wp_enqueue_script('lpr-ajax-add-to-cart', $this->get_url('assets/js/ajax-add-to-cart.min.js'), $ajax_add_to_cart_asset['dependencies'], $ajax_add_to_cart_asset['version'], true);
		wp_set_script_translations( 'lpr-ajax-add-to-cart', 'leo-product-recommendations' );
		wp_localize_script('lpr-ajax-add-to-cart', 'lc_ajax', array(
			'url' => admin_url('admin-ajax.php'),
			'popup_view_times' => $this->get_setting('popup_view_times'),
			/**
			 * Marker of themes that add the single product form to the cart with their own
			 * AJAX call. Forms containing it are left to the theme.
			 *
			 * @since 3.4.0
			 */
			'theme_ajax_selector' => (string) apply_filters('lpr_theme_ajax_add_to_cart_selector', '[data-add-to-cart="ajax"]'),
			/**
			 * Keep adding single product forms with AJAX even when the popup limit is reached
			 * or the popup is replaced (Pro cart drawer).
			 *
			 * @since 3.6.0
			 */
			'ajax_always' => (bool) apply_filters('lpr_ajax_add_to_cart_always', false),
		));

		wp_enqueue_style('lpr-modal', $this->get_url('assets/css/modal.css'), array(), $version);
		if (Popup_Modern::is_modern($this)) {
			wp_enqueue_style('lpr-popup-modern', $this->get_url('assets/css/popup-modern.css'), array('lpr-modal'), $version . '.' . (int) @filemtime($this->get_path('assets/css/popup-modern.css')));
			wp_enqueue_script('wc-add-to-cart');
		}
		//recommend variable and group product add to cart
		if ($this->get_setting('variable_add_to_cart')) {
			wp_enqueue_script('wc-add-to-cart-variation');
		}
	}

	/**
	 * Modal CSS
	 *
	 * @since      1.0.0
	 */
	public function settings_css() {
		// grid
		// max(1, ...) prevents a DivisionByZeroError when a value is saved as 0 or empty.
		$items_desktop = max(1, (int) $this->get_setting('grid_lg_items'));
		$items_tablet = max(1, (int) $this->get_setting('grid_md_items'));
		$items_mobile = max(1, (int) $this->get_setting('grid_sm_items'));
		$grid_column_gap = (int) $this->get_setting('grid_column_gap') + 1;
		$desktop_item_width = 100 / $items_desktop;
		$tablet_item_width = 100 / $items_tablet;
		$mobile_item_width = 100 / $items_mobile;

		// custom css
		$custom_css = $this->get_setting('custom_style') ? $this->get_setting('custom_style') : '';
		?>
		<style id="lpr-settings-css-front-end">
			.lpr-modal .lpr-modal-content ul.recommended-products-list {
				margin: 0 <?php echo esc_attr(-$grid_column_gap / 2); ?>px !important;
			}
			.lpr-modal .lpr-modal-content ul.recommended-products-list li.single-lpr {
				flex: 0 0 calc(<?php echo esc_attr($desktop_item_width . '% - ' . $grid_column_gap . 'px'); ?>);
				width: calc(<?php echo esc_attr($desktop_item_width . '% - ' . $grid_column_gap . 'px'); ?>);
				margin-left: <?php echo esc_attr($grid_column_gap / 2); ?>px !important;
				margin-right: <?php echo esc_attr($grid_column_gap / 2); ?>px !important;
			}
			@media screen and (max-width: 991px) {
				.lpr-modal .lpr-modal-content ul.recommended-products-list li.single-lpr {
					flex: 0 0 calc(<?php echo esc_attr($tablet_item_width . '% - ' . $grid_column_gap . 'px'); ?>);
					width: calc(<?php echo esc_attr($tablet_item_width . '% - ' . $grid_column_gap . 'px'); ?>);
					margin-left: <?php echo esc_attr($grid_column_gap / 2); ?>px !important;
					margin-right: <?php echo esc_attr($grid_column_gap / 2); ?>px !important;
				}
			}
			@media screen and (max-width: 767px) {
				.lpr-modal .lpr-modal-content ul.recommended-products-list li.single-lpr {
					flex: 0 0 calc(<?php echo esc_attr($mobile_item_width . '% - ' . $grid_column_gap . 'px'); ?>);
					width: calc(<?php echo esc_attr($mobile_item_width . '% - ' . $grid_column_gap . 'px'); ?>);
					margin-left: <?php echo esc_attr($grid_column_gap / 2); ?>px !important;
					margin-right: <?php echo esc_attr($grid_column_gap / 2); ?>px !important;
				}
			}
			<?php echo wp_strip_all_tags($custom_css); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS: no tags, so it can't leave the style element; esc_html would break selectors like a > b. ?>
		</style>
	<?php

	}

	/**
	 * Add new tab in woocommerce product data tabes
	 * 
	 * @since      1.9.1
	 * @return void
	 */

	public function product_data_tabs($tabs) {
		$tabs['lpr-product-recommendations'] = [
			'label' => __('Recommendations', 'leo-product-recommendations'),
			'target' => 'lpr_product_recommendations_panel',
			'class' => ['hide_if_external'],
		];
		return $tabs;
	}

	/**
	 * Add fields in product recommendations tab
	 * 
	 * @since      1.9.1
	 * @return void
	 */

	public function product_data_panels() {
		global $post;
		$this->product_selection($post);
	}

	/**
	 * Include Products Recommendations Panel
	 *
	 * @since      1.0.0
	 * @return void
	 */
	public function product_selection($post) {
		include_once $this->get_path('includes/option-select-products.php');
	}

	/**
	 * Get url of plugin file
	 *
	 * @since      1.0.0
	 * @return     URL link of file.
	 * @param      string File name with folder path.
	 */
	public function get_url($file = '') {
		return plugin_dir_url(self::$__FILE__) . $file;
	}

	/**
	 * Get path of plugin file 
	 *
	 * @since      1.0.0
	 * @param      string relative path of plugin file
	 * @return     string full path of plugin file
	 */
	public function get_path($file_path) {
		return plugin_dir_path(self::$__FILE__) . $file_path;
	}

	/**
	 * Get template path of popup modal 
	 *
	 * @since      1.0.0
	 * @param      string File name with folder path.
	 * @return     string Full of template from theme or pro plugin or plugin.
	 */
	public function get_templates_path($file_path) {
		//from theme
		$theme_tmpl = get_stylesheet_directory() . '/lpr/' . $file_path;
		if (file_exists($theme_tmpl)) {
			return $theme_tmpl;
		}

		//from pro version
		if ($this->is_pro_activated()) {
			$pro_template = plugin_dir_path(self::$__FILE__PRO__) . $file_path;
			if (file_exists($pro_template)) {
				return $pro_template;
			}
		}

		//from free version
		return plugin_dir_path(self::$__FILE__) . $file_path;
	}

	/**
	 * Allowed tags and attributes for the inline SVG icons in the popup templates
	 * (wp_kses_post() strips <svg>, which hid the cart and check icons).
	 *
	 * @since 3.4.0
	 * @return array wp_kses() allowed HTML.
	 */
	public static function svg_allowed_html() {
		$common = array(
			'id' => true,
			'class' => true,
			'fill' => true,
			'transform' => true,
			'data-name' => true,
		);

		return array(
			'svg' => array_merge($common, array(
				'xmlns' => true,
				'version' => true,
				'viewbox' => true,
				'width' => true,
				'height' => true,
				'aria-hidden' => true,
				'focusable' => true,
				'role' => true,
			)),
			'g' => $common,
			'path' => array_merge($common, array('d' => true)),
			'circle' => array_merge($common, array('cx' => true, 'cy' => true, 'r' => true)),
			'rect' => array_merge($common, array('x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true)),
			'polygon' => array_merge($common, array('points' => true)),
			'title' => array(),
		);
	}

	/**
	 * Get plugin slug
	 *
	 * @since      1.0.0
	 * @return     string slug of plugin
	 */
	public function get_slug() {
		return dirname(plugin_basename(self::$__FILE__));
	}

	/**
	 * Save product recommendations meta data
	 *
	 * @since      1.0.0
	 * @return  void;
	 */
	public function on_save_post($id) {
		// Products only, not autosaves or revisions, and only for users who can edit this product.
		if ('product' !== get_post_type($id) || wp_is_post_autosave($id) || wp_is_post_revision($id) || !current_user_can('edit_post', $id)) {
			return;
		}

		$nonce = isset($_POST['lc_pr_panel_nonce']) ? sanitize_text_field(wp_unslash($_POST['lc_pr_panel_nonce'])) : '';
		$is_secure = !empty($_POST['_lc_lpr_data']) && !empty($nonce) && wp_verify_nonce($nonce, 'lc-panel-security');

		if ($is_secure) {
			$panel_data = isset($_POST['_lc_lpr_data']) ? wp_unslash($_POST['_lc_lpr_data']) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- array, each field is validated and sanitized below.
			$panel_data = is_array($panel_data) ? $panel_data : array();
			$entry_data = array();

			// heading type
			if (isset($panel_data['heading_type'])) {
				$allowed_heading_type = array('heading', 'article');
				$heading_type = sanitize_key($panel_data['heading_type']);

				if (in_array($heading_type, $allowed_heading_type, true)) {
					$entry_data['heading_type'] = $heading_type;
				}

			}

			// normal heading
			if (isset($panel_data['heading'])) {
				$html_permission = array(
					'span' => array('class'),
					'b' => array(),
					'strong' => array(),
					'i' => array(),
					'br' => array(),
				);
				$entry_data['heading'] = wp_kses($panel_data['heading'], $html_permission);
			}

			// heading article
			if (isset($panel_data['heading_article'])) {
				$entry_data['heading_article'] = wp_kses_post($panel_data['heading_article']);
			}

			// select by type
			if (isset($panel_data['type'])) {
				$entry_data['type'] = sanitize_key($panel_data['type']);
			}

			//selected product
			if (isset($panel_data['products']) && is_array($panel_data['products'])) {
				$recommend_products = array_map(function ($id) {
					return (int) $id;
				}, $panel_data['products']);

				$entry_data['products'] = $recommend_products;
			}

			//categories
			if (isset($panel_data['categories']) && is_array($panel_data['categories'])) {
				$recommend_categories = array_map(function ($id) {
					return (int) $id;
				}, $panel_data['categories']);

				$entry_data['categories'] = $recommend_categories;
			}

			//tags
			if (isset($panel_data['tags']) && is_array($panel_data['tags'])) {
				$recommend_tags = array_map(function ($id) {
					return (int) $id;
				}, $panel_data['tags']);

				$entry_data['tags'] = $recommend_tags;
			}

			//orderby
			if (isset($panel_data['orderby'])) {
				$allowed_orderby = array('rand', 'newest', 'oldest', 'lowprice', 'highprice', 'popularity', 'rating');
				$orderby = sanitize_key($panel_data['orderby']);

				if (in_array($orderby, $allowed_orderby, true)) {
					$entry_data['orderby'] = $orderby;
				}
			}

			//is sale
			if (isset($panel_data['sale'])) {
				$entry_data['sale'] = (bool) $panel_data['sale'];
			}

			//number of products
			if (isset($panel_data['number'])) {
				$entry_data['number'] = (int) $panel_data['number'];
			}

			update_post_meta($id, '_lc_lpr_data', $entry_data);
		}
	}

	/**
	 * Ajax callback for query popup-modal data
	 *
	 * @since      1.0.0
	 * @return  void;
	 */

	public function lpr_get_popup_data() {
		$nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$product_id = isset($_REQUEST['product_id']) ? absint($_REQUEST['product_id']) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if (empty($nonce) || !wp_verify_nonce($nonce, 'lc-ajax-modal') || empty($product_id)) {
			wp_send_json_error(array('message' => 'Bad request'), 400);
		}

		// Public endpoint: only products a visitor can see (no drafts, private or other post types).
		if (!$this->is_public_product($product_id)) {
			wp_die();
		}

		$recommended_products_id = $this->get_recommended_products_id($product_id);
		$cart_items = $this->get_cart_items();

		$recommended_products_id  = array_filter($recommended_products_id, function($id) use($cart_items) {
			return !in_array($id, $cart_items);
		});

		if(!count($recommended_products_id)) wp_die();

		$template_data = $this->get_template_data($product_id, $recommended_products_id);
		if (Popup_Modern::is_modern($this)) {
			$added = Popup_Modern::added_item($product_id);
			include $this->get_templates_path('templates/modern-popup.php');
		} else {
			include $this->get_templates_path('templates/template-modal-content.php');
		}

		if ($this->datalayer_enabled()) {
			$this->print_datalayer_data($product_id, $recommended_products_id);
		}

		wp_die();
	}

	/**
	 * Push the lpr_popup_* dataLayer events (only when window.dataLayer exists).
	 *
	 * @since 3.4.0
	 * @return bool
	 */
	public function datalayer_enabled() {
		return (bool) apply_filters('lpr_popup_datalayer', true);
	}

	/**
	 * Which rule picked the recommendations for a product.
	 *
	 * @since 3.4.0
	 * @param int $product_id
	 * @return string global, manual, dynamic or empty
	 */
	public function get_recommendation_type($product_id) {
		if ($this->uses_linked_products($product_id)) {
			return 'linked';
		}
		if ($this->is_active_global($product_id)) {
			return 'global';
		}
		if ($this->is_smart_selection($product_id)) {
			return 'smart';
		}
		if ($this->is_manually_selection($product_id)) {
			return 'manual';
		}
		if ($this->is_dynamic_selection($product_id)) {
			return 'dynamic';
		}
		return '';
	}

	/**
	 * GA4 item for a product, used by the lpr_popup_* dataLayer events.
	 *
	 * @since 3.4.0
	 * @param \WC_Product $product
	 * @return array
	 */
	public function get_datalayer_item($product) {
		$id_type = $this->get_datalayer_id_type();
		$item_id = ('sku' === $id_type && $product->get_sku()) ? $product->get_sku() : (string) $product->get_id();
		$parent_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();

		$item = array(
			'item_id' => $item_id,
			'item_name' => html_entity_decode(wp_strip_all_tags($product->get_name()), ENT_QUOTES, get_bloginfo('charset')),
			'price' => (float) wc_format_decimal(wc_get_price_to_display($product), wc_get_price_decimals()),
			'quantity' => 1,
		);

		$categories = wc_get_product_terms($parent_id, 'product_cat', array('fields' => 'names'));
		foreach (array_slice((array) $categories, 0, 5) as $i => $name) {
			$item[0 === $i ? 'item_category' : 'item_category' . ($i + 1)] = html_entity_decode($name, ENT_QUOTES, get_bloginfo('charset'));
		}

		if (taxonomy_exists('product_brand')) {
			$brands = wc_get_product_terms($parent_id, 'product_brand', array('fields' => 'names'));
			if (!empty($brands)) {
				$item['item_brand'] = html_entity_decode($brands[0], ENT_QUOTES, get_bloginfo('charset'));
			}
		}

		/**
		 * Filters a GA4 item in the lpr_popup_* dataLayer events.
		 *
		 * @since 3.4.0
		 * @param array       $item
		 * @param \WC_Product $product
		 */
		return apply_filters('lpr_datalayer_item', $item, $product);
	}

	/**
	 * Product ID or SKU as item_id in the dataLayer events.
	 *
	 * @since 3.4.0
	 * @return string id or sku
	 */
	public function get_datalayer_id_type() {
		return 'sku' === apply_filters('lpr_datalayer_item_id_type', 'id') ? 'sku' : 'id';
	}

	/**
	 * Data for the lpr_popup_* dataLayer events, printed with the popup HTML as JSON.
	 * modal.js builds the events from it and from the popup cards.
	 *
	 * @since 3.4.0
	 * @param int   $product_id              Product added to the cart (popup source).
	 * @param array $recommended_products_id
	 * @return void
	 */
	public function print_datalayer_data($product_id, $recommended_products_id, $list = array()) {
		$items = array();

		foreach ($recommended_products_id as $id) {
			$product = wc_get_product($id);
			if (!$product) {
				continue;
			}

			$item = $this->get_datalayer_item($product);

			if ($product->is_type('grouped')) {
				$item['children'] = array();
				foreach ($product->get_children() as $child_id) {
					$child = wc_get_product($child_id);
					if ($child) {
						$item['children'][$child_id] = $this->get_datalayer_item($child);
					}
				}
			}

			$items[$id] = $item;
		}

		$source = wc_get_product($product_id);

		$data = array(
			'currency' => get_woocommerce_currency(),
			'item_list_id' => !empty($list['id']) ? (string) $list['id'] : 'lpr_popup',
			'item_list_name' => !empty($list['name']) ? (string) $list['name'] : (string) apply_filters('lpr_datalayer_item_list_name', 'Product Recommendations Popup'),
			'id_type' => $this->get_datalayer_id_type(),
			'source' => array(
				'source_product_id' => (string) $product_id,
				'source_product_name' => $source ? html_entity_decode(wp_strip_all_tags($source->get_name()), ENT_QUOTES, get_bloginfo('charset')) : '',
				'recommendation_type' => $this->get_recommendation_type($product_id),
			),
			'items' => (object) $items,
		);

		// Places with products from several sources (cart, checkout): product => source.
		if (!empty($list['sources']) && is_array($list['sources'])) {
			$data['sources'] = (object) array_map('strval', $list['sources']);
		}

		echo '<script type="application/json" class="lpr-datalayer-data">' . wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . '</script>';
	}

	/**
	 * Tracking markup for the popup products, for plugins such as GTM4WP.
	 *
	 * The popup HTML comes from admin-ajax, where GTM4WP (and similar plugins) don't load
	 * their front-end code, so the popup products have no tracking data and adding them
	 * pushes no add_to_cart. This runs on WooCommerce's front-end endpoint (?wc-ajax=)
	 * and returns what the standard WooCommerce hooks output for each product. modal.js
	 * copies only GTM4WP's own elements and data attributes into the popup, so the popup
	 * layout never changes. The popup itself stays on admin-ajax: themes such as Astra
	 * rearrange the product loop hooks on front-end requests, which would drop the title
	 * and price from the popup cards.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public function lpr_tracking_data() {
		$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

		if (empty($nonce) || !wp_verify_nonce($nonce, 'lc-ajax-modal')) {
			wp_send_json_error(array('message' => 'Bad request'), 400);
		}

		$ids = isset($_POST['product_ids']) ? (array) wp_unslash($_POST['product_ids']) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$ids = array_slice(array_unique(array_filter(array_map('absint', $ids))), 0, 30);

		global $product, $post;
		$original_product = $product;
		$original_post = $post;
		$data = array();

		foreach ($ids as $id) {
			$item = wc_get_product($id);

			if (!$item || !$this->is_public_product($id)) {
				continue;
			}

			$post = get_post($id); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			setup_postdata($post);
			$product = $item; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

			ob_start();
			do_action('woocommerce_after_add_to_cart_button');
			$form = ob_get_clean();

			$link = apply_filters('woocommerce_loop_add_to_cart_link', '<a href="#" class="lpr-tracking-probe"></a>', $item, array());

			$data[$id] = array(
				'form' => $form,
				'link' => is_string($link) ? $link : '',
			);
		}

		$product = $original_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$post = $original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		wp_reset_postdata();

		wp_send_json_success($data);
	}

	/**
	 * When a product is added with a normal form post (or an ?add-to-cart= link) there is no
	 * AJAX added_to_cart event, so save the product ID in a short-lived cookie. modal.js reads
	 * it on the next page load, opens the popup once and deletes it. A cookie (not the page
	 * HTML) carries it so page caching can't show the popup to other visitors.
	 *
	 * @since 3.4.0
	 * @param string $cart_item_key
	 * @param int $product_id
	 * @return void
	 */
	public function remember_added_product($cart_item_key, $product_id) {
		// AJAX adds (archive buttons, this plugin's own call) open the popup straight away.
		if (wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST) || is_admin() || headers_sent()) {
			return;
		}

		// A form posted in the background by a theme script (Blocksy's AJAX add to cart,
		// fetch() or XHR) is not a page load; that script fires added_to_cart itself.
		if ($this->is_background_request()) {
			return;
		}

		// Only for adds the visitor asked for, not products other code adds on page load.
		if (!isset($_REQUEST['add-to-cart']) || !is_numeric(wp_unslash($_REQUEST['add-to-cart']))) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- only checked to be numeric.
			return;
		}

		$added_id = absint(wp_unslash($_REQUEST['add-to-cart'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if (!$added_id || !apply_filters('lpr_popup_after_reload', true, $added_id, $product_id)) {
			return;
		}

		setcookie(self::ADDED_PRODUCT_COOKIE, (string) $added_id, time() + 5 * MINUTE_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), false);
	}

	/**
	 * Request sent by a script (fetch/XHR) rather than a page load in the browser.
	 *
	 * @since 3.4.0
	 * @return bool
	 */
	private function is_background_request() {
		if (isset($_REQUEST['blocksy_add_to_cart'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

		$requested_with = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? strtolower(sanitize_text_field(wp_unslash($_SERVER['HTTP_X_REQUESTED_WITH']))) : '';
		if ('xmlhttprequest' === $requested_with) {
			return true;
		}

		// Browsers send Sec-Fetch-Mode "navigate" for page loads and form posts,
		// "cors"/"same-origin"/"no-cors" for fetch() and XHR.
		$fetch_mode = isset($_SERVER['HTTP_SEC_FETCH_MODE']) ? strtolower(sanitize_text_field(wp_unslash($_SERVER['HTTP_SEC_FETCH_MODE']))) : '';
		if ('' !== $fetch_mode && 'navigate' !== $fetch_mode) {
			return true;
		}

		return false;
	}

	/**
	 * Product that anyone may see: a published product (or variation of one) that is
	 * not hidden from the catalog, or any product the current user can edit.
	 *
	 * @since 3.4.0
	 * @param int $product_id
	 * @return bool
	 */
	public function is_public_product($product_id) {
		$product = wc_get_product($product_id);
		if (!$product) {
			return false;
		}

		if (current_user_can('edit_post', $product_id)) {
			return true;
		}

		$check_id = $product->get_parent_id() ? $product->get_parent_id() : $product_id;
		$check = $product->get_parent_id() ? wc_get_product($check_id) : $product;

		return $check && 'publish' === get_post_status($check_id) && !post_password_required($check_id) && 'hidden' !== $check->get_catalog_visibility();
	}

	/**
	 * Cart or checkout page, including pages that use the Cart/Checkout blocks
	 * but aren't the page set in WooCommerce settings.
	 *
	 * @since 3.4.0
	 * @return bool
	 */
	public function is_cart_or_checkout_page() {
		if ((function_exists('is_cart') && is_cart()) || (function_exists('is_checkout') && is_checkout())) {
			return true;
		}

		return is_singular() && (has_block('woocommerce/cart') || has_block('woocommerce/checkout'));
	}

	/**
	 * Ajax add to cart 
	 *
	 * @since      1.0.0
	 * @return  json
	 */
	public function ajax_add_to_cart() {
		if (!class_exists(Ajax_Add_To_Cart::class)) {
			include $this->get_path('includes/class-ajax-add-to-cart.php');
		}
		// Same as WooCommerce's own AJAX add to cart: no nonce, so it keeps working on cached pages.
		// Ajax_Add_To_Cart reads and sanitizes only the fields it needs.
		new Ajax_Add_To_Cart($_POST); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Get cart items 
	 *
	 * @since      1.0.0
	 * @return  array with cart items
	 */
	public function get_cart_items() {
		$product_ids = array();

		if (WC()->cart) {
			foreach (WC()->cart->get_cart() as $cart_item) {
				$product_ids[] = $cart_item['product_id'];
			}
		}

		return $product_ids;
	}

	/**
	 * Include modal templates
	 * @since      1.0.0
	 * @return  void;
	 */
	public function include_templates() {
		add_action('wp_footer', function() {
			include_once($this->get_templates_path('templates/modal-modal.php'));
		});
	}

	/**
	 * Get all require data for popup modal
	 *
	 * @param int $product_id
	 * @param array $recommended_products_id
	 * @return array heading of recommendation products
	 * @since   1.0.0
	 */
	public function get_template_data($product_id, $recommended_products_id) {
		$template_data = array();

		//product_id
		$template_data['product_id'] = $product_id;

		//recommended_products_id
		$template_data['recommended_products_id'] = $recommended_products_id;

		//modal_heading
		$pr_data = $this->get_pr_data($product_id); //recommendations data for product
		$modal_heading = (!!$pr_data && isset($pr_data['heading'])) ? $pr_data['heading'] : '';
		$is_article_heading = ($this->get_setting('heading_type') === 'default_heading_description');
		$default_heading = $is_article_heading ? $this->get_setting('default_heading_description') : $this->get_setting('default_heading');
		$default_heading = !empty($default_heading) ? $default_heading : '';
		$html_permission = array(
			'span' => array('class'),
			'b' => array(),
			'strong' => array(),
			'i' => array(),
			'br' => array(),
		);
		$modal_heading = (!$this->is_active_global($product_id) && !empty(trim($modal_heading))) ? $modal_heading : $default_heading;
		$modal_heading = wp_kses($modal_heading, $html_permission);
		$modal_heading = str_replace('%title%', esc_html(wp_strip_all_tags(get_the_title($product_id))), $modal_heading);
		$modal_heading = preg_replace_callback('/\[([^\[\],]+),([^\[\]]+)\]/', function($matches) use ($recommended_products_id) {
			$single = trim($matches[1]);
			$plural = trim($matches[2]);
			$count = count($recommended_products_id);
			// Use ternary operator since these are user-defined patterns, not standard translatable strings
			return (1 === $count) ? $single : $plural;
		}, $modal_heading);
		$heading_type = isset($pr_data['heading_type']) ? $pr_data['heading_type'] : 'heading';
		$heading_article = isset($pr_data['heading_article']) ? $pr_data['heading_article'] : '';
		$modal_heading = ($heading_type === 'heading') || $this->is_active_global($product_id)
		? '<h2 class="modal-heading">' . $modal_heading . '</h2>' :
		'<div class="modal-heading-article">' . do_shortcode($heading_article) . '</div>';
		$template_data['modal_heading'] = $modal_heading;

		//show_close_icon
		$template_data['show_close_icon'] = $this->get_setting('show_close_icon');

		//show_continue_shopping
		$template_data['show_continue_shopping'] = $this->get_setting('show_continue_shopping');

		//show_go_check_out
		$template_data['show_go_check_out'] = $this->get_setting('show_go_check_out');

		//layout_type
		$template_data['layout_type'] = !empty($this->get_setting('layout_type')) ? $this->get_setting('layout_type') : 'grid';

		//variable_add_to_cart
		$template_data['variable_add_to_cart'] = $this->get_setting('variable_add_to_cart');

		//theme
		$theme_info = wp_get_theme();
		$theme = $theme_info->parent() ? $theme_info->parent()->get('Name') : $theme_info->get('Name');
		$template_data['theme'] = $theme;

		//feature_image
		$feature_image = get_the_post_thumbnail_url($product_id, array('100', '100'));
		$template_data['feature_image'] = $feature_image;
		
		//query
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'post__in' => $recommended_products_id,
			'orderby' => 'post__in',
		);
		$query = new \WP_Query($args);
		$template_data['query'] = $query;


		return $template_data;
	}

		/**
	 * Use the product's WooCommerce Upsells/Cross-sells for this product?
	 * Yes when the option is on, the product has nothing set in its own
	 * Recommendations tab, "Skip Manual Selection" is off, and it has linked products.
	 *
	 * @since 3.5.0
	 * @param int $product_id
	 * @return bool
	 */
	public function uses_linked_products($product_id) {
		$settings = $this->get_settings();

		// A "Skip Manual Selection" campaign uses the global rule for every product.
		if (!empty($settings['active_global_settings']) && !empty($settings['disable_global_override'])) {
			return false;
		}

		$data = $this->get_pr_data($product_id);
		if ($this->is_dynamic_selection($product_id) || $this->is_smart_selection($product_id) || !empty($data['products'])) {
			return false;
		}

		return !empty($this->get_linked_product_ids($product_id));
	}

	/**
	 * The product's Upsells and/or Cross-sells that can be shown: published,
	 * visible in the catalog and in stock. Upsells first.
	 *
	 * @since 3.5.0
	 * @param int $product_id
	 * @return int[]
	 */
	public function get_linked_product_ids($product_id) {
		$product_id = absint($product_id);
		if (isset(self::$linked_cache[$product_id])) {
			return self::$linked_cache[$product_id];
		}

		$ids     = array();
		$product = wc_get_product($product_id);

		if ($product) {
			if ($this->get_setting('global_linked_upsells')) {
				$ids = array_merge($ids, $product->get_upsell_ids());
			}
			if ($this->get_setting('global_linked_cross_sells')) {
				$ids = array_merge($ids, $product->get_cross_sell_ids());
			}
		}

		$ids = array_values(array_filter(array_unique(array_map('absint', $ids)), function ($id) use ($product_id) {
			if (!$id || $id === $product_id) {
				return false;
			}
			$linked = wc_get_product($id);
			return $linked
				&& 'publish' === $linked->get_status()
				&& 'hidden' !== $linked->get_catalog_visibility()
				&& $linked->is_in_stock();
		}));

		$limit = absint($this->get_setting('global_products_number'));
		if ($limit) {
			$ids = array_slice($ids, 0, $limit);
		}

		/**
		 * Linked products shown in the popup for a product.
		 *
		 * @since 3.5.0
		 * @param int[] $ids        Product IDs, Upsells first.
		 * @param int   $product_id Product added to the cart.
		 */
		self::$linked_cache[$product_id] = array_values(array_map('absint', (array) apply_filters('lpr_linked_product_ids', $ids, $product_id)));

		return self::$linked_cache[$product_id];
	}

/**
	 * Check manual selection or not 
	 *
	 * @return bool manually selected or not
	 * @since   1.0.0
	 */
	public function is_manually_selection($product_id) {
		$pr_data = $this->get_pr_data($product_id);
		return (!empty($pr_data['type']) && $pr_data['type'] === 'manual-selection');
	}

	/**
	 * Check dynamic selection or not
	 *
	 * @return bool dynamic selected or not
	 * @since   1.0.0
	 */
	public function is_dynamic_selection($product_id) {
		$pr_data = $this->get_pr_data($product_id);
		return (!empty($pr_data['type']) && $pr_data['type'] === 'dynamic-selection');
	}

	/**
	 * Is the smart engine available? Pro turns it on.
	 *
	 * @since 3.6.0
	 * @return bool
	 */
	public function smart_available() {
		/**
		 * Smart recommendations (learned from the store's orders) are available.
		 *
		 * @since 3.6.0
		 * @param bool $available
		 */
		return (bool) apply_filters('lpr_smart_available', false);
	}

	/**
	 * For the Smart picks teaser in Free: products the store's own customers bought
	 * together, from up to the last 300 paid orders of the past 6 months.
	 * Cached for a day, so it costs nothing on most page loads.
	 *
	 * @since 3.6.0
	 * @return array{orders:int, pairs:array<int, array{0:int, 1:int, 2:int}>}
	 */
	public function smart_teaser_data() {
		$cached = get_transient('lpr_smart_teaser');
		if (is_array($cached)) {
			return $cached;
		}

		$data = array('orders' => 0, 'pairs' => array());
		if (function_exists('wc_get_orders')) {
			$order_ids = wc_get_orders(array(
				'type'         => 'shop_order',
				'status'       => array('wc-processing', 'wc-completed'),
				'date_created' => '>' . strtotime('-6 months'),
				'orderby'      => 'date',
				'order'        => 'DESC',
				'limit'        => 300,
				'return'       => 'ids',
			));
			$order_ids = array_filter(array_map('absint', (array) $order_ids));
			$data['orders'] = count($order_ids);

			if ($order_ids) {
				global $wpdb;
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared
				$rows = $wpdb->get_results("SELECT oi.order_id, im.meta_value AS product_id FROM {$wpdb->prefix}woocommerce_order_items oi STRAIGHT_JOIN {$wpdb->prefix}woocommerce_order_itemmeta im ON im.order_item_id = oi.order_item_id AND im.meta_key = '_product_id' WHERE oi.order_item_type = 'line_item' AND oi.order_id IN (" . implode(',', $order_ids) . ')');
				$orders = array();
				foreach ((array) $rows as $row) {
					$orders[(int) $row->order_id][(int) $row->product_id] = true;
				}
				$count = array();
				foreach ($orders as $products) {
					$products = array_keys($products);
					if (count($products) < 2 || count($products) > 20) {
						continue;
					}
					sort($products);
					foreach ($products as $i => $a) {
						for ($j = $i + 1; $j < count($products); $j++) {
							$key = $a . ':' . $products[$j];
							$count[$key] = (isset($count[$key]) ? $count[$key] : 0) + 1;
						}
					}
				}
				arsort($count);
				foreach ($count as $key => $n) {
					list($a, $b) = array_map('intval', explode(':', $key));
					$pa = wc_get_product($a);
					$pb = wc_get_product($b);
					// Only products Smart picks would show: published, visible in the catalog, in stock.
					if (!$pa || !$pb || 'publish' !== $pa->get_status() || 'publish' !== $pb->get_status()
						|| 'hidden' === $pa->get_catalog_visibility() || 'hidden' === $pb->get_catalog_visibility()
						|| !$pa->is_in_stock() || !$pb->is_in_stock()) {
						continue;
					}
					$data['pairs'][] = array($a, $b, (int) $n);
					if (count($data['pairs']) >= 3) {
						break;
					}
				}
			}
		}

		set_transient('lpr_smart_teaser', $data, DAY_IN_SECONDS);
		return $data;
	}

	/**
	 * Product set to "Smart" in its Recommendations tab (only counts while Smart is available).
	 *
	 * @since 3.6.0
	 * @param int $product_id
	 * @return bool
	 */
	public function is_smart_selection($product_id) {
		$pr_data = $this->get_pr_data($product_id);
		return !empty($pr_data['type']) && 'smart-selection' === $pr_data['type'] && $this->smart_available();
	}

	/**
	 * Smart picks for a product. Without the engine, products from the same
	 * categories are used so nothing breaks.
	 *
	 * @since 3.6.0
	 * @param int $product_id
	 * @return int[]
	 */
	public function smart_product_ids($product_id) {
		$limit = absint($this->get_setting('global_products_number'));
		$limit = $limit ? $limit : 12;

		/**
		 * Smart picks for a product, best first.
		 *
		 * @since 3.6.0
		 * @param int[] $ids        Empty unless the engine fills it.
		 * @param int   $product_id Product the picks are for.
		 * @param int   $limit      Maximum number of products.
		 */
		$ids = (array) apply_filters('lpr_smart_product_ids', array(), $product_id, $limit);

		if (!$ids && !$this->smart_available()) {
			$ids = $this->dynamic_query_products($product_id, array('category_type' => 'same_categories', 'orderby' => 'popularity', 'number' => $limit));
		}

		return array_slice(array_values(array_map('absint', $ids)), 0, $limit);
	}

	/**
	 * Array of recommended product ids
	 *
	 * @param int $product_id
	 * @return array of recommended products ids
	 * @since      1.0.0
	 */
	public function get_recommended_products_id($product_id) {
		$ids  = array();
		$from = '';

		if ($this->uses_linked_products($product_id)) {
			$ids  = $this->get_linked_product_ids($product_id);
			$from = 'linked';
		} elseif ($this->is_active_global($product_id)) {
			$global = $this->get_global_pr_data();
			$type   = isset($global['type']) ? $global['type'] : 'rule';

			if ('manual' === $type) {
				// Store-wide rule with hand-picked products: the same list for every product.
				$picked = array_values(array_diff(array_map('absint', (array) $global['products']), array((int) $product_id)));
				$ids    = $picked ? array_values($this->manual_query_products(array('products' => $picked))) : array();
				$from   = 'global-manual';
			} elseif ('smart' === $type) {
				$ids  = $this->smart_product_ids($product_id);
				$from = 'smart';
			} else {
				$ids  = $this->dynamic_query_products($product_id, $global);
				$from = 'global-rule';
			}
		} else {
			$data = $this->get_pr_data($product_id);
			if ($this->is_smart_selection($product_id)) {
				$ids  = $this->smart_product_ids($product_id);
				$from = 'smart';
			} elseif ($this->is_manually_selection($product_id)) {
				$ids  = $this->manual_query_products($data);
				$from = 'manual';
			} elseif ($this->is_dynamic_selection($product_id)) {
				$ids  = $this->dynamic_query_products($product_id, $data);
				$from = 'dynamic';
			}
		}

		/**
		 * Recommended products for a product, in display order.
		 *
		 * @since 3.6.0
		 * @param int[]  $ids        Product IDs.
		 * @param int    $product_id Product the recommendations are for.
		 * @param string $from       Where they came from: linked, manual, dynamic, smart,
		 *                           global-manual, global-rule, or empty for none.
		 */
		$own = array_values(array_map('absint', (array) $ids));
		$ids = (array) apply_filters('lpr_recommended_products_id', $own, $product_id, $from);
		$ids = array_values(array_unique(array_filter(array_map('absint', $ids), function ($id) use ($product_id) {
			return $id && $id !== (int) $product_id;
		})));

		// Smart picks: the whole list for a Smart product or rule, or the products added to
		// fill a short list. Everything else is the store owner's own choice.
		$smart = $this->smart_available();
		$map   = array();
		foreach ($ids as $id) {
			$map[$id] = ($smart && ('smart' === $from || !in_array($id, $own, true))) ? 'smart' : 'own';
		}
		self::$origins[(int) $product_id] = $map;

		return $ids;
	}

	/**
	 * Where a recommended product came from: 'smart' (smart picks), 'own' (hand-picked,
	 * linked, rules) or '' (not recommended for this product right now).
	 *
	 * @since 3.6.0
	 * @param int $source_id  Product the recommendations are for.
	 * @param int $product_id Recommended product.
	 * @return string
	 */
	public function recommendation_origin($source_id, $product_id) {
		$source_id  = absint($source_id);
		$product_id = absint($product_id);
		if (!isset(self::$origins[$source_id])) {
			$this->get_recommended_products_id($source_id);
		}
		return isset(self::$origins[$source_id][$product_id]) ? self::$origins[$source_id][$product_id] : '';
	}

	/**
	 * Check global selection checked or not
	 *
	 * @since      1.0.0
	 * @return bool 
	 */
	public function is_active_global($id) {

		// The product's own WooCommerce Upsells/Cross-sells come before the global rule.
		if ($this->uses_linked_products($id)) {
			return false;
		}

		$settings = $this->get_settings();
		$has_global = !empty($settings['active_global_settings']) ? true : false;
		$disable_overwrite = !empty($settings['disable_global_override']) ? true : false;

		// active global and not overwrite by local
		if ($has_global && $disable_overwrite) {
			return true;
		}

		// active global and not available local
		$is_dynamic_selection = $this->is_dynamic_selection($id) || $this->is_smart_selection($id);
		$data = $this->get_pr_data($id);
		$recommended_products = !empty($data['products']) ? $data['products'] : array();

		if ($has_global && !$is_dynamic_selection && empty($recommended_products)) {
			return true;
		}

		return false;
	}

	/**
	 * Get manual selection data
	 *
	 * @since      1.0.0
	 * @return array of manually selection data
	 */
	public function manual_query_products($data) {
		if(empty($data['products'])) {
			return array();
		}

		$products_id = array_filter($data['products'], function($id) {

			$product = wc_get_product($id);

			// The product may have been deleted since it was picked.
			if (!$product) {
				return false;
			}

			if($product->get_catalog_visibility() === 'hidden') {
				return false;
			}

			if ( $product->get_status() !== 'publish' ) {
				return false;
			};

			if ( $product->managing_stock() && ! $product->is_in_stock() ) {
				return false;
			}
			
			return true;
		});

		return $products_id;
	}

	/**
	 * Get dynamic selection data 
	 *
	 * @since      1.0.0
	 * @return array of dynamically selection data
	 */
	public function dynamic_query_products($product_id, $data) {
		$args = array(
			'post_type' => 'product',
			'post__not_in' => array($product_id),
			'post_status' => 'publish',
		);

		if (!empty($data['number'])) {
			$args['posts_per_page'] = (int) $data['number'];
		}

		$categories = array();

		if (!empty($data['category_type']) && $data['category_type'] === 'same_categories') {
			$categories = wc_get_product_term_ids($product_id, 'product_cat');
		} else {
			$categories = !empty($data['categories']) ? $data['categories'] : $categories;
		}

		$categories = array_map(function ($category) {
			return (int) $category;
		}, $categories);

		$tags = !empty($data['tags']) ? $data['tags'] : array();
		$tags = array_map(function ($tag) {
			return (int) $tag;
		}, $tags);

		$product_visibility_terms  = wc_get_product_visibility_term_ids();
		$product_visibility_not_in[] = $product_visibility_terms['exclude-from-catalog'];
		$product_visibility_not_in[] = $product_visibility_terms['outofstock'];

		// Always exclude hidden and out-of-stock products, with or without category/tag filters.
		$tax_query = array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => $product_visibility_not_in,
				'operator' => 'NOT IN',
			),
		);

		if (!empty($categories)) {
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field' => 'term_id',
				'terms' => $categories,
			);
		}

		if (!empty($tags)) {
			$tax_query[] = array(
				'taxonomy' => 'product_tag',
				'field' => 'term_id',
				'terms' => $tags,
			);
		}

		$args['tax_query'] = $tax_query;

		$orderby = 'date';
		$meta_key = '';
		$order = 'desc';

		switch ($data['orderby']) {
		case 'newest':
			$orderby = 'date';
			$meta_key = '';
			$order = 'desc';
			break;

		case 'oldest':
			$orderby = 'date';
			$meta_key = '';
			$order = 'asc';
			break;

		case 'rand':
			$orderby = 'rand';
			$meta_key = '';
			$order = 'desc';
			break;

		case 'popularity':
			$orderby = 'meta_value_num';
			$meta_key = 'total_sales';
			$order = 'desc';
			break;

		case 'rating':
			$orderby = 'meta_value_num';
			$meta_key = '_wc_average_rating';
			$order = 'desc';
			break;

		case 'lowprice':
		case 'highprice':
			// Sorted by WooCommerce's price lookup table in price_order_clauses(), like the shop's own
			// price sorting. It covers variable products and sale prices, which _regular_price did not.
			$orderby = '';
			$meta_key = '';
			$order = '';
			$args['lpr_price_order'] = 'lowprice' === $data['orderby'] ? 'ASC' : 'DESC';
			$args['suppress_filters'] = false;
			break;

		case 'title':
			$orderby = 'title';
			$meta_key = '';
			$order = 'asc';
			break;
		}

		if (!empty($orderby)) {
			$args['orderby'] = $orderby;
		}

		// Best selling / top rated: products with the same number break the tie by newest,
		// then id, so the list is the same on every request.
		if ('meta_value_num' === $orderby) {
			$args['orderby'] = array('meta_value_num' => 'DESC', 'date' => 'DESC', 'ID' => 'DESC');
		}

		if (!empty($meta_key)) {
			$args['meta_key'] = $meta_key;
		}

		if (!empty($order)) {
			$args['order'] = $order;
		}

		if (!empty($data['sale'])) {
			$args['post__in'] = array_merge(array(0), wc_get_product_ids_on_sale());
		}

		$price_order = !empty($args['lpr_price_order']);
		if ($price_order) {
			add_filter('posts_clauses', array($this, 'price_order_clauses'), 10, 2);
		}

		$posts = get_posts($args);

		if ($price_order) {
			remove_filter('posts_clauses', array($this, 'price_order_clauses'), 10);
		}

		$recommended_products_ids = array_map(function ($post) {
			return $post->ID;
		}, $posts);

		return $recommended_products_ids;
	}

	/**
	 * Order a dynamic selection query by price using WooCommerce's product lookup table.
	 *
	 * Only touches queries that carry the lpr_price_order argument set in dynamic_query_products().
	 *
	 * @since      3.4.0
	 * @param array     $clauses SQL clauses.
	 * @param \WP_Query $query   Current query.
	 * @return array
	 */
	public function price_order_clauses($clauses, $query) {
		$direction = $query->get('lpr_price_order');
		if (!in_array($direction, array('ASC', 'DESC'), true)) {
			return $clauses;
		}

		global $wpdb;
		$lookup = $wpdb->prefix . 'wc_product_meta_lookup';

		$clauses['join'] .= " LEFT JOIN {$lookup} lpr_price_lookup ON {$wpdb->posts}.ID = lpr_price_lookup.product_id ";
		$clauses['orderby'] = 'ASC' === $direction
			? "lpr_price_lookup.min_price ASC, {$wpdb->posts}.ID ASC"
			: "lpr_price_lookup.max_price DESC, {$wpdb->posts}.ID DESC";

		return $clauses;
	}

	/**
	 * Nonce fixe for logged out user
	 */
	public function nonce_fix($uid = 0, $action = '') {
		$nonce_actions = array('lc-ajax-modal', 'lc-add-to-cart', 'lpr-places');
		if (in_array($action, $nonce_actions)) {
			return 0;
		}
		return $uid;
	}

	/**
	 * Get required min php version
	 *
	 * @since      1.0.0
	 * @return string min require php version
	 */
	public function get_min_php() {
		$file_info = get_file_data(self::$__FILE__, array(
			'min_php' => 'Requires PHP',
		));
		return $file_info['min_php'];
	}

	/**
	 * Get require WooCommerce version
	 *
	 * @since      1.0.0
	 * @return string min require WooCommerce version
	 */
	public function get_min_wc() {
		$file_info = get_file_data(self::$__FILE__, array(
			'min_wc' => 'WC requires at least',
		));
		return $file_info['min_wc'];
	}

	/**
	 * Get require WordPress version
	 *
	 * @since      1.0.0
	 * @return string min require WordPress version
	 */
	public function get_min_wp() {
		$file_info = get_file_data(self::$__FILE__, array(
			'min_wc' => 'Requires at least',
		));
		return $file_info['min_wc'];
	}

	/**
	 * Get settings id
	 *
	 * @since      1.0.0
	 * @return string Settings id
	 */
	public function get_settings_id() {
		return self::$setting_id;
	}

	/**
	 * Get settings
	 *
	 * @since      1.0.0
	 * @return  array all settings values of the plugins settings.
	 */
	public function get_settings() {
		if (is_null(self::$settings)) {
			self::$settings = get_option($this->get_settings_id());
		}
		return self::$settings;
	}

	/**
	 * Replace all settings and refresh the in-request cache.
	 *
	 * @since 3.4.0
	 * @param array|null $value New settings array.
	 * @return bool
	 */
	public function set_settings($value) {
		$updated = update_option($this->get_settings_id(), $value);
		self::$settings = get_option($this->get_settings_id());
		return $updated;
	}

	/**
	 * Get default settings
	 *
	 * @since      1.0.0
	 * @return  array of default value of all fields in setting page
	 */
	public function get_default_settings() {
		$default_settings = array();

		foreach ($this->get_field_map() as $id => $field) {
			if (isset($field['default'])) {
				$default_settings[$id] = $field['default'];
			}
		}

		return $default_settings;
	}

	/**
	 * Is pro version activated or not
	 *
	 * @since      1.0.0
	 */
	public function is_pro_activated() {
		return class_exists(Product_Recommendations_Pro::class);
	}

	/**
	 * Check pro plugin installed or not 
	 *
	 * @since      1.1.0
	 */
	public function has_pro_plugin() {
		return file_exists(self::$__FILE__PRO__);
	}

	/**
	 * Get product recommendations meta data
	 *
	 * @since      1.0.0
	 * @return object of post meta _lc_lpr_data
	 */
	public function get_pr_data($id) {
		if (!isset(self::$pr_meta[$id])) {
			self::$pr_meta[$id] = get_post_meta($id, '_lc_lpr_data', true);
		}

		return self::$pr_meta[$id];
	}

	/**
	 * Count total items in cart in Ajax way 
	 *
	 * @since      1.9.0
	 * @return object of post meta _lc_lpr_data
	 */
	public function cart_items_count($fragments) {
		ob_start();
		?>
		<span class="lpr-total-items"><?php echo esc_html(WC()->cart->get_cart_contents_count()); ?></span>
		<?php
		$fragments['a.lpr-cart-count .lpr-total-items'] = ob_get_clean();
		return $fragments;
	}

	/**
	 * Get global recommendations data from setting page
	 * 
	 * @since      1.0.0
	 */
	public function get_global_pr_data() {
		$settings = $this->get_settings();
		if (empty($settings['active_global_settings'])) {
			return array();
		}

		$category_type = !empty($settings['global_categories']) ? $settings['global_categories'] : 'same_categories';
		$categories = !empty($settings['global_custom_categories']) ? $settings['global_custom_categories'] : array();
		$tags = !empty($settings['global_tags']) ? $settings['global_tags'] : array();
		$filtering = !empty($settings['global_filtering']) ? $settings['global_filtering'] : 'rand';
		$onsale = !empty($settings['global_on_sale']) ? $settings['global_on_sale'] : false;
		$number_of_posts = !empty($settings['global_products_number']) ? $settings['global_products_number'] : 12;

		$data = array();
		$rule_type    = isset($settings['global_rule_type']) ? $settings['global_rule_type'] : 'rule';
		$data['type'] = 'manual' === $rule_type ? 'manual' : (('smart' === $rule_type && $this->smart_available()) ? 'smart' : 'rule');
		$data['products'] = !empty($settings['global_products']) ? array_map('absint', (array) $settings['global_products']) : array();
		$data['category_type'] = $category_type;
		$data['categories'] = $categories;
		$data['number'] = $number_of_posts;
		$data['tags'] = $tags;
		$data['orderby'] = $filtering;
		$data['sale'] = $onsale;

		return $data;
	}

	/**
	 * Get setting field data
	 *
	 * @since      1.0.0
	 * @param id settings field id
	 * @return  mixed value of setting field
	 */
	public function get_setting($id) {

		$settings = $this->get_settings();
		$field_type = $this->get_field_type($id);
		$value = isset($settings[$id]) ? $settings[$id] : $this->get_default_setting($id);

		if ($field_type === 'checkbox' && $settings && !isset($settings[$id])) {
			$value = null;
		}

		// Stores that saved settings before the modern popup (3.6.0) keep the classic one,
		// in the popup and in the settings form, until the owner switches.
		if ('popup_design' === $id && $settings && !isset($settings[$id])) {
			$value = 'classic';
		}

		if (!$value) {
			return $value;
		}

		// text field allowed tag
		$html_permission = array(
			'span' => array('class'),
			'b' => array(),
			'strong' => array(),
			'i' => array(),
			'br' => array(),
		);

		switch ($field_type) {
		case 'text':
			$value = wp_kses($value, $html_permission);
			break;

		case 'color_picker':
			$value = esc_attr($value);
			break;

		case 'number':
			$value = (int) $value;
			break;

		case 'checkbox':
			$value = (bool) $value;
			break;

		case 'radio':
			$value = esc_attr($value);
			break;

		case 'categories_select':
			$value = (array) $value;
			$value = array_map(function ($id) {
				return (int) $id;
			}, $value);
			break;

		case 'tags_select':
			$value = (array) $value;
			$value = array_map(function ($id) {
				return (int) $id;
			}, $value);
			break;

		case 'css':
			$value = sanitize_textarea_field($value);
			break;

		case 'editor':
			$value = wp_kses_post($value);
			break;
		}

		return $value;
	}

	/**
	 * Get default value setting input field
	 *
	 * @since      1.0.0
	 * @param id settings field id
	 * @return mixed value of default setting
	 */
	public function get_default_setting($id) {
		$map = $this->get_field_map();
		return isset($map[$id]['default']) ? $map[$id]['default'] : false;
	}

	/**
	 * Get setting field type by setting ID
	 *
	 * @since      1.0.0
	 * @param id setting field ID
	 * @return mixed value of setting field type
	 */
	public function get_field_type($id) {
		$map = $this->get_field_map();
		return isset($map[$id]['type']) ? $map[$id]['type'] : false;
	}

	/**
	 * All settings pages including sections and fields.
	 *
	 * Pro (3.2.0+) adds its pages and fields through the `lpr_settings_pages` filter.
	 * The result is normalized (sub-fields always under `children`) and cached once
	 * per request after all plugins are loaded.
	 *
	 * @since      1.0.0
	 * @return  array of settings pages data
	 */
	public function settings_pages() {
		if (!is_null(self::$settings_pages_cache)) {
			return self::$settings_pages_cache;
		}

		$pages = $this->default_settings_pages();

		if ($this->use_shared_settings()) {
			$pages = apply_filters('lpr_settings_pages', $pages, $this);
		}

		$pages = self::normalize_settings_pages($pages);

		// Do not freeze the schema before plugins (and their filters) are loaded.
		if (did_action('plugins_loaded')) {
			self::$settings_pages_cache = $pages;
		}

		return $pages;
	}

	/**
	 * Normalize a settings schema: sub-fields are read from `children`, `chields`
	 * (Free 2022-2026) or `childs` (Pro up to 3.1) and stored under `children`.
	 *
	 * @since 3.4.0
	 * @param array $pages Settings pages.
	 * @return array
	 */
	public static function normalize_settings_pages($pages) {
		if (!is_array($pages)) {
			return array();
		}

		foreach ($pages as $p => $page) {
			if (empty($page['sections']) || !is_array($page['sections'])) {
				continue;
			}

			foreach ($page['sections'] as $s => $section) {
				if (empty($section['fields']) || !is_array($section['fields'])) {
					continue;
				}

				foreach ($section['fields'] as $f => $field) {
					$pages[$p]['sections'][$s]['fields'][$f] = self::normalize_settings_field($field);
				}
			}
		}

		return $pages;
	}

	/**
	 * Normalize one field definition (see normalize_settings_pages()).
	 *
	 * @since 3.4.0
	 * @param array $field Field definition.
	 * @return array
	 */
	/**
	 * Move one child of a wrapper field out as its own top-level field (simple settings).
	 * Saved values are unchanged: the setting keeps its id.
	 *
	 * @since 3.6.0
	 * @param array  $fields   Top-level fields keyed by id (changed in place).
	 * @param string $wrapper  Wrapper field id.
	 * @param string $child_id Child field id.
	 * @param array  $as       Keys for the new top-level field.
	 * @param array  $rest     Keys for what stays in the wrapper.
	 */
	public static function split_child_field(array &$fields, $wrapper, $child_id, array $as, array $rest = array()) {
		if (!isset($fields[$wrapper])) {
			return;
		}

		$field    = self::normalize_settings_field($fields[$wrapper]);
		$children = isset($field['children']) ? $field['children'] : array();
		$found    = null;

		foreach ($children as $i => $child) {
			if (isset($child['id']) && $child['id'] === $child_id) {
				$found = $child;
				unset($children[$i]);
			}
		}

		if (!$found) {
			return;
		}

		$field['children'] = array_values($children);
		$fields[$wrapper]  = array_merge($field, $rest);

		$new = array_merge($found, array('id' => $child_id), $as);
		unset($new['_parent']);

		// Keep the order: the new field goes right before its wrapper.
		$ordered = array();
		foreach ($fields as $id => $item) {
			if ($id === $wrapper) {
				$ordered[$child_id] = $new;
			}
			$ordered[$id] = $item;
		}
		$fields = $ordered;
	}

	public static function normalize_settings_field($field) {
		$children = array();

		foreach (array('children', 'chields', 'childs') as $key) {
			if (!empty($field[$key]) && is_array($field[$key])) {
				$children = $field[$key];
				break;
			}
		}

		unset($field['chields'], $field['childs']);

		if (!empty($children)) {
			$field['children'] = $children;
		}

		return $field;
	}

	/**
	 * All fields (including sub-fields) keyed by field id, with the id of the
	 * settings page (option name) they belong to in `_page`.
	 *
	 * @since 3.4.0
	 * @return array
	 */
	public function get_field_map() {
		if (!is_null(self::$field_map_cache)) {
			return self::$field_map_cache;
		}

		$map = array();

		foreach ($this->settings_pages() as $page) {
			if (empty($page['sections'])) {
				continue;
			}

			foreach ($page['sections'] as $section) {
				if (empty($section['fields'])) {
					continue;
				}

				foreach ($section['fields'] as $field) {
					if (empty($field['id'])) {
						continue;
					}

					$field['_page'] = $page['id'];
					$map[$field['id']] = $field;

					if (!empty($field['children'])) {
						foreach ($field['children'] as $child) {
							if (!empty($child['id'])) {
								$child['_page'] = $page['id'];
								$child['_parent'] = $field['id'];
								$map[$child['id']] = $child;
							}
						}
					}
				}
			}
		}

		if (!is_null(self::$settings_pages_cache)) {
			self::$field_map_cache = $map;
		}

		return $map;
	}

	/**
	 * Should Free run the settings framework?
	 *
	 * Yes when Pro is not active, or when the active Pro uses the shared framework
	 * (it defines LPR_PRO_SHARED_SETTINGS). An older Pro brings its own copy of the
	 * framework; loading both in one request would declare the same functions twice.
	 *
	 * @since 3.4.0
	 * @return bool
	 */
	public function use_shared_settings() {
		return !$this->is_pro_activated() || defined('LPR_PRO_SHARED_SETTINGS');
	}

	/**
	 * Keep a one-time copy of the settings, taken before the shared framework
	 * (3.4.0) saves them for the first time. Not autoloaded.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public function maybe_backup_settings() {
		if (false !== get_option('lc_lpr_settings_backup')) {
			return;
		}

		$settings = get_option($this->get_settings_id());

		if (empty($settings) || !is_array($settings)) {
			return;
		}

		add_option('lc_lpr_settings_backup', array(
			'time' => time(),
			'version' => $this->plugin_version(),
			'settings' => $settings,
		), '', 'no');
	}

	/**
	 * Settings schema of the free plugin (before the `lpr_settings_pages` filter)
	 *
	 * @since      1.0.0
	 * @return  array of settings pages data
	 */
	public function default_settings_pages() {
		$general_settings_fields = array(
			array(
				'id' => 'popup_design',
				'title' => __('Popup Design', 'leo-product-recommendations'),
				'type' => 'radio',
				'options' => array(
					'modern' => __('Modern', 'leo-product-recommendations'),
					'classic' => __('Classic', 'leo-product-recommendations'),
				),
				'default' => 'modern',
				'description' => __('Modern: the added product, cart subtotal, a row of matching products, and View cart and Checkout buttons. Looks the same in every theme and slides up from the bottom on phones. Classic: the earlier design, using your theme\'s product cards.', 'leo-product-recommendations'),
			),
			array(
				'id' => 'heading_type',
				'title' => sprintf(
					/* translators: %s: Link to global settings page */
					__('Default Heading <small>Used for products that have no heading of their own, or that use the store-wide rule (<a target="_blank" href="%s">What to Recommend</a>)</small>', 'leo-product-recommendations'),
					admin_url('admin.php?page=lpr-settings&sec=lpr-global-settings')
				),
				'type' => 'heading_selection',
				'default' => 'default_heading',
				'children' => array(
					array(
						'id' => 'default_heading',
						'title' => __('Heading', 'leo-product-recommendations'),
						'type' => 'text',
						'default' => __('Goes well with %title%', 'leo-product-recommendations'),
					),
					array(
						'id' => 'default_heading_description',
						'title' => __('Heading & Description', 'leo-product-recommendations'),
						'type' => 'editor',
					),
				),
				'description' => __('<strong>%title%</strong> becomes the added product\'s name. <strong>[item, items]</strong> picks the first word for one recommended product and the second for more, for example [product, products].', 'leo-product-recommendations'),
				'doc' 		  => 'https://leocoder.com/docs/woocommerce-product-recommendations/popup-modal-settings/general-settings/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=docs&utm_content=settings-page',
			),
			array(
				'id' => 'popup_view_times',
				'title' => __('How Often To Show', 'leo-product-recommendations'),
				'type' 	=> 'select',
				'options' => array(
					'always' 		 => __('Every Time', 'leo-product-recommendations'),
					'1' 		 	 =>  __('1 Time', 'leo-product-recommendations'),
					'2' 		 	 =>  __('2 Times', 'leo-product-recommendations'),
					'3' 		 	 =>  __('3 Times', 'leo-product-recommendations'),
					'4' 		 	 =>  __('4 Times', 'leo-product-recommendations'),
					'5' 		 	 =>  __('5 Times', 'leo-product-recommendations'),
				),
				'description' => __('How many times one visitor sees the popup. The count starts again after 2 hours without a popup.', 'leo-product-recommendations')
			),
			array(
				'id' => 'variable_add_to_cart',
				'switch' => true,
				'default' => 1,
				'title' => __('Products With Options', 'leo-product-recommendations'),
				'label' => __('Let shoppers choose options in the popup', 'leo-product-recommendations'),
				'type' => 'checkbox',
				'advanced' => true,
				'description' => __('Shoppers pick the size, color or other options of a recommended product right in the popup. Off: these products show a button that opens the product page, where shoppers choose their options.', 'leo-product-recommendations'),
				'doc'         => 'https://leocoder.com/docs/woocommerce-product-recommendations/popup-modal-settings/general-settings/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=docs&utm_content=settings-page',
			),
			array(
				'id' => 'grid_options',
				'title' => __('Grid Options', 'leo-product-recommendations'),
				'type' => 'wrapper',
				'children' => array(
					array(
						'id' => 'grid_lg_items',
						'title' => __('Desktop Products Per Row', 'leo-product-recommendations'),
						'type' => 'number',
						'min' => 2,
						'max' => 5,
						'default' => 4,
					),

					array(
						'id' => 'grid_md_items',
						'title' => __('Tablet Products Per Row', 'leo-product-recommendations'),
						'type' => 'number',
						'min' => 2,
						'max' => 5,
						'default' => 3,
					),

					array(
						'id' => 'grid_sm_items',
						'title' => __('Mobile Products Per Row', 'leo-product-recommendations'),
						'type' => 'number',
						'min' => 1,
						'max' => 3,
						'default' => 2,
					),
					array(
						'id' => 'grid_column_gap',
						'title' => __('Column Gap', 'leo-product-recommendations'),
						'type' => 'number',
						'suffix' => 'px',
						'min' => 0,
						'max' => 60,
						'default' => 20,
					),
				),
				'doc' => 'https://leocoder.com/docs/woocommerce-product-recommendations/popup-modal-settings/general-settings/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=docs&utm_content=settings-page'
			),
			array(
				'id' => 'layout_image',
				'title' => __('Layout Type <span class="badge">PRO</span>', 'leo-product-recommendations'),
				'type' => 'pro_preview',
				'controls' => array(
					array('type' => 'chips', 'options' => array(__('Grid', 'leo-product-recommendations'), __('Slider', 'leo-product-recommendations')), 'active' => 1),
				),
				'description' => __('Show products in a grid or a sliding row, with autoplay, speed and loop for the slider.', 'leo-product-recommendations'),
				'link' => 'https://leocoder.com/leo-product-recommendations-for-woocommerce/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=upgrade&utm_content=pro-teaser',
			),
			array(
				'id' => 'popup_size_image',
				'title' => __('Popup Size <span class="badge">PRO</span>', 'leo-product-recommendations'),
				'type' => 'pro_preview',
				'controls' => array(
					array('type' => 'chips', 'options' => array(__('Small', 'leo-product-recommendations'), __('Medium', 'leo-product-recommendations'), __('Large', 'leo-product-recommendations'), __('Custom', 'leo-product-recommendations')), 'active' => 1),
				),
				'description' => __('Width of the popup on desktop. Custom: set the desktop, tablet and mobile widths yourself.', 'leo-product-recommendations'),
				'link' => 'https://leocoder.com/leo-product-recommendations-for-woocommerce/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=upgrade&utm_content=pro-teaser',
			),
			array(
				'id' => 'button_visibility_image',
				'title' => __('Buttons <span class="badge">PRO</span>', 'leo-product-recommendations'),
				'type' => 'pro_preview',
				'controls' => array(
					array('type' => 'switches', 'items' => array(__('Continue Shopping Button', 'leo-product-recommendations'), __('Checkout Button', 'leo-product-recommendations'), __('Close Button', 'leo-product-recommendations'))),
				),
				'description' => __('Show or hide each button in the popup.', 'leo-product-recommendations'),
				'link' => 'https://leocoder.com/leo-product-recommendations-for-woocommerce/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=upgrade&utm_content=pro-teaser',
			),
		);

		$style_settings = array(
			array(
				'id' => 'popup_color_image',
				'title' => __('Popup Colors <span class="badge">PRO</span>', 'leo-product-recommendations'),
				'type' => 'pro_preview',
				'controls' => array(
					array('type' => 'swatches', 'label' => __('Button Color', 'leo-product-recommendations'), 'colors' => array('#46C28E', '#2271b1', '#1d2327', '#d9622b', '#7a4fd6')),
					array('type' => 'swatches', 'label' => __('Heading Color', 'leo-product-recommendations'), 'colors' => array('#555555', '#1d2327', '#2271b1', '#1f7a53', '#b32d2e')),
					array('type' => 'swatches', 'label' => __('Notification Bar', 'leo-product-recommendations'), 'colors' => array('#f5f5f5', '#eaf7f1', '#eef4fb', '#fbf6ea', '#ffffff')),
				),
				'description' => __('Match the popup to your brand. Every other popup color and the product title, price and button colors can be set too.', 'leo-product-recommendations'),
				'link' => 'https://leocoder.com/leo-product-recommendations-for-woocommerce/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=upgrade&utm_content=pro-teaser',
			),
		);

		// Tabs, most useful first: What to Recommend, Popup, More Places, Get Started.
		$all_fields = array();
		foreach (array_merge($general_settings_fields, $style_settings) as $field) {
			$all_fields[$field['id']] = $field;
		}


		if (isset($all_fields['popup_view_times'])) {
			$all_fields['popup_view_times']['description'] = __('How many times one visitor sees the popup. The count starts again after 2 hours without a popup.', 'leo-product-recommendations');
		}

		// Simple layout: products per row up front, the rest of the grid options under "More options".
		self::split_child_field($all_fields, 'grid_options', 'grid_lg_items', array(
			'title' => __('Products Per Row', 'leo-product-recommendations'),
			'type' => 'stepper',
			'min' => 2,
			'max' => 5,
			'description' => __('On desktop. Tablet and mobile sizes are under More options.', 'leo-product-recommendations'),
		), array(
			'title' => __('Tablet, Mobile And Spacing', 'leo-product-recommendations'),
			'advanced' => true,
		));

		$pick = function ($ids) use (&$all_fields) {
			$fields = array();
			foreach ($ids as $id) {
				if (isset($all_fields[$id])) {
					$fields[] = $all_fields[$id];
				}
			}
			return $fields;
		};

		// No General tab in Free: everything it had is about the popup, so it lives in the Popup tab.
		$popup_settings_fields    = $pick(array('popup_design', 'popup_view_times', 'heading_type', 'grid_lg_items', 'layout_image', 'popup_size_image', 'button_visibility_image', 'popup_color_image', 'grid_options', 'variable_add_to_cart'));

		// More Places is a Pro feature: Free shows what it does, with a live example.
		if (!class_exists(Places::class)) {
			require_once $this->get_path('includes/class-places.php');
		}
		$places_settings_fields = array(
			array(
				'id' => 'places_pro',
				'title' => __('More Places <span class="badge">PRO</span>', 'leo-product-recommendations'),
				'type' => 'pro_note',
				'description' => __('Recommendations beyond the popup, at every step from the product page to the thank-you page. The previews below use your own products.', 'leo-product-recommendations'),
				'link' => 'https://leocoder.com/leo-product-recommendations-for-woocommerce/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=upgrade&utm_content=places',
			),
		);

		// Discounts are a Pro feature: Free shows how shoppers see them, with the store's products.
		$pro_link = 'https://leocoder.com/leo-product-recommendations-for-woocommerce/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=upgrade&utm_content=discounts';
		$discount_settings_fields = array(
			array(
				'id' => 'discounts_pro',
				'title' => __('Discounts <span class="badge">PRO</span>', 'leo-product-recommendations'),
				'type' => 'discount_showcase',
				'description' => __('Give a small discount when shoppers add recommended products.', 'leo-product-recommendations'),
				'link' => $pro_link,
			),
			array(
				'id' => 'discount_bundle_image',
				'title' => __('Bundle Saving <span class="badge">PRO</span>', 'leo-product-recommendations'),
				'type' => 'pro_preview',
				'controls' => array(
					array('type' => 'switches', 'items' => array(__('Give a discount on products bought together', 'leo-product-recommendations'))),
					array('type' => 'chips', 'options' => array(__('Percentage', 'leo-product-recommendations'), __('Fixed amount', 'leo-product-recommendations')), 'active' => 0),
					array('type' => 'stepper', 'label' => __('Discount', 'leo-product-recommendations'), 'value' => 10, 'suffix' => '%'),
					array('type' => 'switches', 'items' => array(__('Give a bigger saving when shoppers add 2 or more products', 'leo-product-recommendations'))),
				),
				'description' => __('Products added from "Frequently bought together" cost less while the main product is in the cart.', 'leo-product-recommendations'),
				'link' => $pro_link,
			),
			array(
				'id' => 'discount_addon_image',
				'title' => __('Add-on Saving <span class="badge">PRO</span>', 'leo-product-recommendations'),
				'type' => 'pro_preview',
				'controls' => array(
					array('type' => 'switches', 'items' => array(__('Give a discount on products added from the popup or cart drawer', 'leo-product-recommendations'))),
					array('type' => 'chips', 'options' => array(__('Percentage', 'leo-product-recommendations'), __('Fixed amount', 'leo-product-recommendations')), 'active' => 0),
					array('type' => 'stepper', 'label' => __('Discount', 'leo-product-recommendations'), 'value' => 10, 'suffix' => '%'),
					array('type' => 'switches', 'items' => array(__('Give a bigger saving when shoppers add 2 or more products', 'leo-product-recommendations'))),
				),
				'description' => __('After a shopper adds a product, the products recommended with it show a lower price.', 'leo-product-recommendations'),
				'link' => $pro_link,
			),
			array(
				'id' => 'discount_offer_image',
				'title' => __('Checkout Offer Saving <span class="badge">PRO</span>', 'leo-product-recommendations'),
				'type' => 'pro_preview',
				'controls' => array(
					array('type' => 'switches', 'items' => array(__('Give a discount on the checkout offer', 'leo-product-recommendations'))),
					array('type' => 'chips', 'options' => array(__('Percentage', 'leo-product-recommendations'), __('Fixed amount', 'leo-product-recommendations')), 'active' => 0),
					array('type' => 'stepper', 'label' => __('Discount', 'leo-product-recommendations'), 'value' => 15, 'suffix' => '%'),
				),
				'description' => __('The products offered at checkout show a lower price.', 'leo-product-recommendations'),
				'link' => $pro_link,
			),
		);

		$global_settings_fields = array(
			array(
				'id' => 'recommendation_flow',
				'title' => '',
				'type' => 'recommendation_flow',
			),
			array(
				'id' => 'active_global_settings',
				'switch' => true,
				'group' => 'rule',
				'title' => __('Store-wide Rule', 'leo-product-recommendations'),
				'label' => __('Use a store-wide rule for products with nothing set', 'leo-product-recommendations'),
				'type' => 'checkbox',
				'default' => 1,
			),
			array(
				'id' => 'global_rule_type',
				'group' => 'rule',
				'title' => __('Choose Products By', 'leo-product-recommendations'),
				'type' => 'radio',
				'options' => array(
					'rule' => __('Categories and tags', 'leo-product-recommendations'),
					'manual' => __('Products I pick', 'leo-product-recommendations'),
					'smart' => __('Smart picks, learned from your orders', 'leo-product-recommendations'),
				),
				'locked' => array('smart'),
				'default' => 'rule',
				'description' => __('Categories and tags: products are found for you. Products I pick: the same hand-picked list for every product, handy for best sellers or add-ons that go with anything. Smart picks: click it to see what it finds in your orders.', 'leo-product-recommendations'),
			),
			array(
				'id' => 'smart_teaser',
				'group' => 'rule',
				'title' => '',
				'type' => 'smart_teaser',
				'link' => 'https://leocoder.com/leo-product-recommendations-for-woocommerce/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=upgrade&utm_content=smart-picks',
			),
			array(
				'id' => 'global_products',
				'group' => 'rule',
				'title' => __('Products', 'leo-product-recommendations'),
				'type' => 'products_select',
				'description' => __('Shown in this order. The product itself and products already in the cart are left out.', 'leo-product-recommendations'),
			),
			array(
				'id' => 'selection_options',
				'group' => 'rule',
				'title' => __('Rule Options', 'leo-product-recommendations'),
				'type' => 'wrapper_extend',
				'children' => array(
					array(
						'id' => 'global_categories',
						'title' => __('Categories', 'leo-product-recommendations'),
						'type' => 'radio',
						'options' => array(
							'same_categories' => __('Same categories as the product', 'leo-product-recommendations'),
							'manual_categories' => __('Categories I choose', 'leo-product-recommendations'),
						),
						'default' => 'same_categories',
					),
					array(
						'id' => 'global_custom_categories',
						'title' => __('Choose Categories', 'leo-product-recommendations'),
						'type' => 'categories_select',
					),

					array(
						'id' => 'global_tags',
						'title' => __('Choose Tags', 'leo-product-recommendations'),
						'type' => 'tags_select',
					),

					array(
						'id' => 'global_filtering',
						'title' => __('Sort By', 'leo-product-recommendations'),
						'type' 	=> 'select',
						'options' => array(
							'rand' 		 => __('Random', 'leo-product-recommendations'),
							'newest' 	 => __('Newest', 'leo-product-recommendations'),
							'oldest' 	 => __('Oldest', 'leo-product-recommendations'),
							'lowprice' 	 => __('Price: Low to High', 'leo-product-recommendations'),
							'highprice'  => __('Price: High to Low', 'leo-product-recommendations'),
							'popularity' => __('Best Selling', 'leo-product-recommendations'),
							'rating' 	 => __('Top Rated', 'leo-product-recommendations'),
						),
					),
					array(
						'id' 	=> 'global_on_sale',
						'title' => __('On-Sale Only', 'leo-product-recommendations'),
						'type' 	=> 'checkbox',
					),
					array(
						'id' 	  => 'global_products_number',
						'min' => 1,
						'max' => 100,
						'title'   => __('Number of Products', 'leo-product-recommendations'),
						'type' 	  => 'number',
						'default' => 12,
					),
				),
				'doc' => 'https://leocoder.com/docs/woocommerce-product-recommendations/popup-modal-settings/global-settings/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=docs&utm_content=settings-page',
			),
			array(
				'id' => 'disable_global_override',
				'switch' => true,
				'group' => 'rule',
				'label' => __('Use the store-wide rule for every product', 'leo-product-recommendations'),
				'title' => __('Campaign Mode', 'leo-product-recommendations'),
				'type' => 'checkbox',
				'description' => __('Ignores hand-picked and linked recommendations while it is on. Handy for a Black Friday campaign: set the rule above to the campaign products, then turn this on.', 'leo-product-recommendations'),
				'doc' => 'https://leocoder.com/docs/woocommerce-product-recommendations/popup-modal-settings/global-settings/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=docs&utm_content=settings-page',
			),
			array(
				'id' => 'linked_products',
				'title' => __('WooCommerce Linked Products', 'leo-product-recommendations'),
				'type' => 'wrapper_extend',
				'children' => array(
					array(
						'id' => 'global_linked_upsells',
						'title' => __('Show the product\'s Upsells', 'leo-product-recommendations'),
						'type' => 'checkbox',
						'default' => 1,
					),
					array(
						'id' => 'global_linked_cross_sells',
						'title' => __('Show the product\'s Cross-sells', 'leo-product-recommendations'),
						'type' => 'checkbox',
						'default' => 1,
					),
				),
				'description' => __('Set them under Product data > Linked Products. When both are on, Upsells come first.', 'leo-product-recommendations'),
			),
		);

		$setting_pages = array(
			array(
				'id' => $this->get_settings_id(),
				'page_title' => __('Product Recommendations Settings', 'leo-product-recommendations'),
				'menu_title' => __('LPR Settings', 'leo-product-recommendations'),
				'submenu_title' => __('Settings', 'leo-product-recommendations'),
				'slug' => 'lpr-settings',
				'icon' => 'dashicons-cart',
				'position' => 60,
				
				'sections' => array(
					array(
						'id' => 'lpr-global-settings',
						'tab_title' => __('What to Recommend', 'leo-product-recommendations'),
						'title' => __('What to Recommend', 'leo-product-recommendations'),
						'fields' => $global_settings_fields,
					),
					array(
						'id' => 'lpr-style-settings',
						'tab_title' => __('Popup', 'leo-product-recommendations'),
						'title' => __('Popup', 'leo-product-recommendations'),
						'description' => __('The popup that shows after a product is added to the cart: how often, its heading, layout and look.', 'leo-product-recommendations'),
						'fields' => $popup_settings_fields,
					),

					array(
						'id' => 'lpr-places-settings',
						'tab_title' => __('More Places', 'leo-product-recommendations'),
						'title' => __('More Places', 'leo-product-recommendations'),
						'description' => __('Show recommendations in more places than the popup. Available in Pro.', 'leo-product-recommendations'),
						'fields' => $places_settings_fields,
					),

					array(
						'id' => 'lpr-discounts-settings',
						'tab_title' => __('Discounts', 'leo-product-recommendations'),
						'title' => __('Discounts', 'leo-product-recommendations'),
						'description' => __('Small discounts on recommended products, taken off in the cart automatically. Available in Pro.', 'leo-product-recommendations'),
						'fields' => $discount_settings_fields,
					),


					array(
						'id' => 'lpr-documentation',
						'tab_title' => __('Get Started', 'leo-product-recommendations'),
						'title' => __('Get Started', 'leo-product-recommendations'),
						'type' => 'article',
						'template' => $this->get_path('includes/tutorials.php'),
					),
				),
			),
		);

		return $setting_pages;
	}

	/**
	 * Initialize plugin deactivation feedback
	 *
	 * @since 1.3.0
	 */
	public function deactivation_feedback() {
		if (is_admin()) {
			if (!class_exists(Deactivation_Feedback::class)) {
				include_once $this->get_path('feedbacks/deactivation-feedback/class-deactivation-feedback.php');
			}
			new Deactivation_Feedback($this->feedback_fields());
		}
	}

	/**
	 * Plugin deactivation feedback fields
	 *
	 * @return array of deactivation feedback form fields
	 */
	public function feedback_fields() {

		$form_data = array(
			// Fixed name so feedback stays grouped under one plugin on leocoder.com after the 3.4.0 rename.
			'plugin_name' => 'Leo Product Recommendations for WooCommerce',
			'plugin_version' => $this->get_plugin_version(),
			'plugin_slug' => $this->get_slug(),
			'support' => array(
				'title' => __('Need Help?', 'leo-product-recommendations'),
				'support_url' => 'https://leocoder.com/contact/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=support&utm_content=deactivation-form'
			),
			'feedback_heading' => __('Quick Feedback', 'leo-product-recommendations'),
			'form_heading' => __('May we have a little info about why you are deactivating?', 'leo-product-recommendations'),
			'api_url' => 'https://leocoder.com/wp-json/leocoder/v1/deactivation-feedback',

			'fields' => array(
				array(
					'category' => 'temporary_deactivation',
					'reason' => __('It\'s a temporary deactivation.', 'leo-product-recommendations'),
					'instruction' => '',
					'placeholder' => '',
					'input_default' => '',
				),

				array(
					'category' => 'not_show_image_button',
					'reason' => __('Does not show product image or add to cart button.', 'leo-product-recommendations'),
					'instruction' => '<a href="https://leocoder.com/contact/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=support&utm_content=deactivation-form" target="_blank">' . __('Contact our support and we\'ll help you fix it »', 'leo-product-recommendations') . '</a>',
					'input_field' => '',
					'placeholder' => '',
					'input_default' => '',
				),

				array(
					'category' => 'dont_understand',
					'reason' => __('I couldn\'t understand how to use it.', 'leo-product-recommendations'),
					'instruction' => '<a href="https://leocoder.com/docs/woocommerce-product-recommendations/add-recommended-products/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=docs&utm_content=deactivation-form" target="_blank">' . __('Read the documentation »', 'leo-product-recommendations') . '</a>',
					'input_field' => '',
					'placeholder' => '',
					'input_default' => '',
				),

				array(
					'category' => 'not_works',
					'reason' => __('It doesn\'t work with my website.', 'leo-product-recommendations'),
					'instruction' => '<a href="https://leocoder.com/contact/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=support&utm_content=deactivation-form" target="_blank">' . __('Contact our support and we\'ll help you fix it »', 'leo-product-recommendations') . '</a>',
					'input_field' => '',
					'placeholder' => '',
					'input_default' => '',
				),

				array(
					'category' => 'look_bad',
					'reason' => __('It looks bad on my website.', 'leo-product-recommendations'),
					'instruction' => '<a href="https://leocoder.com/contact/?utm_source=lpr-free&utm_medium=plugin-admin&utm_campaign=support&utm_content=deactivation-form" target="_blank">' . __('Contact our support and we\'ll help you fix it »', 'leo-product-recommendations') . '</a>',
					'input_field' => '',
					'placeholder' => '',
					'input_default' => '',
				),

				array(
					'category' => 'dont_need',
					'reason' => __('It works nicely but I don’t need it now.', 'leo-product-recommendations'),
					'instruction' => '<a href="https://wordpress.org/support/plugin/leo-product-recommendations/reviews/" target="_blank">' . __('Leave us a review »', 'leo-product-recommendations') . '</a>',
					'input_field' => '',
					'placeholder' => '',
					'input_default' => '',
				),

				array(
					'category' => 'need_help',
					'reason' => __('I need some help to set up the plugin.', 'leo-product-recommendations'),
					'instruction' => __('Leave your email address and we\'ll contact you soon.', 'leo-product-recommendations'),
					'input_field' => 'email',
					'placeholder' => '',
					'input_default' => sanitize_email($this->admin_email()),
				),

				array(
					'category' => 'another_plugin',
					'reason' => __('I found a better plugin.', 'leo-product-recommendations'),
					'instruction' => '',
					'input_field' => 'text',
					'placeholder' => __('Please share which plugin', 'leo-product-recommendations'),
					'input_default' => '',
				),

				array(
					'category' => 'feature_request',
					'reason' => __('I need a specific feature that it doesn\'t support.', 'leo-product-recommendations'),
					'instruction' => __('Tell us which feature you need.', 'leo-product-recommendations'),
					'input_field' => 'textarea',
					'placeholder' => __('Feature details', 'leo-product-recommendations'),
					'input_default' => '',
				),

				array(
					'category' => 'other',
					'reason' => __('Other.', 'leo-product-recommendations'),
					'instruction' => '',
					'input_field' => 'textarea',
					'placeholder' => __('Please share the reason. We\'ll try to help.', 'leo-product-recommendations'),
					'input_default' => '',
				),
			),
		);

		return $form_data;
	}

	/**
	 * Get plugin name
	 *
	 * @since      1.6.0
	 * @return string name of the plugin
	 */

	public function get_plugin_name() {
		$file_info = get_file_data(self::$__FILE__, array(
			'plugin_name' => 'Plugin Name',
		));
		return $file_info['plugin_name'];
	}

	/**
	 * Get plugin version
	 *
	 * @since      1.6.0
	 * @return string version number of plugin
	 */
	public function get_plugin_version() {
		$file_info = get_file_data(self::$__FILE__, array(
			'version' => 'Version',
		));
		return $file_info['version'];
	}

	/**
	 * Get admin email address
	 *
	 * @return string current user email address
	 */
	public function admin_email() {
		$admin_email = wp_get_current_user();
		return (0 !== $admin_email->ID) ? $admin_email->data->user_email : '';
	}

	/**
	 * Dependencies and version of a built script, read from the `*.asset.php` file
	 * that @wordpress/scripts writes next to it. The version is a content hash, so
	 * browsers pick up every new build.
	 *
	 * @since 3.4.0
	 * @param string $name       Script name, e.g. 'panel' for assets/js/panel.min.js.
	 * @param array  $extra_deps Globals the script uses without importing them (jQuery, wp.i18n, ...).
	 * @return array { dependencies: string[], version: string }
	 */
	public function script_asset($name, $extra_deps = array()) {
		$file  = $this->get_path('assets/js/' . $name . '.min.asset.php');
		$asset = file_exists($file) ? include $file : array();

		$dependencies = isset($asset['dependencies']) ? $asset['dependencies'] : array();
		$version      = isset($asset['version']) ? $asset['version'] : $this->script_version();

		return array(
			'dependencies' => array_values(array_unique(array_merge($dependencies, $extra_deps))),
			'version'      => $version,
		);
	}

	/**
	 * Get enqueue script version
	 *
	 * @since      1.0.0
	 * @return string version of script base on development or production server
	 */
	public function script_version() {
		if (WP_DEBUG) {
			return time();
		}
		return $this->plugin_version();
	}

	/**
	 * Get plugin version
	 *
	 * @since      1.0.0
	 * @return string current version of plugin
	 */
	public function plugin_version() {
		$plugin_data = get_file_data(self::$__FILE__, array(
			'Version' => 'Version',
		));

		return $plugin_data['Version'];
	}

	/**
	 * Get class instance
	 * @since      1.0.0
	 * @return instance of plugin base class
	 */
	public static function init($__FILE__) {
		if (is_null(self::$instance)) {
			self::$instance = new self($__FILE__);
		}
		return self::$instance;
	}
}