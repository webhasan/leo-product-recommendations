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
	 * Array of all products recommendations meta data
	 *
	 * @var array
	 */
	static protected $pr_meta = array();

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
	}

	/**
	 * Action after plugin deactivate
	 *
	 * @since      1.0.0
	 * @return void
	 */
	public function on_deactivation() {
		// do nothing
	}

	/**
	 * Setup plugin once all other plugins are loaded
	 *
	 * @since      1.0.0
	 * @return void
	 */
	public function on_plugins_loaded() {
		$this->load_textdomain();

		if (!$this->has_satisfied_dependencies()) {
			add_action('admin_notices', array($this, 'render_dependencies_notice'));
			return;
		}

		// used this hook in pro plugin
		do_action('lpr_before_action');

		$this->includes();
		$this->hooks();

		//deactivation feedback
		$this->deactivation_feedback();
	}

	/**
	 * Load Localization files
	 *
	 * @since      1.0.0
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain('leo-product-recommendations', false, dirname(plugin_basename(self::$__FILE__)) . '/languages');
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
				__('The Leo Product Recommendations for WooCommerce plugin requires <a href="%1$s">WooCommerce</a> %2$s or greater to be installed and active.', 'leo-product-recommendations'),
				'https://wordpress.org/plugins/woocommerce/',
				$minimum_woocommerce_version
			);
		}

		if (!$wordpress_minimum_met) {
			$errors[] = sprintf(
				/* translators: 1. link of wordpress 2. version of WordPress. */
				__('The Leo Product Recommendations for WooCommerce plugin requires <a href="%1$s">WordPress</a> %2$s or greater to be installed and active.', 'leo-product-recommendations'),
				'https://wordpress.org/',
				$minimum_wordpress_version
			);
		}

		if (!$php_minimum_met) {
			$errors[] = sprintf(
				/* translators: 1. version of php */
				__('The Leo Product Recommendations for WooCommerce plugin requires <strong>php version %s</strong> or greater. Please update your server php version.', 'leo-product-recommendations'),
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
		printf('<div class="error"><p>%s</p></div>', implode(' ', $message));
	}

	/**
	 * Include add-on features with this plugin
	 *
	 * @since      1.0.0
	 * @return void
	 */
	public function includes() {
		$this->admin_ajax(); // handle all admin ajax request
		$this->plugin_action_links(); // add action link, example: Go Pro->, Settings->
		$this->plugin_settings();
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
				'settings' => '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=getwooplugins-settings&tab=lc_lpr_settings')) . '">' . __('Settings', 'leo-product-recommendations') . '</a>',
				'documentation' => '<a href="' . esc_url('https://cutt.ly/KjE8lEI') . '" target="_blank" rel="noopener noreferrer nofollow">' . __('Docs', 'leo-product-recommendations') . '</a>',
			);

			if (!$this->has_pro_plugin()) {
				$link_after = array(
					'go-pro' => '<a href="' . esc_url('https://cutt.ly/4jE8fxM') . '" target="_blank" rel="noopener noreferrer nofollow" style="color: red; font-weight: bold;">' . __('Go Pro', 'leo-product-recommendations') . '</a>',
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
	public function plugin_settings() {

		//include class for register Menu Page, Sub-Menu Page and Fields
		if( !class_exists( GetWooPlugins_Admin_Menus::class ) ) {
			require_once $this->get_path('includes/getwooplugins/class-getwooplugins-admin-menus.php');
		}
		\GetWooPlugins_Admin_Menus::instance();

		//Register settings Tags, Sections, and Fields
		add_filter( 'getwooplugins_get_settings_pages', array( $this, 'init_settings' ) );
	}

	/**
	 * Class of settings fields
	 *
	 * @since 2.5.0
	 * @param arr $settings array for previous settings
	 * @return arr of all settings fields including new one
	 */
	public function init_settings( $settings ) {
		//setting class including settings fields 
		require_once $this->get_path('includes/class-leo-product-recommendations-settings.php');
		$settings[] = new \Leo_Product_Recommendations_Settings(); 
		return $settings;
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

		// Include required front-end scripts
		add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts')); 

		// Add recommendations tabs in product data table
		add_action('woocommerce_product_data_tabs', array($this, 'product_data_tabs'));
		add_action('woocommerce_product_data_panels', array($this, 'product_data_panels'));
		add_action('save_post', array($this, 'on_save_post'));

		// Fetch popup modal data
		add_action('wp_ajax_get_popup_data', array($this, 'get_popup_data'));
		add_action('wp_ajax_nopriv_get_popup_data', array($this, 'get_popup_data'));

		// Ajax add to cart
		add_action('wp_ajax_lc_ajax_add_to_cart', array($this, 'ajax_add_to_cart'));
		add_action('wp_ajax_nopriv_lc_ajax_add_to_cart', array($this, 'ajax_add_to_cart'));

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
		wp_enqueue_editor();

		if (!$this->is_pro_activated()) {
			wp_enqueue_script('selection-panel-script', $this->get_url('assets/js/panel.min.js'), array('lodash', 'wp-element', 'wp-components', 'wp-polyfill', 'wp-i18n', 'jquery'), $version, true);
			wp_set_script_translations( 'selection-panel-script', 'leo-product-recommendations' );
			wp_localize_script('selection-panel-script', 'lc_pr_panel_data', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('lc-panel-security'),
				'pro_image' => $this->get_url('assets/images/pro-feature.jpg'),
				'pro_link' => esc_url('https://cutt.ly/4jE8fxM'),
			));
			wp_enqueue_style('selection-panel-style', $this->get_url('assets/css/panel.css'), '', $version);
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

		wp_enqueue_script('lpr-modal', $this->get_url('assets/js/modal.min.js'), array('jquery', 'wp-i18n'), $version, true);
		wp_set_script_translations( 'lpr-modal', 'leo-product-recommendations' );

		wp_localize_script('lpr-modal', 'lc_ajax_modal', array(
			'url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('lc-ajax-modal'),
			'layout_type' => $layout_type
		));

		// if (is_product()) { // disabled condition so that it work for quick view
		wp_enqueue_script('lpr-ajax-add-to-cart', $this->get_url('assets/js/ajax-add-to-cart.min.js'), array('jquery', 'wp-i18n'), $version, true);
		wp_set_script_translations( 'lpr-ajax-add-to-cart', 'leo-product-recommendations' );
		wp_localize_script('lpr-ajax-add-to-cart', 'lc_ajax', array(
			'url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('lc-add-to-cart'),
		));
		// }

		wp_enqueue_style('lpr-modal', $this->get_url('assets/css/modal.css'), array(), $version);
		//recommend variable and group product add to cart
		if ($this->get_setting('variable_add_to_cart', 'yes')) {
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
		$items_desktop = $this->get_setting('grid_lg_items', 4);

		$items_tablet = $this->get_setting('grid_md_items', 3);
		$items_mobile = $this->get_setting('grid_sm_items', 2);
		$grid_column_gap = (int) $this->get_setting('grid_column_gap', 20) + 1;

		$desktop_item_width = 100 / $items_desktop;
		$tablet_item_width = 100 / $items_tablet;
		$mobile_item_width = 100 / $items_mobile;

		// custom css
		$custom_css = $this->get_setting('custom_style', '');
		?>
		<style id="lpr-settings-css-front-end">
			.lpr-modal .lpr-modal-content ul.recommended-products-list {
				margin: 0 <?php echo -$grid_column_gap / 2; ?>px !important;
			}
			.lpr-modal .lpr-modal-content ul.recommended-products-list li.single-lpr {
				flex: 0 0 calc(<?php echo $desktop_item_width . '% - ' . $grid_column_gap . 'px'; ?>);
				width: calc(<?php echo $desktop_item_width . '% - ' . $grid_column_gap . 'px'; ?>);
				margin-left: <?php echo $grid_column_gap / 2; ?>px !important;
				margin-right: <?php echo $grid_column_gap / 2; ?>px !important;
			}
			@media screen and (max-width: 991px) {
				.lpr-modal .lpr-modal-content ul.recommended-products-list li.single-lpr {
					flex: 0 0 calc(<?php echo $tablet_item_width . '% - ' . $grid_column_gap . 'px'; ?>);
					width: calc(<?php echo $tablet_item_width . '% - ' . $grid_column_gap . 'px'; ?>);
					margin-left: <?php echo $grid_column_gap / 2; ?>px !important;
					margin-right: <?php echo $grid_column_gap / 2; ?>px !important;
				}
			}
			@media screen and (max-width: 767px) {
				.lpr-modal .lpr-modal-content ul.recommended-products-list li.single-lpr {
					flex: 0 0 calc(<?php echo $mobile_item_width . '% - ' . $grid_column_gap . 'px'; ?>);
					width: calc(<?php echo $mobile_item_width . '% - ' . $grid_column_gap . 'px'; ?>);
					margin-left: <?php echo $grid_column_gap / 2; ?>px !important;
					margin-right: <?php echo $grid_column_gap / 2; ?>px !important;
				}
			}
			<?php echo $custom_css; ?>
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
		$is_secure = !empty($_POST['_lc_lpr_data']) && !empty($_POST['lc_pr_panel_nonce']) && wp_verify_nonce($_POST['lc_pr_panel_nonce'], 'lc-panel-security');

		if ($is_secure) {
			$panel_data = (array) $_POST['_lc_lpr_data'];
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

	public function get_popup_data() {
		$nonce = $_GET['nonce'];
		$product_id  = $_GET['product_id'];

		if (!isset($nonce) || !wp_verify_nonce($nonce, 'lc-ajax-modal') || !isset($product_id)) {
			wp_send_json_error(array('message' => 'Bad request'), 400);
		}

		$recommended_products_id = $this->get_recommended_products_id($product_id);
		$cart_items = $this->get_cart_items();

		$recommended_products_id  = array_filter($recommended_products_id, function($id) use($cart_items) {
			return !in_array($id, $cart_items);
		});

		if(!count($recommended_products_id)) wp_die();

		$template_data = $this->get_template_data($product_id, $recommended_products_id);
		include $this->get_templates_path('templates/template-modal-content.php');
		wp_die();
	}

	/**
	 * Ajax add to cart 
	 *
	 * @since      1.0.0
	 * @return  json
	 */
	public function ajax_add_to_cart() {

		if (!empty($_POST) && $_POST['nonce'] && wp_verify_nonce($_POST['nonce'], 'lc-add-to-cart')) {
			if (!class_exists(Ajax_Add_To_Cart::class)) {
				include $this->get_path('includes/class-ajax-add-to-cart.php');
			}
			new Ajax_Add_To_Cart($_POST);
		} else {
			wp_send_json_error(array('message' => 'Bad request'), 400);
		}
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
		$is_article_heading = ($this->get_setting('heading_type', 'default_heading') === 'default_heading_description');
		$default_heading = $is_article_heading ? $this->get_setting('default_heading_description','') : $this->get_setting('default_heading', 'You may purchase following [product, products] with the %title%');
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
		$modal_heading = str_replace('%title%', get_the_title($product_id), $modal_heading);
		$modal_heading = preg_replace('/\[(.+),(.+)\]/', _n('${1}', '${2}', count($recommended_products_id)), $modal_heading);
		$heading_type = isset($pr_data['heading_type']) ? $pr_data['heading_type'] : 'heading';
		$heading_article = isset($pr_data['heading_article']) ? $pr_data['heading_article'] : '';
		$modal_heading = ($heading_type === 'heading') || $this->is_active_global($product_id)
		? '<h2 class="modal-heading">' . $modal_heading . '</h2>' :
		'<div class="modal-heading-article">' . do_shortcode($heading_article) . '</div>';
		$template_data['modal_heading'] = $modal_heading;

		//show_close_icon
		$template_data['show_close_icon'] = $this->get_setting('show_close_icon','no') === 'yes' ? true : false;

		//show_continue_shopping
		$template_data['show_continue_shopping'] = $this->get_setting('show_continue_shopping','yes') === 'yes' ? true : false;

		//show_go_check_out
		$template_data['show_go_check_out'] = $this->get_setting('show_go_check_out','yes') === 'yes' ? true : false;

		//layout_type
		$template_data['layout_type'] = $this->get_setting('layout_type', 'grid');

		//variable_add_to_cart
		$template_data['variable_add_to_cart'] = $this->get_setting('variable_add_to_cart','yes')  === 'yes' ? true : false;

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
	 * Array of recommended product ids
	 *
	 * @param int $product_id
	 * @return array of recommended products ids
	 * @since      1.0.0
	 */
	public function get_recommended_products_id($product_id) {
		if ($this->is_active_global($product_id)) {
			return $this->dynamic_query_products($product_id, $this->get_global_pr_data());
		}

		$data = $this->get_pr_data($product_id);
		if ($this->is_manually_selection($product_id)) {
			return $this->manual_query_products($data);
		} elseif ($this->is_dynamic_selection($product_id)) {
			return $this->dynamic_query_products($product_id, $data);
		}
		return array();
	}

	/**
	 * Check global selection checked or not
	 *
	 * @since      1.0.0
	 * @return bool 
	 */
	public function is_active_global($id) {

		$has_global = $this->get_setting('active_global_settings','yes') === 'yes' ? true : false;
		$disable_overwrite = $this->get_setting('disable_global_override','no') === 'yes' ? true : false;
		
		// active global and not overwrite by local
		if ($has_global && $disable_overwrite) {
			return true;
		}

		// active global and not available local
		$is_dynamic_selection = $this->is_dynamic_selection($id);
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

			if($product->get_catalog_visibility() === 'hidden') {
				return false;
			}

			if ( $product->get_status() !== 'publish' ) {
				return false;
			};

			$product = wc_get_product($id);
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
	 * @return array of dynamic selection product data
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

		if (!empty($categories) && !empty($tags)) {
			$args['tax_query'] = array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'product_cat',
					'field' => 'term_id',
					'terms' => $categories,
				),
				array(
					'taxonomy' => 'product_tag',
					'field' => 'term_id',
					'terms' => $tags,
				),
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => $product_visibility_not_in,
					'operator' => 'NOT IN',
				)
			);
		} else if (!empty($categories)) {
			$args['tax_query'] = array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'product_cat',
					'field' => 'term_id',
					'terms' => $categories,
				),
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => $product_visibility_not_in,
					'operator' => 'NOT IN',
				)
			);
		} else if (!empty($tags)) {
			$args['tax_query'] = array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'product_tag',
					'field' => 'term_id',
					'terms' => $tags,
				),
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'term_taxonomy_id',
					'terms'    => $product_visibility_not_in,
					'operator' => 'NOT IN',
				)
			);
		} 

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
			$orderby = 'meta_value_num';
			$meta_key = '_regular_price';
			$order = 'asc';
			break;

		case 'highprice':
			$orderby = 'meta_value_num';
			$meta_key = '_regular_price';
			$order = 'desc';
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

		if (!empty($meta_key)) {
			$args['meta_key'] = $meta_key;
		}

		if (!empty($order)) {
			$args['order'] = $order;
		}

		if (!empty($data['sale'])) {
			$args['post__in'] = array_merge(array(0), wc_get_product_ids_on_sale());
		}

		$posts = get_posts($args);

		$recommended_products_ids = array_map(function ($post) {
			return $post->ID;
		}, $posts);

		return $recommended_products_ids;
	}

	/**
	 * Nonce fixe for logged out user
	 */
	public function nonce_fix($uid = 0, $action = '') {
		$nonce_actions = array('lc-ajax-modal', 'lc-add-to-cart');
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
		if(!class_exists('GetWooPlugins_Admin_Settings')) {
			include_once('getwooplugins/class-getwooplugins-admin-settings.php');
		}
		return \GetWooPlugins_Admin_Settings::get_option( $this->get_settings_id() );
	}

	/**
	 * Get setting field data
	 *
	 * @since      1.0.0
	 * @param id settings field id
	 * @return  mixed value of setting field
	 */
	public function get_setting($id, $default = null) {
		if(!class_exists('GetWooPlugins_Admin_Settings')) {
			include_once('getwooplugins/class-getwooplugins-admin-settings.php');
		}

		$options = \GetWooPlugins_Admin_Settings::get_option( $this->get_settings_id() );
		return isset( $options[ $id ] ) ? $options[ $id ] : $default;
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
		<span class="lpr-total-items"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
		<?php
		$fragments['a.lpr-cart-count .lpr-total-items'] = ob_get_clean();
		return $fragments;
	}

	/**
	 * Get global recommendations data from setting page
	 * 
	 * @since  1.0.0
	 * @return array of global setting 
	 */
	public function get_global_pr_data() {
		$data = array();

		if ($this->get_setting('active_global_settings', 'yes') !== 'yes') {
			return $data;
		}

		$data['category_type'] 	= $this->get_setting('global_categories', 'same_categories');
		$data['categories'] 	= $this->get_setting('global_custom_categories', array());
		$data['number'] 		= $this->get_setting('global_products_number', 12);
		$data['tags'] 			= $this->get_setting('global_tags', array());
		$data['orderby'] 		= $this->get_setting('global_filtering', 'rand');
		$data['sale'] 			= $this->get_setting('global_on_sale', 'no') === 'yes' ? true : false;

		return $data;
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
			'plugin_name' => $this->get_plugin_name(),
			'plugin_version' => $this->get_plugin_version(),
			'plugin_slug' => $this->get_slug(),
			'support' => array(
				'title' => __('Need Help?', 'leo-product-recommendations'),
				'support_url' => 'https://cutt.ly/UjXivGe'
			),
			'feedback_heading' => __('Quick Feedback', 'leo-product-recommendations'),
			'form_heading' => __('May we have a little info about why you are deactivating?', 'leo-product-recommendations'),
			'api_url' => 'https://leocoder.com/wp-json/leocoder/v1/deactivation-feedback',

			'fields' => array(
				array(
					'category' => 'temporary_deactivation',
					'reason' => __('It\'s a temporary deactivation.', 'leo-product-recommendations', 'leo-product-recommendations'),
					'instruction' => '',
					'placeholder' => '',
					'input_default' => '',
				),

				array(
					'category' => 'not_show_image_button',
					'reason' => __('Does not show product image or add to cart button.', 'leo-product-recommendations', 'leo-product-recommendations'),
					'instruction' => '<a href="https://cutt.ly/UjXivGe" target="_blank">' . __('Please contact with our support we will try fix it quickly for you »', 'leo-product-recommendations') . '</a>',
					'input_field' => '',
					'placeholder' => '',
					'input_default' => '',
				),

				array(
					'category' => 'dont_understand',
					'reason' => __('I couldn\'t understand how to use it.', 'leo-product-recommendations'),
					'instruction' => '<a href="https://cutt.ly/rjE8eiu" target="_blank">' . __('Please check details documentations »', 'leo-product-recommendations') . '</a>',
					'input_field' => '',
					'placeholder' => '',
					'input_default' => '',
				),

				array(
					'category' => 'not_works',
					'reason' => __('It doesn\'t work with my website.', 'leo-product-recommendations'),
					'instruction' => '<a href="https://cutt.ly/UjXivGe" target="_blank">' . __('Please contact with our support we will try fix it quickly for you »', 'leo-product-recommendations') . '</a>',
					'input_field' => '',
					'placeholder' => '',
					'input_default' => '',
				),

				array(
					'category' => 'look_bad',
					'reason' => __('It looks bad on my website.', 'leo-product-recommendations'),
					'instruction' => '<a href="https://cutt.ly/UjXivGe" target="_blank">' . __('Please contact with our support we will help you to fix the issues »', 'leo-product-recommendations') . '</a>',
					'input_field' => '',
					'placeholder' => '',
					'input_default' => '',
				),

				array(
					'category' => 'dont_need',
					'reason' => __('It works nicely but I don’t need it now.', 'leo-product-recommendations'),
					'instruction' => '<a href="https://cutt.ly/ZjXi7q8" target="_blank">' . __('Please encourage us by giving nice feedback  »', 'leo-product-recommendations') . '</a>',
					'input_field' => '',
					'placeholder' => '',
					'input_default' => '',
				),

				array(
					'category' => 'need_help',
					'reason' => __('I need some help to set up the plugin.', 'leo-product-recommendations'),
					'instruction' => __('Please provide your email address we will contact you soon.', 'leo-product-recommendations'),
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
					'instruction' => __('Please let us know feature details we will try add it ASAP', 'leo-product-recommendations'),
					'input_field' => 'textarea',
					'placeholder' => __('Require feature details', 'leo-product-recommendations'),
					'input_default' => '',
				),

				array(
					'category' => 'other',
					'reason' => __('Other.', 'leo-product-recommendations'),
					'instruction' => '',
					'input_field' => 'textarea',
					'placeholder' => __('Please share the reason. We will try to fix / help you.', 'leo-product-recommendations'),
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