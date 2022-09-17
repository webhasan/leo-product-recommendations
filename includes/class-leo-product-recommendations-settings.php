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
                return 'lpr_settings_test';
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
                        'id'    => 'general_options',
                        'type'  => 'title',
                        'title' => esc_html__( 'General options', 'leo-product-recommendations' ),
                        'desc'  => 'Default will be used as a fallback heading for undefined heading & Global Setting recommendations heading.',
                    ),
                    
                    array(
                        'id'       => 'shape_style',
                        'title'    => esc_html__( 'Shape Style', 'leo-product-recommendations' ),
                        'type'     => 'radio',
                        'desc'     => esc_html__( 'This controls which shape style used by default.', 'leo-product-recommendations' ),
                        'desc_tip' => true,
                        'default'  => 'squared',
                        /*'is_pro'       => true,
                        'is_new'       => true,*/
                        
                        'options'      => array(
                            'rounded' => esc_html__( 'Rounded Shape', 'leo-product-recommendations' ),
                            'squared' => esc_html__( 'Squared Shape', 'leo-product-recommendations' ),
                        ),
                        'help_preview' => true,
                    ),
                    
                    array(
                        'id'           => 'default_to_button',
                        'title'        => esc_html__( 'Dropdowns to Button', 'leo-product-recommendations' ),
                        'desc'         => esc_html__( 'Convert default dropdowns to button.', 'leo-product-recommendations' ),
                        'default'      => 'yes',
                        'type'         => 'checkbox',
                        'help_preview' => true,
                    ),
                    
                    array(
                        'id'      => 'default_to_image',
                        'type'    => 'checkbox',
                        'title'   => esc_html__( 'Dropdowns to Image', 'leo-product-recommendations' ),
                        'desc'    => esc_html__( 'Convert default dropdowns to image type if variation has an image.', 'leo-product-recommendations' ),
                        'default' => 'yes',
                        'is_pro'  => true,
                    ),
                    
                    array(
                        'type' => 'sectionend',
                        'id'   => 'general_options',
                    ),
                
                );
                
                return $settings;
            }
            
            
            protected function get_settings_for_style_new_section() {
                
                $settings = array(
                    
                    // Start swatches tick and cross coloring
                    array(
                        'id'    => 'style_icons_options',
                        'type'  => 'title',
                        'title' => esc_html__( 'Swatches indicator', 'leo-product-recommendations' ),
                        'desc'  => esc_html__( 'Change swatches indicator color', 'leo-product-recommendations' ),
                    ),
                    
                    array(
                        'id'                => 'tick_color',
                        'type'              => 'color',
                        'title'             => esc_html__( 'Tick Color', 'leo-product-recommendations' ),
                        'desc'              => esc_html__( 'Swatches Selected tick color. Default is: #ffffff', 'leo-product-recommendations' ),
                        'css'               => 'width: 6em;',
                        'default'           => '#ffffff',
                        // 'is_new'            => true,
                        'custom_attributes' => array(//    'data-alpha-enabled' => 'true'
                        )
                    ),
                    
                    array(
                        'id'                => 'cross_color',
                        'type'              => 'color',
                        'title'             => esc_html__( 'Cross Color', 'leo-product-recommendations' ),
                        'desc'              => esc_html__( 'Swatches cross color. Default is: #ff0000', 'leo-product-recommendations' ),
                        'css'               => 'width: 6em;',
                        'default'           => '#ff0000',
                        //'is_new'            => true,
                        'custom_attributes' => array(//    'data-alpha-enabled' => 'true'
                        )
                    ),
                    
                    array(
                        'type' => 'sectionend',
                        'id'   => 'style_icons_options',
                    ),
                    
                    // Start single page swatches style
                    array(
                        'id'    => 'single_style_options',
                        'type'  => 'title',
                        'title' => esc_html__( 'Product Page Swatches Size', 'leo-product-recommendations' ),
                        'desc'  => esc_html__( 'Change swatches style on product page', 'leo-product-recommendations' ),
                    ),
                    
                    array(
                        'id'                => 'width',
                        'type'              => 'number',
                        'title'             => esc_html__( 'Width', 'leo-product-recommendations' ),
                        'desc'              => esc_html__( 'Single product variation item width. Default is: 30', 'leo-product-recommendations' ),
                        'css'               => 'width: 50px;',
                        'default'           => '30',
                        'suffix'            => 'px',
                        'custom_attributes' => array(
                            'min'  => 10,
                            'max'  => 200,
                            'step' => 5,
                        ),
                    ),
                    
                    array(
                        'id'                => 'height',
                        'type'              => 'number',
                        'title'             => esc_html__( 'Height', 'leo-product-recommendations' ),
                        'desc'              => esc_html__( 'Single product variation item height. Default is: 30', 'leo-product-recommendations' ),
                        'css'               => 'width: 50px;',
                        'default'           => 30,
                        'suffix'            => 'px',
                        'custom_attributes' => array(
                            'min'  => 10,
                            'max'  => 200,
                            'step' => 5,
                        ),
                    ),
                    
                    array(
                        'id'                => 'single_font_size',
                        'type'              => 'number',
                        'title'             => esc_html__( 'Font Size', 'leo-product-recommendations' ),
                        'desc'              => esc_html__( 'Single product variation item font size. Default is: 16', 'leo-product-recommendations' ),
                        'css'               => 'width: 50px;',
                        'default'           => 16,
                        'suffix'            => 'px',
                        'custom_attributes' => array(
                            'min'  => 8,
                            'max'  => 48,
                            'step' => 2,
                        ),
                    ),
                    
                    array(
                        'type' => 'sectionend',
                        'id'   => 'single_style_options',
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
                        'title'     => esc_html__( 'Global Heading Type', 'leo-product-recommendations' ),
                        'desc'      => esc_html__( 'Choose whether you like a single line heading or a descriptive heading.', 'leo-product-recommendations' ),
                        'options'      => array(
                            'default_heading' => esc_html__( 'Heading', 'leo-product-recommendations' ),
                            'default_heading_description' => esc_html__( 'Heading & Description', 'leo-product-recommendations' ),
                        ),
                    ),
                    
                    array(
                        'id'      => 'default_heading',
                        'type'    => 'text',
                        'title'   => esc_html__( 'Single Line Heading', 'leo-product-recommendations' ),
                        'desc'    => esc_html__( 'Write single line heading. Use pattern %title% for the product title. And [item, items] for singular and plural of item type.', 'leo-product-recommendations' ),
                        'default' => 'You may purchase following [product, products] with the %title%',
                    ),

                    array(
                        'id'      => 'default_heading_description',
                        'type'    => 'textarea',
                        'title'   => esc_html__( 'Descriptive Heading', 'leo-product-recommendations' ),
                        'desc'    => esc_html__( 'Write descriptive heading.', 'leo-product-recommendations' ),
                    ),

                    array(
                        'type' => 'sectionend',
                        'id'   => 'global_heading_title',
                    ),


                    array(
                        'id'    => 'global_recommendations_title',
                        'type'  => 'title',
                        'title' => esc_html__( 'Global Product Recommendation', 'leo-product-recommendations' ),
                        'desc'  => 'If there are no recommendations available for certain or several products (if you do not configure from the woo-commerce product editor), the global setting will work for those products as a recovery. This setting also helps if you like mass recommendations arranged for all stores instead of different configurations for each product.',
                    ),

                    array(
                        'id'     => 'active_global_settings',
                        'type'   => 'checkbox',
                        'title'  => esc_html__( 'Global Recommendations', 'leo-product-recommendations' ),
                        'default' => 'yes',
                        'desc'  => 'Enable global recommendations'
                    ),

                    array(
                        'id'    => 'global_categories',
                        'type' => 'radio',
                        'title' => esc_html__( 'Category Type', 'leo-product-recommendations' ),
                        'default' => 'same_categories',
                        'desc'  => 'Show products recommendations form same category or manually chooses from particular categories',
                        'options'      => array(
                            'same_categories' => esc_html__( 'Product Related Category', 'leo-product-recommendations' ),
                            'manual_categories' => esc_html__( 'Choose Categories', 'leo-product-recommendations' ),
                        ),
                        'require' => $this->normalize_required_attribute( array( 'active_global_settings' => array( 'type' => '!empty' ) ) ),
                    ),

                    array(
                        'id'    => 'global_custom_categories',
                        'type'     => 'multiselect',
                        'multiselect' => 'true',
                        'title' => esc_html__( 'Choose Categories', 'leo-product-recommendations' ),
                        'desc'  => 'Show products recommendations form same category or manually chooses from particular categories',
                        'options'    => $this->get_product_categories(),
                        'require' => array(array( 'input[name="lpr_settings_test[global_categories]"]' => array( 'type' => 'equal', 'value' => array('manual_categories')) )) ,
                        'placeholder' => esc_html__( 'All Categories', 'leo-product-recommendations' ),
                    ),

                    array(
                        'id'    => 'global_tags',
                        'type'     => 'multiselect',
                        'size' => 'tiny',
                        'multiselect' => 'true',
                        'title' => esc_html__( 'Choose Tags', 'leo-product-recommendations' ),
                        'desc'  => 'Show products recommendations form same category or manually chooses from particular categories',
                        'placeholder' => esc_html__( 'All Tags', 'leo-product-recommendations' ),
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
                        'desc' => 'Only show discount products',
                        'require' => $this->normalize_required_attribute( array( 'active_global_settings' => array( 'type' => '!empty' ) ) ),
					),

                    array(
						'id' 	  => 'global_products_number',
						'title'   => __('Numbers of Products', 'leo-product-recommendations'),
						'type' 	  => 'number',
						'default' => 12,
                        'desc' => 'How many product to show in popup',
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
            
        }
    endif;