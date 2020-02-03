<?php
/**
 * Deactivation Feedback
 * Show feedback form to reason of deactivation plugin
 * @since      1.0.0
 * @author     Md Hasanuzzaman <webhasan24@gmail.com>
 */

class Pgfy_Deactivation_Feedback {
    protected $settings;
    protected static $has_scripts = false;
    
    public function __construct($settings) {
        $this->settings = $settings;
        $this->init_feedback_form();
        $this->submit_feedback();
    }


    protected function init_feedback_form() {
        add_action('current_screen', function() {
			$current_screen = get_current_screen();

			if($current_screen && in_array( $current_screen->id, array( 'plugins', 'plugins-network' ), true )) {
                $this->modal_scripts();
                add_action('admin_footer', array($this, 'add_modal'));
			}
		});
    }

    /**
     * If used deactivation feedback  in multipule plugins 
     * scripts should load only one time.
     *
     * @return void
     */
    public function modal_scripts() {
        
        if(!self::$has_scripts) {

            add_action('admin_enqueue_scripts', function() {
                wp_enqueue_script('pgfy-deactivation-feedback-script', $this->url('script.js'), array('jquery','wp-i18n'), false, true);
                wp_localize_script( 'pgfy-deactivation-feedback-script', 'ajax_url', admin_url( 'admin-ajax.php'));
                wp_localize_script( 'pgfy-deactivation-feedback-script', 'security', wp_create_nonce('valid-feedback-submit'));
                wp_enqueue_style( 'pgfy-deactivation-feedback-style', $this->url('style.css'));
            });

            self::$has_scripts = true;
        }
    }

    public function add_modal() {
        echo $this->modal();
    }

    public function modal() {
        $modal_id = 'pgfy-feedback-modal-'.$this->settings['plugin_slug'];
        $form_id = 'feedback-form-'.$this->settings['plugin_slug'];
        $modal_html = '';

        $modal_html .= '<div class="pgfy-feedback-modal" id="'.$modal_id.'">';
        $modal_html .= ' <div class="pgfy-feedback-modal-background"></div>';
        $modal_html .=  '<div class="pgfy-feedback-modal-card">';

            $modal_html .=  '<header class="pgfy-feedback-modal-card-head">';

                if($this->settings['feedback_heading']) {
                    $modal_html .=  '<p class="pgfy-feedback-modal-card-title">'.$this->settings['feedback_heading'].'</p>';  
                    $modal_html .=  '<button class="pgfy-feedback-modal-close" aria-label="close"></button>';  
                }

            $modal_html .=  '</header>';

                $modal_html .=  '<section class="pgfy-feedback-modal-card-body">';
                
                    if(isset($this->settings['form_heading']) && $this->settings['form_heading'] !== ''):
                        $modal_html .= '<h3 class="pgfy-feedback-form-heading">'.$this->settings['form_heading'].'</h3>';
                    endif;

                    $modal_html .= '<div class="pgfy-feedback-modal-body">';
                    $modal_html .= '<form class="'.$form_id.'">';

                    foreach($this->settings['fields'] as $field): 
                        $reason = isset($field['reason']) ? $field['reason'] : '';
                        $category = (isset($field['category']) && $field['category'] !== '') ? $field['category'] : 'undefined';
                        $input_field = (isset($field['input_field']) && $field['input_field'] !== '') ? $field['input_field'] : false;
                        $placeholder = (isset($field['placeholder']) && $field['placeholder'] !== '') ? $field['placeholder'] : '';
                        $input_default = (isset($field['input_default']) && $field['input_default'] !== '') ? $field['input_default'] : '';
                        $instuction = (isset($field['instuction']) && $field['instuction'] !== '') ? $field['instuction'] : false;

                        $modal_html .= '<fieldset>';
                            $modal_html .= sprintf('<label><input type="radio" class="reason" name="reason" value="%1$s"/>%2$s</label>', $category, $reason);

                            if($input_field || $instuction):

                                $modal_html .= '<div class="pgfy-inner-field">';

                                if($input_field && $input_field === 'textarea'):
                                    $modal_html .= sprintf('<textarea  name="%1$s" placeholder="%2$s" value="%3$s"></textarea>', $category, $placeholder, $input_default);
                                elseif($input_field):
                                    $modal_html .= sprintf('<input type="%1$s" name="%2$s" placeholder="%3$s" value="%4$s" />', $input_field, $category, $placeholder, $input_default);
                                endif;

                                if($instuction):
                                    $modal_html .= '<p>'.$field['instuction'].'</p>';
                                endif;

                                $modal_html .= '</div>';
                            endif;
                        $modal_html .= '</fieldset>';
                    endforeach;

                    $modal_html .= '</form>';
                    $modal_html .= '</div>';
                $modal_html .=  '</section>';
                
                $modal_html .=  '<footer class="pgfy-feedback-modal-card-foot">';
                    $modal_html .=  '<div class="pgfy-submit-wrap"><button class="button button-primary">Send & Deactive</button><span class="loading" style="background-image: url('.home_url().'/wp-admin/images/spinner.gif)"></span></div>';
                    $modal_html .=  '<a href="" class="pgfy-feedback-deactivation-link">Skip & Deactive</a>';
                $modal_html .=  '</footer>';

        $modal_html .= '</div>';
        $modal_html .= '</div>';

        return $modal_html;
    }


    public function submit_feedback() {
        add_action( 'wp_ajax_deactivation_feedback', array($this, 'deactivation_feedback') );
    }

    public function deactivation_feedback() {
   
        if((!isset($_POST['formData']) || !wp_verify_nonce( $_POST['security'], 'valid-feedback-submit'))) {
            wp_send_json_error();
        }

        $form_data = $_POST['formData'];
        $reason_category = $form_data['reason'];
        $reason_text = isset($form_data[$reason_category]) ? esc_html($form_data[$reason_category]) : '';

        if ( 'temporary_deactivation' === $reason_category ) {
            wp_send_json_success( true );
        }

        $plugin = $this->settings['plugin_name'];
        $plugin_version = isset($this->settings['plugin_version']) ? $this->settings['plugin_version'] : '1.0.0';

        $theme = array(
            'is_child_theme'   => is_child_theme(),
            'parent_theme'     => wp_get_theme( get_template() )->get( 'Name' ),
            'theme_name'       => wp_get_theme()->get( 'Name' ),
            'theme_version'    => wp_get_theme()->get( 'Version' ),
            'theme_uri'        => wp_get_theme( get_template() )->get( 'ThemeURI' ),
            'theme_author'     => wp_get_theme( get_template() )->get( 'Author' ),
            'theme_author_uri' => wp_get_theme( get_template() )->get( 'AuthorURI' ),
        );
        
        $database_version = wc_get_server_database_version();
        $active_plugins   = (array) get_option( 'active_plugins', array() );
        
        if ( is_multisite() ) {
            $network_activated_plugins = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
            $active_plugins            = array_merge( $active_plugins, $network_activated_plugins );
        }
        
        $environment = array(
            'is_multisite'         => is_multisite(),
            'site_url'             => get_option( 'siteurl' ),
            'home_url'             => get_option( 'home' ),
            'php_version'          => phpversion(),
            'mysql_version'        => $database_version[ 'number' ],
            'mysql_version_string' => $database_version[ 'string' ],
            'wc_version'           => WC()->version,
            'wp_version'           => get_bloginfo( 'version' ),
            'server_info'          => isset( $_SERVER[ 'SERVER_SOFTWARE' ] ) ? wc_clean( wp_unslash( $_SERVER[ 'SERVER_SOFTWARE' ] ) ) : '',
        );

        $response = wp_remote_post( $this->settings['api_url'], array(
            'sslverify' => false,
            'timeout'   => 60,
            'body'      => array(
                'plugin'       => $plugin,
                'version'      => $plugin_version,
                'reason_category' => $reason_category,
                'reason_text'  => $reason_text,
                'theme'        => $theme,
                'plugins'      => $active_plugins,
                'environment'  => $environment
            )
        ) );


        $responce_code = wp_remote_retrieve_response_code( $response );
        
        if ( ! is_wp_error( $response ) && $responce_code >= 200  &&  $responce_code <= 299) {
            wp_send_json_success( wp_remote_retrieve_body( $response ) );
        } else {
            wp_send_json_error( wp_remote_retrieve_response_message( $response ) );
        }
    }


    public function url($filte) {
        $dir_url = plugin_dir_url(  __FILE__);
        return $dir_url . '/' . $filte;
    }
}
