<?php
/**
 * The core plugin class
 *
 * @since      1.0.0
 * @author     LeoCoder
 */

class LC_Woo_Product_Recommendations {
    /**
     * Inctance of class
     *
     * @var instance
     */
    static protected $instance;

    /**
     * Array of all products recommendations data.
     *
     * @var instance
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
    static protected $__FILE__PRO__ = WP_PLUGIN_DIR . '/woocommerce-product-recommendations-pro/woocommerce-product-recommendations-pro.php';

    /**
     * Plugin setting id used to save setting data
     *
     * @var string
     * @since      1.0.0
     */
    static protected $setting_id = 'lc_wpr_settings';

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
     * Action after plugin active.
     * enable ajax add to cart and disabel redirec to cart page.
     *
     * @since      1.0.0
     */
    public function on_activation() {
        update_option('woocommerce_enable_ajax_add_to_cart', 'yes');
        update_option('woocommerce_cart_redirect_after_add', 'no');
        $this->add_default_settings();
    }

    /**
     * Action after plugin deactive
     *
     * @since      1.0.0
     * @return void
     */
    public function on_deactivation() {
        // do nothing
    }

    /**
     * Action after plugin deactive
     *
     * @since      1.0.0
     * @return void
     */
    public function add_default_settings() {
        $settings         = $this->get_settings();
        $settings         = !empty($settings) ? $settings : array();
        $default_settings = $this->get_default_settings();
        update_option($this->get_settings_id(), array_merge($default_settings, $settings));
    }

    /**
     * Setup plugin once all other plugins are loaded.
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

        // before going to action
        // used this hook in pro plugin
        do_action('wpr_before_action');

        $this->includes();
        $this->hooks();
    }

    /**
     * Load Localization files.
     *
     * @since      1.0.0
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain('woocommerce-product-recommendations', false, dirname(plugin_basename(self::$__FILE__)) . '/languages');
    }

    /**
     * Returns true if all dependencies for the plugin are loaded.
     *
     * @since      1.0.0
     * @return bool
     */
    protected function has_satisfied_dependencies() {
        $dependency_errors = $this->get_dependency_errors();
        return 0 === count($dependency_errors);
    }

    /**
     * Get an array of dependency error messages.
     *
     * @since      1.0.0
     * @return array all dependency error message.
     */
    protected function get_dependency_errors() {
        $errors                      = array();
        $wordpress_version           = get_bloginfo('version');
        $minimum_wordpress_version   = $this->get_min_wp();
        $minimum_woocommerce_version = $this->get_min_wc();
        $minium_php_verion           = $this->get_min_php();

        $wordpress_minimum_met   = version_compare($wordpress_version, $minimum_wordpress_version, '>=');
        $woocommerce_minimum_met = class_exists('WooCommerce') && version_compare(WC_VERSION, $minimum_woocommerce_version, '>=');
        $php_minimum_met         = version_compare(phpversion(), $minium_php_verion, '>=');

        if (!$woocommerce_minimum_met) {

            $errors[] = sprintf(
                /* translators: 1. link of plugin, 2. plugin version. */
                __('The WooCommerce Product Recommendations plugin requires <a href="%1$s">WooCommerce</a> %2$s or greater to be installed and active.', 'woocommerce-product-recommendations'),
                'https://wordpress.org/plugins/woocommerce/',
                $minimum_woocommerce_version
            );
        }

        if (!$wordpress_minimum_met) {
            $errors[] = sprintf(
                /* translators: 1. link of wordpress, 2. version of WordPress. */
                __('The WooCommerce Product Recommendations plugin requires <a href="%1$s">WordPress</a> %2$s or greater to be installed and active.', 'woocommerce-product-recommendations'),
                'https://wordpress.org/',
                $minimum_wordpress_version
            );
        }

        if (!$php_minimum_met) {
            $errors[] = sprintf(
                /* translators: 1. version of php */
                __('The WooCommerce Product Recommendations plugin requires <strong>php verion %s</strong> or greater. Please update your server php version.', 'woocommerce-product-recommendations'),
                $minium_php_verion
            );
        }

        return $errors;
    }

    /**
     * Notify users about plugin dependency
     *
     * @since      1.0.0
     * @return void
     */
    public function render_dependencies_notice() {
        $message = $this->get_dependency_errors();
        printf('<div class="error"><p>%s</p></div>', implode(' ', $message));
    }

    /**
     * Include addon features with this plugin
     *
     * @since      1.0.0
     * @return void
     */
    public function includes() {
        // handle all admin ajax request
        $this->admin_ajax();
        $this->plugin_action_links();
        if (!$this->is_pro_activated()) {
            $this->plugin_settins();
        }
    }

    /**
     * Handle All selection panel Ajax Request
     * @since      1.0.0
     * @return void
     */
    public function admin_ajax() {
        if (!class_exists('LC_Wpr_Admin_Ajax')) {
            include_once $this->get_path('includes/class-lc-wpr-admin-ajax.php');
        }
        new LC_Wpr_Admin_Ajax();
    }

    /**
     * Add plugin action link
     * @since      1.0.0
     */
    public function plugin_action_links() {
        add_action('plugin_action_links_' . plugin_basename(self::$__FILE__), function ($links) {
            $settings = array(
                'settings' => '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=wpr-settings')) . '">' . __('Settings', 'woocommerce-product-recommendations') . '</a>',
            );
            return array_merge($settings, $links);
        });
    }

    /**
     * Handle All selection panel Ajax Request
     * @since      1.0.0
     * @return void
     */
    public function plugin_settins() {
        if (!class_exists('LC_Wpr_Settings_Page')) {
            require_once $this->get_path('includes/class-lc-wpr-settings-page.php');
        }

        new LC_Wpr_Settings_Page($this);
    }

    /**
     * Add all actions hook.
     *
     * @since      1.0.0
     * @return void
     */
    public function hooks() {
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts')); // back-end scripts
        add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts')); // front-end scripts

        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'on_save_post'));

        add_action('wp_ajax_fetch_modal_products', array($this, 'fetch_modal_products'));
        add_action('wp_ajax_nopriv_fetch_modal_products', array($this, 'fetch_modal_products'));

        add_action('wp_ajax_lc_ajax_add_to_cart', array($this, 'ajax_add_to_cart'));
        add_action('wp_ajax_nopriv_lc_ajax_add_to_cart', array($this, 'ajax_add_to_cart'));

        add_action('wp_ajax_lc_get_cart_items', array($this, 'get_cart_items'));
        add_action('wp_ajax_nopriv_lc_get_cart_items', array($this, 'get_cart_items'));

        add_filter('nonce_user_logged_out', array($this, 'nonce_fix'), 100, 2);

        if (!is_admin()) {
            add_action('after_setup_theme', array($this, 'include_templates')); // include modal template
        }

        if (!$this->is_pro_activated()) {
            add_action('wp_head', array($this, 'settings_css')); // for custom styling
        }
    }

    /**
     * Enqueue all admin scripts and styles
     *
     * @since      1.0.0
     * @return void
     */
    public function admin_enqueue_scripts() {
        $version = $this->script_version();
        wp_enqueue_editor();
        if (!$this->is_pro_activated()) {
            wp_enqueue_script('selection-panel-script', $this->get_url('assets/js/panel.min.js'), array('lodash', 'wp-element', 'wp-components', 'wp-polyfill', 'wp-i18n', 'jquery'), $version, true);
            wp_localize_script('selection-panel-script', 'ajax_url', admin_url('admin-ajax.php'));
            wp_enqueue_style('selection-panel-style', $this->get_url('assets/css/panel.css'), '', $version);
        }

        $screen = get_current_screen();
        if ($screen->id === 'toplevel_page_wpr-settings') {
            wp_enqueue_style('wpr-settings', $this->get_url('assets/css/settings.css'), array(), $version);
            wp_enqueue_script('spectrum', $this->get_url('assets/js/color-picker/spectrum.js'), array('jquery'), $version, true);
            wp_enqueue_script('wpr-settings', $this->get_url('assets/js/settings.min.js'), array('jquery', 'spectrum', 'wp-i18n'), $version, true);
            wp_enqueue_style('wpr-spectrum', $this->get_url('assets/js/color-picker/spectrum.css'), array(), $version);

            wp_enqueue_script('wpr-select2', $this->get_url('assets/js/select2/select2.min.js'), array('spectrum'), $version, true);
            wp_enqueue_style('wpr-select2', $this->get_url('assets/js/select2/select2.min.css'), array(), $version);

            $cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/css'));
            wp_localize_script('wpr-settings', 'wpr_css_editor', $cm_settings);
            wp_enqueue_style('wp-codemirror');
            wp_enqueue_script('wp-theme-plugin-editor');
        }
    }

    /**
     * Enqueue all front end scripts and styles
     *
     * @since      1.0.0
     * @return void
     */
    public function wp_enqueue_scripts() {
        $version = $this->script_version();
        $settings    = $this->get_settings();
        $layout_type = ($this->is_pro_activated() && !empty($settings['layout_type'])) ? $settings['layout_type'] : 'grid';

        wp_enqueue_script('wpr-modal', $this->get_url('assets/js/modal.min.js'), array('jquery'), $version, true);
        wp_localize_script('wpr-modal', 'lc_ajax_modal', array(
            'url'         => admin_url('admin-ajax.php'),
            'nonce'       => wp_create_nonce('lc-ajax-modal'),
            'layout_type' => $layout_type,
        ));

        // if (is_product()) { // disabled condition so that it work for quick view
        wp_enqueue_script('wpr-ajax-add-to-cart', $this->get_url('assets/js/ajax-add-to-cart.min.js'), array('jquery', 'wp-i18n'), $version, true);
        wp_localize_script('wpr-ajax-add-to-cart', 'lc_ajax', array(
            'url'   => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lc-add-to-cart'),
        ));
        // }

        if (!$this->is_pro_activated()) {
            wp_enqueue_style('wpr-modal', $this->get_url('assets/css/modal.css'), array(), $version);
        }
    }

    /**
     * Modal CSS
     *
     * @since      1.0.0
     */
    public function settings_css() {
        // grid
        $items_desktop      = $this->get_setting('grid_lg_items');
        $items_tablet       = $this->get_setting('grid_md_items');
        $items_mobile       = $this->get_setting('grid_sm_items');
        $grid_column_gap    = (int) $this->get_setting('grid_column_gap') + 1;
        $desktop_item_width = 100 / $items_desktop;
        $tablet_item_width  = 100 / $items_tablet;
        $mobile_item_width  = 100 / $items_mobile;

        // custom css
        $custom_css = $this->get_setting('custom_style') ? $this->get_setting('custom_style') : '';
        ?>
		<style id="wpr-settings-css-front-end">
			.wpr-modal .wpr-modal-content ul.recommended-products-list {
				margin: 0 <?php echo -$grid_column_gap / 2; ?>px !important;
			}
			.wpr-modal .wpr-modal-content ul.recommended-products-list li.single-wpr {
				flex: 0 0 calc(<?php echo $desktop_item_width . '% - ' . $grid_column_gap . 'px'; ?>);
				width: calc(<?php echo $desktop_item_width . '% - ' . $grid_column_gap . 'px'; ?>);
				margin-left: <?php echo $grid_column_gap / 2; ?>px !important;
				margin-right: <?php echo $grid_column_gap / 2; ?>px !important;
			}
			@media screen and (max-width: 991px) {
				.wpr-modal .wpr-modal-content ul.recommended-products-list li.single-wpr {
					flex: 0 0 calc(<?php echo $tablet_item_width . '% - ' . $grid_column_gap . 'px'; ?>);
					width: calc(<?php echo $tablet_item_width . '% - ' . $grid_column_gap . 'px'; ?>);
					margin-left: <?php echo $grid_column_gap / 2; ?>px !important;
					margin-right: <?php echo $grid_column_gap / 2; ?>px !important;
				}
			}
			@media screen and (max-width: 767px) {
				.wpr-modal .wpr-modal-content ul.recommended-products-list li.single-wpr {
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
     * Add Meta Box
     *
     * @since      1.0.0
     * @return void
     */
    public function add_meta_boxes() {
        add_meta_box(
            'lc_pr_prodcut_selection',
            __('Product Recommendations', 'woocommerce-product-recommendations'),
            array($this, 'product_selection'),
            array('product')
        );
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
     * Plugin file URL
     *
     * @since      1.0.0
     * @return     URL link of file.
     * @param      stirng File name with folder path.
     */
    public function get_url($file = '') {
        return plugin_dir_url(self::$__FILE__) . $file;
    }

    /**
     * Get file path of plugin file
     *
     * @since      1.0.0
     * @param      stirng relative path of plugin file
     * @return     string full path of plugin file
     */
    public function get_path($file_path) {
        return plugin_dir_path(self::$__FILE__) . $file_path;
    }

    /**
     * Get template path from theme or plugin
     *
     * @since      1.0.0
     * @param      stirng File name with folder path.
     * @return     string Full of template from theme or pro plguin or plugin.
     */
    public function get_templates_path($file_path) {
        //from theme
        $theme_tmpl = get_stylesheet_directory() . '/wpr/' . $file_path;
        if (file_exists($theme_tmpl)) {
            return $theme_tmpl;
        }

        //from pro version
        if ($this->is_pro_activated()) {
            return plugin_dir_path(self::$__FILE__PRO__) . $file_path;
        }

        //from plugin
        return plugin_dir_path(self::$__FILE__) . $file_path;
    }

    /**
     * Get plugin slug
     *
     * @since      1.0.0
     * @return     string slug of plugin
     */
    public function get_slug() {
        return basename(self::$__FILE__, '.php');
    }

    /**
     * Save post callback function
     *
     * @since      1.0.0
     * @return  void;
     */
    public function on_save_post($id) {
        if (isset($_POST['_lc_wpr_data'])) {
            update_post_meta($id, '_lc_wpr_data', $_POST['_lc_wpr_data']);
        }
    }

    /**
     * Ajax call back to query modal products
     *
     * @since      1.0.0
     * @return  void;
     */

    public function fetch_modal_products() {
        $nonce                   = $_GET['nonce'];
        $recommended_products_id = $_GET['recommendation_items'];
        $layout_type             = $_GET['layout_type'];

        if (!isset($nonce) || !wp_verify_nonce($nonce, 'lc-ajax-modal') || !isset($recommended_products_id)) {
            wp_send_json_error(array('message' => 'Bad request'), 400);
        }

        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'post__in'       => $recommended_products_id,
            'orderby'        => 'post__in',
        );

        $loop = new WP_Query($args);
        if ($loop->have_posts()): while ($loop->have_posts()): $loop->the_post();
                include $this->get_templates_path('templates/template-recommendations-products.php');
            endwhile;
            wp_reset_postdata();endif;

        wp_die();
    }

    /**
     * Ajax callback to add to cart
     *
     * @since      1.0.0
     * @return  json responsve with json data
     */
    public function ajax_add_to_cart() {

        if ($_REQUEST['data'] && $_REQUEST['nonce'] && wp_verify_nonce($_REQUEST['nonce'], 'lc-add-to-cart')) {
            if (!class_exists('LC_Ajax_Add_To_Cart')) {
                include $this->get_path('includes/class-lc-ajax-add-to-cart.php');
            }

            new LC_Ajax_Add_To_Cart($_REQUEST['data']);
        } else {
            wp_send_json_error(array('message' => 'Bad request'), 400);
        }
    }

    /**
     * Ajax callback to get items already added to cart
     *
     * @since      1.0.0
     * @return  json response with json data of cart products
     */
    public function get_cart_items() {
        $products_ids_array = array();
        foreach (WC()->cart->get_cart() as $cart_item) {
            $products_ids_array[] = $cart_item['product_id'];
        }
        wp_send_json($products_ids_array);
    }

    /**
     * Include modal templates
     * @since      1.0.0
     * @return  void;
     */
    public function include_templates() {
        // modal in shop / archives page
        if (apply_filters('wc_pr_show_in_product_archives', true)) {
            add_action('woocommerce_after_shop_loop_item', array($this, 'product_archive_modal'));
        }

        // modal in single product page
        if (apply_filters('wc_pr_show_in_singe_product', true)) {
            add_action('wp_footer', array($this, 'product_single_modal'));
        }

        // modal in WooCommerce Gutenberg products block
        if (apply_filters('wc_pr_show_in_gutenberg_product_block', true)) {
            add_filter('woocommerce_blocks_product_grid_item_html', array($this, 'product_gutenberg_block'), 10, 3);
        }
    }

    /**
     * Get recommendation products modal heading
     *
     * @param int $product_id
     * @return string heading of recommendation products
     * @since   1.0.0
     */
    public function get_template_data($product_id, $recommended_products_id) {
        $template_data                            = array();
        $template_data['product_id']              = $product_id;
        $template_data['recommended_products_id'] = $recommended_products_id;
        //heaidng
        $pr_data = $this->get_pr_data($product_id);

        $modal_heading   = (!!$pr_data && isset($pr_data['heading'])) ? $pr_data['heading'] : '';
        $default_heading = $this->get_setting('default_heading');
        $default_heading = !empty($default_heading) ? $default_heading : '';
        // cart products
        $cart_products_ids = array();

        foreach (WC()->cart->get_cart() as $cart_item) {
            $cart_products_ids[] = $cart_item['product_id'];
        }

        $selectable_products = array_filter($recommended_products_id, function ($item) use ($cart_products_ids) {
            return !in_array($item, $cart_products_ids);
        });

        $html_permission = array(
            'span'   => array('class'),
            'b'      => array(),
            'strong' => array(),
            'i'      => array(),
            'br'     => array(),
        );

        $modal_heading = !empty(trim($modal_heading)) ? $modal_heading : $default_heading;
        $modal_heading = wp_kses($modal_heading, $html_permission);
        $modal_heading = str_replace('%title%', get_the_title($product_id), $modal_heading);
        $modal_heading = preg_replace('/\[(.+),(.+)\]/', _n('${1}', '${2}', count($selectable_products)), $modal_heading);

        $heading_type    = isset($pr_data['heading_type']) ? $pr_data['heading_type'] : 'heading';
        $heading_article = isset($pr_data['heading_article']) ? $pr_data['heading_article'] : '';

        $modal_heading = ($heading_type === 'heading')
        ? '<h2 class="modal-heading">' . $modal_heading . '</h2>' :
        '<div class="modal-heading-article">' . do_shortcode($heading_article) . '</div>';
        $template_data['modal_heading'] = $modal_heading;

        //visiblity items
        $show_close_icon                         = $this->get_setting('show_close_icon');
        $template_data['show_close_icon']        = $show_close_icon;
        $show_continue_shopping                  = $this->get_setting('show_continue_shopping');
        $template_data['show_continue_shopping'] = $show_continue_shopping;
        $show_go_check_out                       = $this->get_setting('show_go_check_out');
        $template_data['show_go_check_out']      = $show_go_check_out;

        $layout_type                  = $this->get_setting('layout_type');
        $layout_type                  = !empty($layout_type) ? $layout_type : 'grid';
        $template_data['layout_type'] = $layout_type;

        return $template_data;
    }

    /**
     * Check selection method menual or not
     * @return bool menually selected or not
     * @since   1.0.0
     */
    public function is_menually_selection($product_id) {
        $pr_data = $this->get_pr_data($product_id);
        return (!empty($pr_data['type']) && $pr_data['type'] === 'menual-selection');
    }

    /**
     * Check selection method dynamic or not
     * @return bool dynamic selected or not
     * @since   1.0.0
     */
    public function is_dynamic_selection($product_id) {
        $pr_data = $this->get_pr_data($product_id);
        return (!empty($pr_data['type']) && $pr_data['type'] === 'dynamic-selection');
    }

    /**
     * Array of recommendation product ids
     *
     * @param int $product_id
     * @return array array of recommendation products ids
     * @since      1.0.0
     */
    public function get_recommended_products_id($product_id) {
        if ($this->is_active_global($product_id)) {
            return $this->dynamic_query_products($product_id, $this->get_global_pr_data());
        }

        $data = $this->get_pr_data($product_id);
        if ($this->is_menually_selection($product_id)) {
            return $this->menual_query_products($data);
        } elseif ($this->is_dynamic_selection($product_id)) {
            return $this->dynamic_query_products($product_id, $data);
        }
        return array();
    }

    /**
     * Check localhost selection available or not
     * @since      1.0.0
     * @return void;
     */
    public function is_active_global($id) {

        $settings          = $this->get_settings();
        $has_global        = !empty($settings['active_global_settings']) ? true : false;
        $disable_overwrite = !empty($settings['disable_global_override']) ? true : false;
        // active global and not overwirte by local
        if ($has_global && $disable_overwrite) {
            return true;
        }
        // active global and not available local
        $is_dynamic_selection = $this->is_dynamic_selection($id);
        $data                 = $this->get_pr_data($id);
        $recommended_products = !empty($data['products']) ? $data['products'] : array();

        if ($has_global && !$is_dynamic_selection && empty($recommended_products)) {
            return true;
        }

        return false;
    }

    public function menual_query_products($data) {
        return !empty($data['products']) ? $data['products'] : array();
    }

    /**
     * Get dynamic data
     */
    public function dynamic_query_products($product_id, $data) {
        $args = array(
            'post_type'    => 'product',
            'post__not_in' => array($product_id),
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

        if (!empty($categories) && !empty($tags)) {
            $args['tax_query'] = array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $categories,
                ),
                array(
                    'taxonomy' => 'product_tag',
                    'field'    => 'term_id',
                    'terms'    => $tags,
                ),
            );
        } else if (!empty($categories)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $categories,
                ),
            );
        } else if (!empty($tags)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_tag',
                    'field'    => 'term_id',
                    'terms'    => $tags,
                ),
            );
        }

        $orderby  = 'date';
        $meta_key = '';
        $order    = 'desc';

        switch ($data['orderby']) {
        case 'newest':
            $orderby  = 'date';
            $meta_key = '';
            $order    = 'desc';
            break;

        case 'oldest':
            $orderby  = 'date';
            $meta_key = '';
            $order    = 'asc';
            break;

        case 'rand':
            $orderby  = 'rand';
            $meta_key = '';
            $order    = 'desc';
            break;

        case 'popularity':
            $orderby  = 'meta_value_num';
            $meta_key = 'total_sales';
            $order    = 'desc';
            break;

        case 'rating':
            $orderby  = 'meta_value_num';
            $meta_key = '_wc_average_rating';
            $order    = 'desc';
            break;

        case 'lowprice':
            $orderby  = 'meta_value_num';
            $meta_key = '_regular_price';
            $order    = 'asc';
            break;

        case 'highprice':
            $orderby  = 'meta_value_num';
            $meta_key = '_regular_price';
            $order    = 'desc';
            break;

        case 'title':
            $orderby  = 'title';
            $meta_key = '';
            $order    = 'asc';
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
     * Add modal to archive / shop page prodcuts
     * @since      1.0.0
     * @return void;
     */
    public function product_archive_modal() {
        global $product;
        $product_id = $product->get_id();

        if ($this->is_active_global($product_id) || $this->is_pro_activated() || $this->is_menually_selection($product_id)): // free version does not support dynamic selection

            $recommended_products_id = $this->get_recommended_products_id($product_id);

            if (!empty($recommended_products_id)) {
                $data = $this->get_template_data($product_id, $recommended_products_id);
                add_action('wp_footer', function () use ($data) {
                    include ($this->get_templates_path('templates/template-modal.php'));
                });
            }
        endif;
    }

    /**
     * Add modal to single product page
     * @since      1.0.0
     * @return void;
     */
    public function product_single_modal() {
        if (!is_product()) {
            return false;
        }

        global $product;
        $product_id = $product->get_id();

        if ($this->is_active_global($product_id) || $this->is_pro_activated() || $this->is_menually_selection($product_id)): // free version does not support dynamic selection
            $recommended_products_id = $this->get_recommended_products_id($product_id);
            if (!empty($recommended_products_id)) {
                $data = $this->get_template_data($product_id, $recommended_products_id);
                include $this->get_templates_path('templates/template-modal.php');
            }
        endif;
    }

    /**
     * Add modal to Guterberg block product
     * @since      1.0.0
     */
    public function product_gutenberg_block($html, $data, $product) {
        $product_id = $product->get_id();

        if ($this->is_active_global($product_id) || $this->is_pro_activated() || $this->is_menually_selection($product_id)): // free version does not support dynamic selection

            $recommended_products_id = $this->get_recommended_products_id($product_id);

            if (!empty($recommended_products_id)) {
                $data = $this->get_template_data($product_id, $recommended_products_id);

                add_action('wp_footer', function () use ($data) {
                    include ($this->get_templates_path('templates/template-modal.php'));
                });
            }
        endif;

        return $html;
    }

    public function nonce_fix($uid = 0, $action = '') {
        $nonce_actions = array('lc-ajax-modal', 'lc-add-to-cart');
        if (in_array($action, $nonce_actions)) {
            return 0;
        }
        return $uid;
    }

    /**
     * Require php version
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
     * Require WooCommerce Version
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
     * Require WordPress Version
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
     *
     * @return  array Value of the plugins settings.
     */
    public function get_settings() {
        return get_option($this->get_settings_id());
    }

    /**
     * Get default settings
     *
     * @since      1.0.0
     *
     * @return  array of default value of all sertting when default avaiable
     */
    public function get_default_settings() {
        $default_settings = array();
        $pages            = $this->settings_pages();

        foreach ($pages as $page) {
            if (isset($page['sections']) && !empty($page['sections'])) {

                foreach ($page['sections'] as $section) {

                    if (isset($section['fields']) && !empty($section['fields'])) {

                        foreach ($section['fields'] as $field) {
                            if (isset($field['default'])) {
                                $default_settings[$field['id']] = $field['default'];
                            }

                            if (!empty($field['childs'])) {
                                foreach ($field['childs'] as $child_field) {
                                    if (isset($child_field['default'])) {
                                        $default_settings[$child_field['id']] = $child_field['default'];
                                    }

                                }
                            }
                        }
                    }
                }
            }
        }

        return $default_settings;
    }

    /**
     * Check Pro version of the plugin already installed or not
     *
     * @since      1.0.0
     */
    public function is_pro_activated() {
        return class_exists('LC_Woo_Product_Recommendations_Pro');
    }

    /**
     * Get prodcut recommendation meta data
     * @since      1.0.0
     * @return object post meta of _lc_wpr_data
     */
    public function get_pr_data($id) {
        if (!isset(self::$pr_meta[$id])) {
            self::$pr_meta[$id] = get_post_meta($id, '_lc_wpr_data', true);
        }

        return self::$pr_meta[$id];
    }

    public function get_global_pr_data() {
        $settings = $this->get_settings();
        if (empty($settings['active_global_settings'])) {
            return array();
        }

        $category_type   = !empty($settings['global_categories']) ? $settings['global_categories'] : 'same_categories';
        $categories      = !empty($settings['global_custom_categories']) ? $settings['global_custom_categories'] : array();
        $tags            = !empty($settings['global_tags']) ? $settings['global_tags'] : array();
        $filtering       = !empty($settings['global_filtering']) ? $settings['global_filtering'] : 'rand';
        $onsale          = !empty($settings['global_on_sale']) ? $settings['global_on_sale'] : false;
        $number_of_posts = !empty($settings['global_products_number']) ? $settings['global_products_number'] : 12;

        $data                  = array();
        $data['category_type'] = $category_type;
        $data['categories']    = $categories;
        $data['number']        = $number_of_posts;
        $data['tags']          = $tags;
        $data['orderby']       = $filtering;
        $data['sale']          = $onsale;

        return $data;
    }

    /**
     * Get settings
     *
     * @since      1.0.0
     * @param id settings field id
     * @return  array get single setting value by setting id
     */
    public function get_setting($id) {
		
        $settings = $this->get_settings();
        $field_type = $this->get_field_type($id);
        $value = isset($settings[$id]) ? $settings[$id] : $this->get_default_setting($id);

        if ($field_type === 'checkbox' && $settings && !isset($settings[$id])) {
            $value = null;
        }

        if (!$value) {
            return $value;
        }

        // text field allowed tag
        $html_permission = array(
            'span'   => array('class'),
            'b'      => array(),
            'strong' => array(),
            'i'      => array(),
            'br'     => array(),
        );

        switch ($field_type) {
        case 'text':
            $value = wp_kses($value, $html_permission);
            break;

        case 'color_picker':
            $value = esc_attr($value);
            break;

        case 'number':
            $value = (int) esc_attr($value);
            break;

        case 'css':
            $value = sanitize_textarea_field($value);
            break;
        }
        return $value;
    }

    /**
     * Get Default Setting Value
     * @param id settings field id
     * @since      1.0.0
     */
    public function get_default_setting($id) {
        $pages = $this->settings_pages();

        foreach ($pages as $page) {
            if (isset($page['sections']) && !empty($page['sections'])) {

                foreach ($page['sections'] as $section) {

                    if (isset($section['fields']) && !empty($section['fields'])) {

                        foreach ($section['fields'] as $field) {
                            if ($field['id'] === $id && isset($field['default'])) {
                                return $field['default'];
                            }

                            if (!empty($field['childs'])) {
                                foreach ($field['childs'] as $child_field) {

                                    if ($child_field['id'] === $id && isset($child_field['default'])) {
                                        return $child_field['default'];
                                    }

                                }
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * get setting field type by setting field ID
     * @param id setting field ID
     *
     * @since      1.0.0
     */
    public function get_field_type($id) {
        $pages = $this->settings_pages();

        foreach ($pages as $page) {
            if (isset($page['sections']) && !empty($page['sections'])) {

                foreach ($page['sections'] as $section) {

                    if (isset($section['fields']) && !empty($section['fields'])) {
                        foreach ($section['fields'] as $field) {
                            if ($field['id'] === $id) {
                                return isset($field['type']) ? $field['type'] : false;
                            }

                            if (!empty($field['childs'])) {
                                foreach ($field['childs'] as $child_field) {
                                    if ($child_field['id'] === $id) {
                                        return isset($child_field['type']) ? $child_field['type'] : false;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * All settings pages including section and fields
     *
     * @since      1.0.0
     * @return  array of settings pages
     */
    public function settings_pages() {
        $general_settings_fields = array(
            array(
                'id'          => 'default_heading',
                'title'       => __('Default Heading', 'woocommerce-product-recommendations'),
                'type'        => 'text',
                'description' => __('If you like to use same heading patternt for all recommendations then use default heading. Use pattern <strong>%title%</strong> for product title. Pattern <strong>[item, items]</strong> is changeable. You can use <strong>[product, products]</strong> or anything that makes sense. Singular word for single recommended product and plural word for multiple recommended products.', 'woocommerce-product-recommendations'),
                'default'     => __('You may purchase following [item, items] with the %title%', 'woocommerce-product-recommendations'),
            ),

            array(
                'id'     => 'grid_options',
                'title'  => __('Grid Options', 'woocommerce-product-recommendations'),
                'type'   => 'wrapper',
                'childs' => array(

                    array(
                        'id'      => 'grid_lg_items',
                        'title'   => __('Desktop Items', 'woocommerce-product-recommendations'),
                        'type'    => 'number',
                        'min'     => 2,
                        'max'     => 5,
                        'default' => 4,
                    ),

                    array(
                        'id'      => 'grid_md_items',
                        'title'   => __('Tablet Items', 'woocommerce-product-recommendations'),
                        'type'    => 'number',
                        'min'     => 2,
                        'max'     => 5,
                        'default' => 3,
                    ),

                    array(
                        'id'      => 'grid_sm_items',
                        'title'   => __('Mobile Items', 'woocommerce-product-recommendations'),
                        'type'    => 'number',
                        'min'     => 1,
                        'max'     => 3,
                        'default' => 2,
                    ),
                    array(
                        'id'      => 'grid_column_gap',
                        'title'   => __('Column Gap', 'woocommerce-product-recommendations'),
                        'type'    => 'number',
                        'sufix'   => 'px',
                        'min'     => 0,
                        'max'     => 60,
                        'default' => 20,
                    ),
                ),
            ),
        );

        $style_settings = array(
            array(
                'id'          => 'custom_style',
                'title'       => __('Custom CSS', 'woocommerce-product-recommendations'),
                'type'        => 'css',
                'description' => __('Write custom css to change style of modal.', 'woocommerce-product-recommendations'),
            ),
        );

        $global_settings_fields = array(
            array(
                'id'          => 'active_global_settings',
                'title'       => __('Active Global Setting', 'woocommerce-product-recommendations'),
                'type'        => 'checkbox',
                'description' => __('If there are no recommendations available for some products (if you don\'t setup from product editor), the global setting will work for those products as a fallback. This setting is also helpful if you like a bulk recommendation setup for entire shop instead of a different setup for each product.', 'woocommerce-product-recommendations'),
            ),
            array(
                'id'     => 'selection_options',
                'title'  => __('Recommendation Options', 'woocommerce-product-recommendations'),
                'type'   => 'wrapper_extend',
                'childs' => array(
                    array(
                        'id'      => 'global_categories',
                        'title'   => __('Categories', 'woocommerce-product-recommendations'),
                        'type'    => 'radio',
                        'options' => array(
                            'same_categories'   => __('Product Related Category', 'woocommerce-product-recommendations'),
                            'menual_categories' => __('Menual', 'woocommerce-product-recommendations'),
                        ),
                        'default' => 'same_categories',
                    ),
                    array(
                        'id'    => 'global_custom_categories',
                        'title' => __('Choose Categories', 'woocommerce-product-recommendations'),
                        'type'  => 'categories_select',
                    ),

                    array(
                        'id'    => 'global_tags',
                        'title' => __('Choose Tags', 'woocommerce-product-recommendations'),
                        'type'  => 'tags_select',
                    ),

                    array(
                        'id'      => 'global_filtering',
                        'title'   => __('Products Filtering', 'woocommerce-product-recommendations'),
                        'type'    => 'select',
                        'options' => array(
                            'rand'       => 'Random Products',
                            'newest'     => 'Newest Products',
                            'oldest'     => 'Oldest Products',
                            'lowprice'   => 'Low Price Products',
                            'highprice' => 'High Price Products',
                            'popularity' => 'Best Selling Products',
                            'rating'     => 'Top Rated Products',
                        ),
                    ),
                    array(
                        'id'    => 'global_on_sale',
                        'title' => __('On-Sale Only', 'woocommerce-product-recommendations'),
                        'type'  => 'checkbox',
                    ),
                    array(
                        'id'      => 'global_products_number',
                        'title'   => __('Numbers of Products', 'woocommerce-product-recommendations'),
                        'type'    => 'number',
                        'default' => 12,
                    ),
                ),
            ),
            array(
                'id'          => 'disable_global_override',
                'label'       => 'Skip',
                'title'       => __('Skip Manual Selection', 'woocommerce-product-recommendations'),
                'type'        => 'checkbox',
                'description' => 'It will skip individual recommendations what you have done from product edit page using WPR setting panel. <br> It is helpful for a quick campaign. <strong>example:</strong> On your black Friday campaign, you want to temporary skip individual product specific recommendations. And recommend some specific categories of products. Just select the categories from the above setting and check this <strong>Skip</strong> checkbox.',
            ),
        );

        $setting_pages = array(
            array(
                'id'         => $this->get_settings_id(),
                'page_title' => 'WooCommerce Product Recommendations Settings',
                'menu_title' => 'WPR Settings',
                'slug'       => 'wpr-settings',
                'icon'       => 'dashicons-cart',
                'position'   => 60,

                'sections'   => array(
                    array(
                        'id'           => 'wpr-general-settings',
                        'tab_title'    => 'General',
                        'title'        => 'General Settings',
                        'descriptions' => 'General Setting of WooCommerce Products Recommendations',
                        'fields'       => $general_settings_fields,
                    ),

                    array(
                        'id'           => 'wpr-style-settings',
                        'tab_title'    => 'Style',
                        'title'        => 'Colors & Styles Settings',
                        'descriptions' => 'General Setting of WooCommerce Products Recommendations',
                        'fields'       => $style_settings,
                    ),

                    array(
                        'id'           => 'wpr-global-settings',
                        'tab_title'    => 'Global',
                        'title'        => 'Global Settings',
                        'descriptions' => 'Global Settings',
                        'fields'       => $global_settings_fields,
                    ),

                    array(
                        'id'           => 'wpr-documentation',
                        'tab_title'    => 'Tutorials',
                        'title'        => 'Tutorial & Documentation',
                        'type'         => 'article',
                        'descriptions' => 'Global Settings',
                        'template'     => $this->get_path('includes/tutorials.php'),
                    ),
                ),
            ),
        );

        return $setting_pages;
    }

    /**
     * Get enqueue script version
     *
     * @since      1.0.0
     * @return string version of script base on development or production
     */
    public function script_version() {
        if (WP_DEBUG) {
            return time();
        }
        return $this->plugin_version();
    }


    /**
     * Ger plugin version 
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
     * Get class instance.
     * @since      1.0.0
     * @return instance of base class.
     */
    public static function init($__FILE__) {
        if (is_null(self::$instance)) {
            self::$instance = new self($__FILE__);
        }

        return self::$instance;
    }
}
