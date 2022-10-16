<?php

    defined( 'ABSPATH' ) || exit;
    
    if ( ! class_exists( 'Leo_Product_Recommendations_Settings' ) ):
        
        class Leo_Product_Recommendations_Settings extends GetWooPlugins_Settings_Page {
            
            /**
             * Constructor.
             */
            public function __construct() {
                
                $this->notices();
                $this->hooks();
                parent::__construct();
                do_action( 'leo_product_recommendations_settings_loaded', $this );
            }
            
            public function get_id() {
                return 'lc_lpr_settings';
            }
            
            public function get_label() {
                return esc_html__( 'Variation Swatches', 'leo-product-recommendations' );
            }
            
            public function get_menu_name() {
                return esc_html__( 'LPR Settings', 'leo-product-recommendations' );
            }
            
            public function get_title() {
                return esc_html__( 'Leo Product Recommendations Settings', 'leo-product-recommendations' );
            }
            
            protected function hooks() {
                add_action( 'admin_footer', array( $this, 'modal_templates' ) );
                add_action( 'getwooplugins_sidebar', array( $this, 'sidebar' ) );
                add_filter( 'show_getwooplugins_save_button', array( $this, 'save_button' ), 10, 3 );
                add_filter( 'show_getwooplugins_sidebar', array( $this, 'save_button' ), 10, 3 );

                // include require scripts
                if($this->is_own_settings_page()) {
                    add_action('admin_enqueue_scripts', function() {
                        $lpr = leo_product_recommendations();

                        wp_enqueue_script( 'lpr-settings-script', $lpr->get_url('assets/js/settings.min.js'), array('jquery','wp-i18n'), filemtime($lpr->get_path('assets/js/settings.min.js')), true );
                        wp_enqueue_style('lpr-settings-style', $lpr->get_url('assets/css/settings.css'), array(), filemtime( $lpr->get_path('assets/css/settings.css')));
    
                        //css editor
                        $cm_settings['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/css'));
                        wp_localize_script('lpr-settings', 'lpr_css_editor', $cm_settings);
                        wp_enqueue_style('wp-codemirror');
                        wp_enqueue_script('wp-theme-plugin-editor');
                    });
                }

                // mapping old checkbox value "1" to yes 
                add_filter( "option_".$this->get_id(), function($value) {
                    $checkbox_fields = array(
                        'variable_add_to_cart', 
                        'show_go_check_out',
                        'show_close_icon',
                        'active_global_settings',
                        'global_on_sale',
                        'disable_global_override'
                    );

                    foreach($checkbox_fields as $field) {
                        if(isset($value[$field]) && $value[$field] === '1') {
                            $value[$field] = 'yes';
                        }else if(!empty($value) && !isset($value[$field])) {
                            $value[$field] = 'no';
                        }
                    }

                    return $value;
                }, 10, 2);  
            }
            
            public function save_button( $default, $current_tab, $current_section ) {
                if ( $current_tab === $this->get_id() && in_array( $current_section, array( 'tutorial', 'plugins', 'group' ) ) ) {
                    return false;
                }
                
                return $default;
            }
            
            public function sidebar( $current_tab ) {
                if ( $current_tab === $this->get_id() ) {
                    //include_once dirname( __FILE__ ) . '/html-settings-sidebar.php';
                }
            }
            
            public function modal_templates() {
                $this->template_shape_style();
                $this->template_default_to_button();
                $this->template_default_to_image();
                $this->template_clear_on_reselect();
                $this->template_hide_out_of_stock_variation();
                $this->template_clickable_out_of_stock_variation();
                $this->template_show_variation_stock_info();
                $this->template_display_limit();
                $this->template_archive_show_availability();
                $this->template_archive_swatches_position();
                $this->template_show_swatches_on_filter_widget();
                $this->template_enable_catalog_mode();
                $this->template_enable_single_variation_preview();
                $this->template_enable_large_size();
                $this->template_archive_align();
                $this->template_attribute_behavior();
                $this->template_enable_linkable_variation_url();
                $this->template_license();
                $this->template_show_on_archive();
                $this->template_archive_default_selected();
            }
            
            public function modal_support_links() {
                $links = array(
                    'button_url'  => 'https://getwooplugins.com/documentation/woocommerce-variation-swatches/',
                    'button_text' => esc_html__( 'See Documentation', 'leo-product-recommendations' ),
                    'link_url'    => 'https://getwooplugins.com/tickets/',
                    'link_text'   => esc_html__( 'Help &amp; Support', 'leo-product-recommendations' )
                );
                
                return $links;
            }
            
            public function modal_buy_links() {

                $links = array(
                    'button_url'   => 'https://leocoder.com/leo-product-recommendations-for-woocommerce/#pricing',
                    'button_text'  => esc_html__( 'Buy Now', 'leo-product-recommendations' ),
                    'button_class' => 'button-danger',
                    'link_url'     => 'https://leocoder.com/docs/woocommerce-product-recommendations/general-description/',
                    'link_text'    => esc_html__( 'See Documentation', 'leo-product-recommendations' )
                );
                
                return $links;
            }
            
            public function template_shape_style() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-01.webm' ) ) );
                //$this->modal_dialog( 'shape_style', esc_html__( 'Swatches Shape Style', 'leo-product-recommendations' ), $body, $this->modal_support_links() );
            }
            
            public function template_default_to_button() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-02.webm' ) ) );
                //$this->modal_dialog( 'default_to_button', esc_html__( 'Swatches Default To Button', 'leo-product-recommendations' ), $body, $this->modal_support_links() );
            }
            
            public function template_default_to_image() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-03.webm' ) ) );
                //$this->modal_dialog( 'default_to_image', esc_html__( 'Swatches Default To Image', 'leo-product-recommendations' ), $body, $this->modal_buy_links() );
            }
            
            public function template_clear_on_reselect() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-04.webm' ) ) );
                //$this->modal_dialog( 'clear_on_reselect', esc_html__( 'Swatches Clear on Reselect', 'leo-product-recommendations' ), $body, $this->modal_support_links() );
            }
            
            public function template_hide_out_of_stock_variation() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-05.webm' ) ) );
                //$this->modal_dialog( 'hide_out_of_stock_variation', esc_html__( 'Swatches Hide Out Of Stock', 'leo-product-recommendations' ), $body, $this->modal_buy_links() );
            }
            
            public function template_clickable_out_of_stock_variation() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-06.webm' ) ) );
                //$this->modal_dialog( 'clickable_out_of_stock_variation', esc_html__( 'Swatches Clickable Out Of Stock', 'leo-product-recommendations' ), $body, $this->modal_buy_links() );
            }
            
            public function template_show_variation_stock_info() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-07.webm' ) ) );
                //$this->modal_dialog( 'show_variation_stock_info', esc_html__( 'Swatches Show variation stock info.', 'leo-product-recommendations' ), $body, $this->modal_buy_links() );
            }
            
            public function template_display_limit() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-08.webm' ) ) );
                //$this->modal_dialog( 'display_limit', esc_html__( 'Swatches Attribute Display Limit', 'leo-product-recommendations' ), $body, $this->modal_buy_links() );
            }
            
            public function template_archive_show_availability() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-09.webm' ) ) );
                //$this->modal_dialog( 'archive_show_availability', esc_html__( 'Swatches Show Product Availability', 'leo-product-recommendations' ), $body, $this->modal_support_links() );
            }
            
            public function template_archive_swatches_position() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-10.webm' ) ) );
                //$this->modal_dialog( 'archive_swatches_position', esc_html__( 'Swatches Display Position', 'leo-product-recommendations' ), $body, $this->modal_buy_links() );
            }
            
            public function template_show_swatches_on_filter_widget() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-11.webm' ) ) );
                //$this->modal_dialog( 'show_swatches_on_filter_widget', esc_html__( 'Swatches Display On Widget', 'leo-product-recommendations' ), $body, $this->modal_buy_links() );
            }
            
            public function template_enable_catalog_mode() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-12.webm' ) ) );
                //$this->modal_dialog( 'enable_catalog_mode', esc_html__( 'Swatches Show as catalog mode', 'leo-product-recommendations' ), $body, $this->modal_buy_links() );
            }
            
            public function template_enable_single_variation_preview() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-13.webm' ) ) );
                //$this->modal_dialog( 'enable_single_variation_preview', esc_html__( 'Swatches Show variation preview', 'leo-product-recommendations' ), $body, $this->modal_buy_links() );
            }
            
            public function template_enable_large_size() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-14.webm' ) ) );
                //$this->modal_dialog( 'enable_large_size', esc_html__( 'Swatches Show variation preview', 'leo-product-recommendations' ), $body, $this->modal_buy_links() );
            }
            
            public function template_archive_align() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-15.webm' ) ) );
                //$this->modal_dialog( 'archive_align', esc_html__( 'Swatches Show variation preview', 'leo-product-recommendations' ), $body, $this->modal_buy_links() );
            }
            
            public function template_attribute_behavior() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-16.webm' ) ) );
                //$this->modal_dialog( 'attribute_behavior', esc_html__( 'Swatches Disabled Attribute style', 'leo-product-recommendations' ), $body, $this->modal_support_links() );
            }
            
            public function template_enable_linkable_variation_url() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-17.webm' ) ) );
                //$this->modal_dialog( 'enable_linkable_variation_url', esc_html__( 'Swatches Generate Sharable URL', 'leo-product-recommendations' ), $body, $this->modal_buy_links() );
            }
            
            public function template_license() {
                
                $links = array(
                    'button_url'  => 'https://leocoder.com/my-account/api-keys/',
                    'button_text' => esc_html__( 'Get license', 'leo-product-recommendations' ),
                    'link_url'    => 'https://leocoder.com/submit-ticket/',
                    'link_text'   => esc_html__( 'Help &amp; Support', 'leo-product-recommendations' )
                );
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-18.webm' ) ) );
                //$this->modal_dialog( 'license', esc_html__( 'Swatches License', 'leo-product-recommendations' ), $body, $links );
            }
            
            public function template_show_on_archive() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-19.webm' ) ) );
                // $this->modal_dialog( 'show_on_archive', esc_html__( 'Swatches On Archive Page', 'leo-product-recommendations' ), $body, $this->modal_buy_links() );
            }
            
            public function template_archive_default_selected() {
                
                // $body = sprintf( '<video preload="auto" autoplay loop muted playsinline src="%s"></video>', esc_url( woo_variation_swatches()->org_assets_url( '/preview-20.webm' ) ) );
                // $this->modal_dialog( 'archive_default_selected', esc_html__( 'Swatches Archive Default Selected', 'leo-product-recommendations' ), $body, $this->modal_buy_links() );
            }
            
            
            protected function notices() {
                // phpcs:disable WordPress.Security.NonceVerification.Recommended
                if ( $this->is_current_tab() && isset( $_GET[ 'reset' ] ) ) { // WPCS: input var okay, CSRF ok.
                    GetWooPlugins_Admin_Settings::add_message( __( 'Product Recommendations Settings Reset.', 'leo-product-recommendations' ) );
                }
                // phpcs:enable
            }
            
            public function output( $current_tab ) {
                global $current_section;
                
                if ( $current_tab === $this->get_id() && 'tutorial' === $current_section ) {
                    $this->tutorial_section( $current_section );
                } elseif ( $current_tab === $this->get_id() && 'group' === $current_section ) {
                    $this->group_section( $current_section );
                } else {
                    parent::output( $current_tab );
                }
            }
            
            public static function get_all_image_sizes() {
                
                $image_subsizes = wp_get_registered_image_subsizes();
                
                return apply_filters( 'woo_variation_swatches_get_all_image_sizes', array_reduce( array_keys( $image_subsizes ), function ( $carry, $item ) use ( $image_subsizes ) {
                    
                    $title  = ucwords( str_ireplace( array( '-', '_' ), ' ', $item ) );
                    $width  = $image_subsizes[ $item ][ 'width' ];
                    $height = $image_subsizes[ $item ][ 'height' ];
                    
                    $carry[ $item ] = sprintf( '%s (%d &times; %d)', $title, $width, $height );
                    
                    return $carry;
                },                                                                                array() ) );
            }
            
            public function plugins_tab( $label ) {
                return sprintf( '<span class="getwooplugins-recommended-plugins-tab dashicons dashicons-admin-plugins"></span> <span>%s</span>', $label );
            }
            
            /**
             * Settings Tabs
             */
            protected function get_own_sections() {
                $sections = array(
                    ''         => esc_html__( 'General', 'leo-product-recommendations' ),
                    'style_new'=> esc_html__( 'Styling', 'leo-product-recommendations' ),
                    'global'   => esc_html__( 'Global', 'leo-product-recommendations' ),
                    'license'  => array(
                        'name' => esc_html__( 'License', 'leo-product-recommendations' ),
                        'url'  => false
                    ),
                    'tutorial' => esc_html__( 'Tutorial', 'leo-product-recommendations' ),
                );
                
                if ( current_user_can( 'install_plugins' ) ) {
                    $sections[ 'plugins' ] = array(
                        'name' => $this->plugins_tab( esc_html__( 'Useful Free Plugins', 'leo-product-recommendations' ) ),
                        'url'  => self_admin_url( 'plugin-install.php?s=getwooplugins&tab=search&type=author' ),
                    );
                }
                
                return $sections;
            }
            
            public function tutorial_section( $current_section ) {
                ob_start();
                $settings = $this->get_settings( $current_section );
                //include_once dirname( __FILE__ ) . '/html-settings-tutorial.php';
                echo ob_get_clean();
            }
            
            public function group_section( $current_section ) {
                ob_start();
                $settings = $this->get_settings( $current_section );
                include_once dirname( __FILE__ ) . '/html-settings-group.php';
                echo ob_get_clean();
            }
            
            protected function get_settings_for_default_section() {
                
                $settings = array(
                    
                    array(
                        'id'    => 'general_options_title',
                        'type'  => 'title',
                        'title' => __( 'General options', 'leo-product-recommendations' ),
                    ),
                    
                    array(
                        'id'       => 'variable_add_to_cart',
                        'title'    => __( 'Variable Add To Cart', 'leo-product-recommendations' ),
                        'type'     => 'checkbox',
                        'desc'     => __( 'Show Add To Card for variable and group products.', 'leo-product-recommendations'),
                        'default'  => 'yes',
                    ),

                    array(
                        'id' => 'layout_type',
                        'title' => __('Layout Type', 'leo-product-recommendations'),
                        'type' => 'radio',
                        'options' => array(
                            'grid' => __('Grid', 'leo-product-recommendations'),
                            'slider' => __('Slider', 'leo-product-recommendations'),
                        ),
                        'default' => 'grid',
                        'is_pro'  => !$this->is_pro_activated(),
                    ),

                    array(
                        'type' => 'sectionend',
                        'id'   => 'general_options_title',
                    ),

                    array(
                        'id'    => 'layout_settings_title',
                        'type'  => 'title',
                        'title' => __( 'Layout Settings', 'leo-product-recommendations' ),
                        'desc'  => __('Settings for grid / slider layouts of recommended products', 'leo-product-recommendations'),
                    ),

                    array(
						'id' => 'grid_lg_items',
						'title' => __('Desktop Items Per Row', 'leo-product-recommendations'),
						'type' => 'number',
						'default' => 4,
                        'css' => 'width: 80px;',
                        'require' => array(array( 'input[name="lc_lpr_settings[layout_type]"]' => array( 'type' => 'equal', 'value' => array('grid')) )),
                        'custom_attributes' => array(
                            'min' => 2,
                            'max' => 5,
                        ),
					),
                    array(
						'id' => 'grid_md_items',
						'title' => __('Tablet Items Per Row', 'leo-product-recommendations'),
						'type' => 'number',
                        'css' => 'width: 80px;',
						'default' => 3,
                        'require' => array(array( 'input[name="lc_lpr_settings[layout_type]"]' => array( 'type' => 'equal', 'value' => array('grid')) )),
                        'custom_attributes' => array(
                            'min' => 2,
                            'max' => 5,
                        ),
					),

					array(
						'id' => 'grid_sm_items',
						'title' => __('Mobile Items Per Row', 'leo-product-recommendations'),
						'type' => 'number',
                        'css' => 'width: 80px;',
						'default' => 2,
                        'require' => array(array( 'input[name="lc_lpr_settings[layout_type]"]' => array( 'type' => 'equal', 'value' => array('grid')) )),
                        'custom_attributes' => array(
                            'min' => 1,
                            'max' => 3,
                        ),
					),
					array(
						'id' => 'grid_column_gap',
						'title' => __('Column Gap', 'leo-product-recommendations'),
						'type' => 'number',
                        'css' => 'width: 80px;',
						'suffix' => 'px',
						'default' => 20,
                        'require' => array(array( 'input[name="lc_lpr_settings[layout_type]"]' => array( 'type' => 'equal', 'value' => array('grid')) )),
                        'custom_attributes' => array(
                            'min' => 0,
                            'max' => 60,
                        ),
					),

                    array(
						'id' => 'is_autoplay',
						'title' => __('Auto Play', 'leo-product-recommendations'),
						'type' => 'checkbox',
						'default' => 'yes',
                        'desc' => 'Enable Slider Auto Play',
                        'require' => array(array( 'input[name="lc_lpr_settings[layout_type]"]' => array( 'type' => 'equal', 'value' => array('slider')) )),
					),

					array(
						'id' => 'autoplay_speed',
						'title' => __('Auto Play Speed', 'leo-product-recommendations'),
						'type' => 'number',
                        'css' => 'width: 80px;',
						'default' => 2000,
						'suffix' => 'ms',
                        'require' => array(array( 'input[name="lc_lpr_settings[layout_type]"]' => array( 'type' => 'equal', 'value' => array('slider')) )),
					),

					array(
						'id' => 'smart_speed',
						'title' => __('Smart Speed', 'leo-product-recommendations'),
						'type' => 'number',
                        'css' => 'width: 80px;',
						'default' => 1200,
						'suffix' => 'ms',
                        'require' => array(array( 'input[name="lc_lpr_settings[layout_type]"]' => array( 'type' => 'equal', 'value' => array('slider')) )),
					),

					array(
						'id' => 'is_loop',
						'title' => __('Loop', 'leo-product-recommendations'),
						'type' => 'checkbox',
						'default' => 'yes',
                        'desc' => 'Enable Slider Loop',
                        'require' => array(array( 'input[name="lc_lpr_settings[layout_type]"]' => array( 'type' => 'equal', 'value' => array('slider')) )),
					),

					array(
						'id' => 'slide_by',
						'title' => __('Slider By', 'leo-product-recommendations'),
						'type' => 'select',
						'default' => 1,
						'options' => array(
							'1' => __('1 Item', 'leo-product-recommendations'),
							'2' => __('2 Items', 'leo-product-recommendations'),
							'page' => __('Page', 'leo-product-recommendations'),
						),
                        'require' => array(array( 'input[name="lc_lpr_settings[layout_type]"]' => array( 'type' => 'equal', 'value' => array('slider')) )),
					),

					array(
						'id' => 'slider_lg_items',
						'title' => __('Desktop Items', 'leo-product-recommendations'),
						'type' => 'number',
                        'css' => 'width: 80px;',
						'default' => 4,
                        'require' => array(array( 'input[name="lc_lpr_settings[layout_type]"]' => array( 'type' => 'equal', 'value' => array('slider')) )),
                        'custom_attributes' => array(
                            'min' => 2,
                            'max' => 5,
                        ),
					),

					array(
						'id' => 'slider_md_items',
						'title' => __('Tablet Items', 'leo-product-recommendations'),
						'type' => 'number',
                        'css' => 'width: 80px;',
						'default' => 3,
                        'require' => array(array( 'input[name="lc_lpr_settings[layout_type]"]' => array( 'type' => 'equal', 'value' => array('slider')) )),
                        'custom_attributes' => array(
                            'min' => 2,
                            'max' => 5,
                        ),
					),

					array(
						'id' => 'slider_sm_items',
						'title' => __('Mobile Items', 'leo-product-recommendations'),
						'type' => 'number',
                        'css' => 'width: 80px;',
						'default' => 2,
                        'require' => array(array( 'input[name="lc_lpr_settings[layout_type]"]' => array( 'type' => 'equal', 'value' => array('slider')) )),
                        'custom_attributes' => array(
                            'min' => 1,
                            'max' => 3,
                        ),
					),
					array(
						'id' => 'slider_column_gap',
						'title' => __('Column Gap', 'leo-product-recommendations'),
						'type' => 'number',
                        'css' => 'width: 80px;',
						'suffix' => 'px',
						'default' => 20,
                        'require' => array(array( 'input[name="lc_lpr_settings[layout_type]"]' => array( 'type' => 'equal', 'value' => array('slider')) )),
                        'custom_attributes' => array(
                            'min' => 0,
                            'max' => 60,
                        ),
					),
                    
                    array(
                        'type' => 'sectionend',
                        'id'   => 'layout_settings_title',
                    ),

                    array(
                        'id'    => 'popup_size_heading',
                        'type'  => 'title',
                        'title' => __( 'Popup Size', 'leo-product-recommendations' ),
                        'desc'  => __('Size of the recommendations popup modal.', 'leo-product-recommendations'),
                    ),

                    array(
						'id' => 'popup_lg_size',
						'title' => __('Desktop Size', 'leo-product-recommendations'),
						'type' => 'number',
						'suffix' => 'px',
						'default' => 1000,
                        'css' => 'width: 70px;',
                        'is_pro'  => !$this->is_pro_activated(),
					),
					array(
						'id' => 'popup_md_size',
						'title' => __('Tablet Size', 'leo-product-recommendations'),
						'type' => 'number',
						'suffix' => 'px',
						'default' => 720,
                        'css' => 'width: 70px;',
                        'is_pro'  => !$this->is_pro_activated(),
					),
					array(
						'id' => 'popup_sm_size',
						'title' => __('Mobile Size', 'leo-product-recommendations'),
						'type' => 'number',
						'suffix' => 'px',
						'default' => 600,
                        'css' => 'width: 70px;',
                        'is_pro'  => !$this->is_pro_activated(),
                    ),

                    array(
                        'type' => 'sectionend',
                        'id'   => 'popup_size_heading',
                    ),

                    array(
                        'id'    => 'button_visibility_title',
                        'type'  => 'title',
                        'title' => __( 'Button Visibility', 'leo-product-recommendations' ),
                        'desc'  => __('Show hide button of the popup heading.', 'leo-product-recommendations'),
                    ),

                    array(
						'id' => 'show_continue_shopping',
						'title' => __('Continue Shopping Button', 'leo-product-recommendations'),
						'type' => 'checkbox',
						'default' => 'yes',
                        'desc' => 'Show Continue Shopping Button',
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'show_go_check_out',
						'title' => __('Go Checkout Button', 'leo-product-recommendations'),
						'type' => 'checkbox',
						'default' => 'yes',
                        'desc' => 'Show Go Checkout Button',
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'show_close_icon',
						'title' => __('Popup Close Icon', 'leo-product-recommendations'),
						'type' => 'checkbox',
                        'desc' => 'Show Popup Close Icon',
                        'is_pro'  => !$this->is_pro_activated(),
					),

                    array(
                        'type' => 'sectionend',
                        'id'   => 'button_visibility_title',
                    ),
                
                );
                
                return $settings;
            }
            
            
            protected function get_settings_for_style_new_section() {
                
                $settings = array(
                    array(
                        'id'    => 'popup_color_settings_title',
                        'type'  => 'title',
                        'title' => esc_html__( 'Popup Color Settings', 'leo-product-recommendations' ),
                    ),
                    
                    array(
						'id' => 'modal_bg_color',
						'title' => __('Modal Background', 'leo-product-recommendations'),
						'type' => 'color',
						'default' => '#fff',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'notification_bar_bg',
						'title' => __('Notification Bar Background', 'leo-product-recommendations'),
						'type' => 'color',
						'default' => '#f5f5f5',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'notification_icon_color',
						'title' => __('Notification Icon Color', 'leo-product-recommendations'),
						'type' => 'color',
						'default' => '#46C28E',
                        'css' => 'width: 6em;',
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'notification_text_color',
						'title' => __('Notification Text Color', 'leo-product-recommendations'),
						'type' => 'color',
						'default' => '#555555',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'view_cart_color',
						'title' => __('View Cart Color', 'leo-product-recommendations'),
						'type' => 'color',
						'default' => '#46C28E',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'continue_shopping_bg',
						'title' => __('Continue Shopping Background', 'leo-product-recommendations'),
						'type' => 'color',
						'default' => '#4cc491',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),
					array(
						'id' => 'continue_shopping_hover_bg',
						'title' => __('Continue Shopping Hover Bg', 'leo-product-recommendations'),
						'type' => 'color',
						'default' => '#35a073',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'checkout_bg',
						'title' => __('Checkout Background', 'leo-product-recommendations'),
						'type' => 'color',
						'default' => '#4cc491',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'checkout_hover_bg',
						'title' => __('Checkout Hover Background', 'leo-product-recommendations'),
						'type' => 'color',
						'default' => '#35a073',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'cart_color',
						'title' => __('Cart Color', 'leo-product-recommendations'),
						'type' => 'color',
						'default' => '#35a073',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'modal_close_icon_color',
						'title' => __('Popup Close Background', 'leo-product-recommendations'),
						'type' => 'color',
						'default' => '#46C28E',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'heading_text_color',
						'title' => __('Heading Text Color', 'leo-product-recommendations'),
						'type' => 'color',
						'default' => '#555555',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'heading_background_color',
						'title' => __('Heading Background Color', 'leo-product-recommendations'),
						'type' => 'color',
						'default' => '#ffffff',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'heading_border_color',
						'title' => __('Heading Border Color', 'leo-product-recommendations'),
						'type' => 'color',
						'default' => '#f1f1f1',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'slider_nav_color',
						'title' => __('Slider Nav Color', 'leo-product-recommendations'),
						'type' => 'color',
						'default' => '#46C28E',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'overlay_color',
						'title' => __('Body Overlay Color', 'leo-product-recommendations'),
						'type' => 'color',
						'default' => 'rgba(0,0,0,.5)',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),
                    
                    array(
                        'type' => 'sectionend',
                        'id'   => 'popup_color_settings_title',
                    ),

                    array(
                        'id'    => 'popup_products_color_title',
                        'type'  => 'title',
                        'title' =>  __( 'Recommended Products Color Settings', 'leo-product-recommendations' ),
                        'desc'  => __( 'The default colors are inherited colors of the theme colors', 'leo-product-recommendations' ),
                    ),

                    array(
						'id' => 'product_title_color',
						'title' => __('Product Title Color', 'leo-product-recommendations'),
						'type' => 'color',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'product_title_hove_color',
						'title' => __('Hover Product Title Color', 'leo-product-recommendations'),
						'type' => 'color',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'price_color',
						'title' => __('Price Color', 'leo-product-recommendations'),
						'type' => 'color',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'offer_price_color',
						'title' => __('Offer Price Color', 'leo-product-recommendations'),
						'type' => 'color',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'button_text_color',
						'title' => __('Button Text Color', 'leo-product-recommendations'),
						'type' => 'color',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'hover_button_text_color',
						'title' => __('Hover Button Text Color', 'leo-product-recommendations'),
						'type' => 'color',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'button_background_color',
						'title' => __('Button Background', 'leo-product-recommendations'),
						'type' => 'color',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'hover_button_background',
						'title' => __('Hover Button Background', 'leo-product-recommendations'),
						'type' => 'color',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'on_sale_text_color',
						'title' => __('On-sale Badge Text Color', 'leo-product-recommendations'),
						'type' => 'color',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'on_sale_background',
						'title' => __('On-sale Background Color', 'leo-product-recommendations'),
						'type' => 'color',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

					array(
						'id' => 'product_border_color',
						'title' => __('Product Border Color', 'leo-product-recommendations'),
						'type' => 'color',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),
					array(
						'id' => 'product_background_color',
						'title' => __('Product Background Color', 'leo-product-recommendations'),
						'type' => 'color',
                        'css' => 'width: 6em;',
                        'custom_attributes' => array(
                            'data-alpha-enabled' => 'true'
                        ),
                        'is_pro'  => !$this->is_pro_activated(),
					),

                    array(
                        'type' => 'sectionend',
                        'id'   => 'popup_products_color_title',
                    ),

                    array(
                        'id'    => 'custom_css_title',
                        'type'  => 'title',
                        'title' =>  __( 'Custom CSS', 'leo-product-recommendations' ),
                    ),

                    array(
                        'id' => 'custom_style',
                        'title' => __('Custom CSS', 'leo-product-recommendations'),
                        'type' => 'textarea',
                        'desc' => __('Write custom css to change style of modal.', 'leo-product-recommendations'),
                    ),

                    array(
                        'type' => 'sectionend',
                        'id'   => 'custom_css_title',
                    ),
                
                );
                
                return $settings;
            }

            protected function get_settings_for_global_section() {

                $settings = array(
                    array(
                        'id'    => 'global_heading_title',
                        'type'  => 'title',
                        'title' => esc_html__( 'Global Popup Heading', 'leo-product-recommendations' ),
                        'desc'  => 'Global heading will be used for globally selected recommended products heading and fallback heading.',
                    ),

                    array(
                        'id'        => 'heading_type',
                        'type'      => 'radio',
                        'default'   => 'default_heading',
                        'title'     => __( 'Global Heading Type', 'leo-product-recommendations' ),
                        'desc'      => __( 'Choose whether you like a single line heading or a descriptive heading.', 'leo-product-recommendations' ),
                        'options'      => array(
                            'default_heading' => __( 'Heading', 'leo-product-recommendations' ),
                            'default_heading_description' => __( 'Heading & Description', 'leo-product-recommendations' ),
                        ),
                    ),
                    
                    array(
                        'id'      => 'default_heading',
                        'type'    => 'text',
                        'title'   => __( 'Single Line Heading', 'leo-product-recommendations' ),
                        'desc'    => __( 'Write single line heading. Use pattern %title% for the product title. And [item, items] for singular and plural of item type.', 'leo-product-recommendations' ),
                        'default' => 'You may purchase following [product, products] with the %title%',
                        'require' => array(array( 'input[name="lc_lpr_settings[heading_type]"]' => array( 'type' => 'equal', 'value' => array('default_heading')) )),
                    ),
                    
                    array(
                        'id'      => 'default_heading_description',
                        'type'    => 'textarea',
                        'title'   => __( 'Descriptive Heading', 'leo-product-recommendations' ),
                        'desc'    => __( 'Write descriptive heading.', 'leo-product-recommendations' ),
                        'require' => array(array( 'input[name="lc_lpr_settings[heading_type]"]' => array( 'type' => 'equal', 'value' => array('default_heading_description')) )),
                    ),

                    array(
                        'type' => 'sectionend',
                        'id'   => 'global_heading_title',
                    ),

                    array(
                        'id'    => 'global_recommendations_title',
                        'type'  => 'title',
                        'title' => __( 'Global Product Recommendation', 'leo-product-recommendations' ),
                        'desc'  => __('If there are no recommendations available for certain or several products (if you do not configure from the woo-commerce product editor), the global setting will work for those products as a recovery. This setting also helps if you like mass recommendations arranged for all stores instead of different configurations for each product.', 'leo-product-recommendations')
                    ),

                    array(
                        'id'     => 'active_global_settings',
                        'type'   => 'checkbox',
                        'title'  => __( 'Global Recommendations', 'leo-product-recommendations' ),
                        'default' => 'yes',
                        'desc'  => __('Enable global recommendations','leo-product-recommendations')
                    ),

                    array(
                        'id'    => 'global_categories',
                        'type' => 'radio',
                        'title' => __( 'Category Type', 'leo-product-recommendations' ),
                        'default' => 'same_categories',
                        'desc'  => __('Show products recommendations form same category or manually chooses from particular categories', 'leo-product-recommendations'),
                        'options'      => array(
                            'same_categories' => __( 'Product Related Category', 'leo-product-recommendations' ),
                            'manual_categories' => __( 'Choose Categories', 'leo-product-recommendations' ),
                        ),
                        'require' => $this->normalize_required_attribute( array( 'active_global_settings' => array( 'type' => '!empty' ) ) ),
                    ),

                    array(
                        'id'    => 'global_custom_categories',
                        'type'     => 'multiselect',
                        'multiselect' => 'true',
                        'title' => __( 'Choose Categories', 'leo-product-recommendations' ),
                        'desc'  => __('Show products recommendations form same category or manually chooses from particular categories','leo-product-recommendations'),
                        'options'    => $this->get_product_categories(),
                        'placeholder' => __( 'All Categories', 'leo-product-recommendations' ),
                        'require' => array(array( 'input[name="lc_lpr_settings[global_categories]"]' => array( 'type' => 'equal', 'value' => array('manual_categories')) )) ,
                    ),

                    array(
                        'id'    => 'global_tags',
                        'type'     => 'multiselect',
                        'size' => 'tiny',
                        'multiselect' => 'true',
                        'title' => __( 'Choose Tags', 'leo-product-recommendations' ),
                        'desc'  => __('Show products recommendations form same category or manually chooses from particular categories', 'leo-product-recommendations'),
                        'placeholder' => __( 'All Tags', 'leo-product-recommendations' ),
                        'options'    => $this->get_product_tags(),
                        'require' => $this->normalize_required_attribute( array( 'active_global_settings' => array( 'type' => '!empty' ) ) ),
                    ),

                    array(
                        'id' => 'global_filtering',
						'title' => __('Products Filtering', 'leo-product-recommendations'),
						'type' 	=> 'select',
                        'default' => 'rand',
						'options' => array(
							'rand' 		 => __('Random Products', 'leo-product-recommendations'),
							'newest' 	 => __('Newest Products', 'leo-product-recommendations'),
							'oldest' 	 => __('Oldest Products', 'leo-product-recommendations'),
							'lowprice' 	 => __('Low Price Products', 'leo-product-recommendations'),
							'highprice'  => __('High Price Products', 'leo-product-recommendations'),
							'popularity' => __('Best Selling Products', 'leo-product-recommendations'),
							'rating' 	 => __('Top Rated Products', 'leo-product-recommendations'),
						),
                        'require' => $this->normalize_required_attribute( array( 'active_global_settings' => array( 'type' => '!empty' ) ) ),
                    ),
                    array(
						'id' 	=> 'global_on_sale',
						'title' => __('On-Sale Only', 'leo-product-recommendations'),
						'type' 	=> 'checkbox',
                        'desc' => __('Only show discount products','leo-product-recommendations'),
                        'require' => $this->normalize_required_attribute( array( 'active_global_settings' => array( 'type' => '!empty' ) ) ),
					),

                    array(
						'id' 	  => 'global_products_number',
						'title'   => __('Numbers of Products', 'leo-product-recommendations'),
						'type' 	  => 'number',
						'default' => 12,
                        'desc' => __('How many product to show in popup','leo-product-recommendations'),
                        'require' => $this->normalize_required_attribute( array( 'active_global_settings' => array( 'type' => '!empty' ) ) ),
					),
                    array(
                        'id' => 'disable_global_override',
                        'label' => __('Skip', 'leo-product-recommendations'),
                        'title' => __('Skip Manual Selection', 'leo-product-recommendations'),
                        'type' => 'checkbox',
                        'desc' => __('Force Global Selection <br> <br>It will ignore individual recommendations of what you have done from the Woo-Commerce Edit Product page using the WPR configuration panel. <br>
                        It is helpful for a quick campaign. For example: In your Black Friday campaign, you want to temporarily skip individual product-specific recommendations. And recommend certain or several categories of products. Simply select the categories of the previous configuration from above and check this <strong>Skip</strong> check box.','leo-product-recommendations'),
                        'require' => $this->normalize_required_attribute( array( 'active_global_settings' => array( 'type' => '!empty' ) ) ),
                    ),

                    array(
                        'type' => 'sectionend',
                        'id'   => 'global_recommendations_title',
                    ),
                );

                return $settings;
            }

            /**
             * Get all categories of products.
             * 
             * @return array of product categories.
             */
            protected function get_product_categories() {
                $product_cats = get_terms(array(
                    'taxonomy'   => "product_cat",
                    'orderby'    => 'name',
                    'hide_empty' => false
                ));

                $product_cats = array_reduce($product_cats, function($collection, $category) {
                     $collection[esc_attr( $category->term_id )] = esc_html( $category->name );
                     return $collection;
                }, array());

                return $product_cats;
            }

            /**
             * Get all tags of products.
             * 
             * @return array of product tags.
             */
            protected function get_product_tags() {
                $product_tags = get_terms(array(
                    'taxonomy'   => "product_tag",
                    'orderby'    => 'name',
                    'hide_empty' => false
                ));

                $product_tags = array_reduce($product_tags, function($collection, $tag) {
                     $collection[esc_attr( $tag->term_id )] = esc_html( $tag->name );
                     return $collection;
                }, array());

                return $product_tags;
            }

            /**
             * Check current page is leo product recommendations settings page. 
             * @since 2.2.0
             * @return bool of current is leo products recommendations page or not. 
             */
            function is_own_settings_page() {
                $page = !empty($_GET['page']) ? sanitize_key($_GET['page']) : null;
                $tab = !empty($_GET['tab']) ? sanitize_key($_GET['tab']) : null;

                if($tab === 'lc_lpr_settings' || (!$tab && $page === 'getwooplugins-settings')) {
                    return true;
                }
                return false;
            }

            /**
             * Check is pro version of the plugin is activated
             * @since 2.2.0
             * @return bool 
             */
            public function is_pro_activated() {
                $lpr = leo_product_recommendations();
                return $lpr->is_pro_activated();
            } 
        }
    endif;