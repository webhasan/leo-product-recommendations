<?php 
/**
 * The core plugin class
 *
 * @since      1.0.0
 * @author     Pluginsify
 */

 
class Pgfy_Woo_Product_Recommend {

	static protected $instance;
	static protected $name = 'WooCommerce Product Recommend';
	static protected $version = '1.0.0';

	/**
	 * Plugin root file, equivalent to __FILE__ of pluign root file.
	 *
	 * @var string
	 */
	static protected $__FILE__;

    /**
    * Class constructor
    * Run all plugin code
    * @since      1.0.0
     */
    private function __construct($__FILE__) {

		self::$__FILE__ = $__FILE__;

		register_activation_hook( self::$__FILE__, array( $this, 'on_activation' ) );
		register_deactivation_hook( self::$__FILE__, array( $this, 'on_deactivation' ) );
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
    }

    /**
	 * Action after plugin active.
	 * enable ajax add to cart and disabel redirec to cart page.
     * 
	 * @since      1.0.0
	 */
	public function on_activation() {
		if($this->has_pro_version() && class_exists( 'WooCommerce' )) {
			update_option('woocommerce_enable_ajax_add_to_cart', 'yes');
			update_option('woocommerce_cart_redirect_after_add', 'no');
		}
	}
	
	/**
	 * Check Pro version of the plugin already installed or not 
     * 
	 * @since      1.0.0
	 */
	public function has_pro_version() {
		return class_exists('Pgfy_Woo_Product_Recommend_Pro');
	}
    
    /**
	 * Action after plugin deactive
	 *
     * @since      1.0.0
	 * @return void
	 */
	public function on_deactivation() {
		
    }
    
    /**
     * Setup plugin once all other plugins are loaded.
     *
     * @since      1.0.0
     * @return void
     */
    public function on_plugins_loaded() {

		if(!$this->has_pro_version()){

			$this->load_textdomain();
			if ( ! $this->has_satisfied_dependencies() ) {
				add_action( 'admin_notices', array( $this, 'render_dependencies_notice' ) );
				return;
			}

			$this->includes();
			$this->hooks();

		}else {
			add_action( 'admin_init', array( $this, 'deactivate_self' ) );
			add_action( 'admin_notices', array( $this, 'render_has_pro_notice' ) );
		}
	}
	
    /**
	 * Load Localization files.
	 *
     * @since      1.0.0    
	 * @return void
	 */
    public function load_textdomain() {
        load_plugin_textdomain( 'woocommerce-product-recommend', false, dirname( plugin_basename( self::$__FILE__ ) ) . '/languages' );
    }
 
    
    /**
	 * Returns true if all dependencies for the plugin are loaded.
	 *
     * @since      1.0.0   
	 * @return bool
	 */
	protected function has_satisfied_dependencies() {
		$dependency_errors = $this->get_dependency_errors();
		return 0 === count( $dependency_errors );
    }
    
    /**
	 * Get an array of dependency error messages.
	 *
     * @since      1.0.0   
	 * @return array
	 */
	protected function get_dependency_errors() {
		$errors                      = array();
		$wordpress_version           = get_bloginfo( 'version' );
		$minimum_wordpress_version   = '5.0';
		$minimum_woocommerce_version = '3.2';
		$wordpress_minimum_met       = version_compare( $wordpress_version, $minimum_wordpress_version, '>=' );
		$woocommerce_minimum_met     = class_exists( 'WooCommerce' ) && version_compare( WC_VERSION, $minimum_woocommerce_version, '>=' );

		if ( ! $woocommerce_minimum_met ) {
			$errors[] = sprintf(
				__( 'The WooCommerce Product Recommond Pro plugin requires <a href="%1$s">WooCommerce</a> %2$s or greater to be installed and active.', 'woocommrece-product-recommend' ),
				'https://wordpress.org/plugins/woocommerce/',
				$minimum_woocommerce_version
			);
		}

		if ( ! $wordpress_minimum_met ) {
			$errors[] = sprintf(
				__( 'The WooCommerce Product Recommond Pro plugin requires <a href="%1$s">WordPress</a> %2$s or greater to be installed and active.', 'woocommrece-product-recommend' ),
				'https://wordpress.org/',
				$minimum_wordpress_version
			);
		}

		return $errors;
    }
    

     /**
	 * Deactive Plugin Itself
     * 
     * @since      1.0.0
	 */

	public function deactivate_self() {
		deactivate_plugins( plugin_basename( self::$__FILE__ ) );
		unset( $_GET['activate'] );
    }
    
    /**
     * Notify users of the plugin requirements.
     * 
     * @since      1.0.0
     */
	public function render_dependencies_notice() {
		$message = $this->get_dependency_errors();
		printf( '<div class="error"><p>%s</p></div>', implode( ' ', $message ) );
	}


 	/**
     * Nofication if try to install free version
	 * if already pro version activated
     * 
     * @since      1.0.0
     */

	public function render_has_pro_notice() {
		$message = __('Pro version already activated, no need free version.','woocommerce-product-recommend');
		printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', $message);
	}

	/**
     * Include addon features with this plugin
	 * 
     * @since      1.0.0
     */
	public function includes() {
		$this->deactivation_feedback();
	}

	/**
     * Plugin deactivation 
     * Include addon feature with this plugin
     * @since      1.0.0
     */
	public function deactivation_feedback() {

		if(!class_exists('Pgfy_Deactivation_Feedback')) {
			include_once($this->get_path('includes/feedbacks/deactivation-feedback/class-pgfy-deactivation-feedback.php'));
		}
		new Pgfy_Deactivation_Feedback($this->deactive_form_fields());
	}

	/**
     * Add all actions hook.
     * 
     * @since      1.0.0
     */
	public function hooks() {
		
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts')); // back-end scripts
		add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts')); // front-end scripts

		add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
		add_action('save_post', array($this, 'on_save_post'));
		add_action( 'wp_ajax_pr_fetch', array($this, 'fetch_post_meta'));
		add_action( 'wp_ajax_nopriv_pr_fetch', array($this, 'fetch_post_meta'));

		add_action( 'wp_ajax_pgfy_ajax_add_to_cart', array($this, 'ajax_add_to_cart'));
		add_action( 'wp_ajax_nopriv_pgfy_ajax_add_to_cart', array($this, 'ajax_add_to_cart'));

		add_action('after_setup_theme', array($this, 'include_templates')); // include modal template
	}

	/**
	 * Enqueue all admin scripts and styles
	 * 
	 * @since      1.0.0
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script('wpr-script', $this->get_url('assets/js/panel.js'), array('wp-element','wp-i18n','jquery'), false, true);
		wp_localize_script( 'wpr-script', 'ajax_url', admin_url( 'admin-ajax.php' ));
		wp_enqueue_style( 'wpr-panel', $this->get_url('assets/css/panel.css'));
	}

	/**
	 * Enqueue all front end scripts and styles
	 * 
	 * @since      1.0.0
	 */
	public function wp_enqueue_scripts() {
		wp_enqueue_script('wpr-modal', $this->get_url('assets/js/modal.js'), array('jquery'), false, true);

		if(is_product()) {
			wp_enqueue_script('wpr-ajax-add-to-cart', $this->get_url('assets/js/ajax-add-to-cart.js'), array('jquery','wp-i18n'), false, true);
			wp_localize_script( 'wpr-ajax-add-to-cart', 'pgfy_ajax', array(
				'url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('pgfy-add-to-cart')
			));
		}
		
		wp_enqueue_style( 'wpr-modal', $this->get_url('assets/css/modal.css'));
	}
	

	/**
	 * Add Meta Box
	 * 
	 * @since      1.0.0
	 */
	public function add_meta_boxes() {
		add_meta_box( 
			'plfy_prodcut_selection', 
			__('Recommended Products','woocommerce-product-recommend'), 
			array($this, 'product_selection'),
			array('product')
		);
	}

	/**
	 * Include Products Recommend Panel
	 * 
	 * @since      1.0.0
	 */

	public function product_selection($post) {
		include_once($this->get_path('includes/option-select-products.php'));
	}

	/**
	 * Plugin file URL
	 * 
	 * @since      1.0.0
	 * @return     URL link of file.
	 * @param      stirng File name with folder path.
	 */
	public function get_url($file = '') {
		return plugin_dir_url( self::$__FILE__ ) . $file;
	}

	/**
	 * Get file path from abspath
	 * 
	 * @since      1.0.0
	 * @param      stirng File name with folder path.
	 * @return     string Full path of file.
	 */
	public function get_path($file_path) {
		return plugin_dir_path( self::$__FILE__ ) . $file_path;
	}

	/**
	 * Get plugin slug
	 * 
	 * @since      1.0.0
	 * @return     string plugin slug
	 */
	public function get_slug() {
		return basename( self::$__FILE__, '.php');
	}

	/**
	 * Save post callback function
	 * 
	 * @since      1.0.0
	 */
	public function on_save_post($id) {
		$selected = isset($_POST['pgfy_pr_data']) ? $_POST['pgfy_pr_data'] : array();
		update_post_meta($id, 'pgfy_pr_data', $selected);
	}

	/**
	 * Fetch Product Meta and Recommend Products, etc 
	 * 
	 * @since      1.0.0
	 */
	public function fetch_post_meta() {
		// post ID 
		$post_id = $_POST['post_id'];

		// Get all prodcuts
		$post_data = get_posts(array(
			'post_type' 	=> 'product',
			'numberposts' 	=> -1,
			'exclude'     	=> array($post_id),
		));

		// map prodcut id , title, thumbnails and categoris
		$products = array_map(function($item) {
			$id = $item->ID;
			$title = $item->post_title;
			$thumbnail_image = get_the_post_thumbnail_url($id, array('100','100'));
	
			$categories = get_the_terms($id, 'product_cat');
	
			$categories = array_map(function($category) {
				return $category->name;
			}, $categories);
	
			return compact('id','title','thumbnail_image', 'categories');
		}, $post_data);

		// remove product itself from it list
		$products = array_filter($products, function($product) use($post_id) {
			return  $product['id'] != $post_id;
		});
	
		// Get product recommend data.
		$pr_data = get_post_meta($post_id,'pgfy_pr_data', true);

		// Get product recommend heading

		$heading = (!!$pr_data && isset($pr_data['heading'])) ? $pr_data['heading'] : '';

		// Get selected recommended products id
		$recommended_products_ids = (!!$pr_data && isset($pr_data['products'])) ? $pr_data['products'] : array();

		// Get selected prodcuts by id
		$selectedProducts = array_values(array_filter($products, function($product) use($recommended_products_ids) {
			return in_array($product['id'], $recommended_products_ids);
		}));

		wp_send_json(compact("products", "selectedProducts", "heading"));
	}


	/**
	 * Ajax call back to add to cart for singe product page
	 * 
	 * @since      1.0.0
	 */
	public function ajax_add_to_cart() {
		// wp_die($this->get_path('includes/class-pgfy-ajax-add-to-cart.php'));

		if(!class_exists('Pgfy_Ajax_Add_To_Cart')) {
			include($this->get_path('includes/class-pgfy-ajax-add-to-cart.php'));
		}

		if($_REQUEST['data'] && $_REQUEST['nonce'] && wp_verify_nonce($_REQUEST['nonce'], 'pgfy-add-to-cart')) {
			new Pgfy_Ajax_Add_To_Cart($_REQUEST['data']);
		}else {
			wp_send_json_error(array('message' => 'Bad request'), 400 );
		}
	}

	/**
	 * Include modal templates
     * @since      1.0.0
	 */
	public function include_templates() {
		// modal in shop / archives page
		if(apply_filters('wc_pr_show_in_product_archives', true)) {
			add_action('woocommerce_after_shop_loop_item', array($this, 'product_archive_modal'));
		}

		// modal in single product page
		if(apply_filters('wc_pr_show_in_singe_product', true)) {
			add_action('woocommerce_after_single_product_summary', array($this, 'product_single_modal'), 21);
		}
		
		// modal in WooCommerce Gutenberg products block
		if(apply_filters('wc_pr_show_in_gutenberg_product_block', true)) {
			add_filter('woocommerce_blocks_product_grid_item_html', array($this, 'product_gutenberg_block'), 10, 3);
		}
	}

	/**
	 * Add modal to archive / shop page prodcuts
     * @since      1.0.0
	 */
	public function product_archive_modal() {
		global $product;
		$product_id = $product->get_id();
		$pr_data = get_post_meta($product_id, 'pgfy_pr_data', true);
		$recommended_products_ids = (!!$pr_data && isset($pr_data['products'])) ? $pr_data['products'] : array();
		$modal_heading = (!!$pr_data && isset($pr_data['heading'])) ? $pr_data['heading'] : '';

		if(!empty( $recommended_products_ids )) {
			include($this->get_path('templates/template-modal.php'));
		}
	}

	/**
	 * Add modal to single product page
     * @since      1.0.0
	 */
	public function product_single_modal() {
		global $product;

		$product_id = $product->get_id();

		$pr_data = get_post_meta($product_id, 'pgfy_pr_data', true);
		$recommended_products_ids = (!!$pr_data && isset($pr_data['products'])) ? $pr_data['products'] : array();
		$modal_heading = (!!$pr_data && isset($pr_data['heading'])) ? $pr_data['heading'] : '';

		if(!empty($recommended_products_ids)) {
			include($this->get_path('templates/template-modal.php'));
		}
	}

	/**
	 * Add modal to Guterberg block product
     * @since      1.0.0
	 */
	public function product_gutenberg_block($html, $data, $product) {

		$product_id = $product->get_id();
		$pr_data = get_post_meta($product_id, 'pgfy_pr_data', true);
		$recommended_products_ids = (!!$pr_data && isset($pr_data['products'])) ? $pr_data['products'] : array();
		$modal_heading = (!!$pr_data && isset($pr_data['heading'])) ? $pr_data['heading'] : '';

		if(empty($recommended_products_ids)) 
			return $html;


		ob_start();
			include($this->get_path('templates/template-modal.php'));
		$modalHtml = ob_get_clean();
		$output = str_replace('</li>', '',$html);
		$output .= $modalHtml;
		$output .= "</li>";

		return $output;
	}


	/**
	 * Feedback Form data
	 * 
	 * @return array deactivation form field settings data
	 */
	public function deactive_form_fields() {

		$admin_email = $this->amdin_email();
		$form = array(
			
			'plugin_name' 		=> self::$name,
			'plugin_version' 	=> self::$version,
			'plugin_slug'		=> $this->get_slug(),
			'feedback_heading'  => 'Quick Feedback',
			'form_heading'      => 'May we have a little info about why you are deactivating?',
			'api_url' => 'http://localhost/feedback/wp-json/pluginsify/v1/deactivation-feedback',

			'fields' => array(
				array(
					'category' 		=> 'temporary_deactivation',
					'reason' 	  	=> __('It\'s a temporary deactivation.'),
					'instuction'  	=> '',
					'input_field' 	=> '',
					'placeholder' 	=> '',
					'input_default' => '',
				),

				array(
					'category' 		=> 'dont_understand',
					'reason' 	  	=> __('I couldn\'t understand how to make it work..'),
					'instuction'  	=> '<a href="#">Check instruciton and demo.</a>',
					'input_field' 	=> '',
					'placeholder' 	=> '',
					'input_default' => '',
				),

				array(
					'category' 		=> 'dont_need',
					'reason' 	  	=> __('Plugin works nice but longer need the plugin.'),
					'instuction'  	=> '<a href="#">Incourse us by giving nice feedback.</a>',
					'input_field' 	=> '',
					'placeholder' 	=> '',
					'input_default' => '',
				),

				array(
					'category' => 'need_help',
					'reason' 	  => __('I need some help to configure plugin.'),
					'instuction'  => 'Please provide your email address we will conact you soon.',
					'input_field' => 'email',
					'placeholder' => '',
					'input_default' => sanitize_email($admin_email)
				),

				array(
					'category' 		=> 'another_plugin',
					'reason' 	  	=> __('Found better plugin.'),
					'instuction'  	=> '',
					'input_field' 	=> 'text',
					'placeholder' 	=> 'Please share which plugin',
					'input_default' => ''
				),

				array(
					'category' 		=> 'feature_request',
					'reason' 	  	=> __('I need specific feature that you don\'t support.'),
					'instuction'  	=> 'Please let us know feature details we will try add it ASAP',
					'input_field' 	=> 'textarea',
					'placeholder' 	=> 'Require feature details',
					'input_default' => ''
				),

				array(
					'category' 		=> 'other',
					'reason' 	  	=> __('Other'),
					'instuction'  	=> '',
					'input_field' 	=> 'textarea',
					'placeholder' 	=> 'Please share the reason. We will try to fix / help.',
					'input_default' => ''
				),
			)
		);

		return $form;
	}


	/**
	 * Get admin email address
	 * 
	 * @return string current user email address
	 */
	public function amdin_email() {
		$admin_email =  wp_get_current_user();
		return ( 0 !== $admin_email->ID ) ? $admin_email->data->user_email : '';
	}


    /**
	 * Get class instance.
     * @since      1.0.0
	 * @return object Instance.
	 */
    public static function init($__FILE__) {
        if(is_null(self::$instance)) 
            self::$instance = new self($__FILE__);

        return self::$instance;
    }
}