<?php
namespace LoeCoder\Plugin;

if (!defined('ABSPATH')) {
    exit;
}
/**
 * Deactivation Feedback
 * Show feedback form to reason of deactivation plugin
 * @since      1.6
 * @author     Md Hasanuzzaman <webhasan24@gmail.com>
 */

class Deactivation_Feedback {

    /**
     * Array of all form and other require data
     *
     * @var arr
     */
    private $settings = [];

    /**
     * Script loaded or not. 
     * It will check all required css and js already available.
     *
     * @var arr
     */
    private static $has_scripts = false;


    /**
     * Class constructor, initialize everything
     * All action passed inside it.
     */
    public function __construct($settings) {
        $this->settings = $settings;
        $this->init_feedback_form();
        $this->submit_feedback();
    }

    /**
     * Initialized popup deactivation from.
     */
    private function init_feedback_form() {
        add_action('current_screen', function() {

            $current_screen = get_current_screen();

            if ($current_screen && in_array($current_screen->id, ['plugins', 'plugins-network'], true)) {
                $this->modal_scripts();
                add_action('admin_footer', [$this, 'add_modal']);
            }

        });

    }

    /**
     * Load all require scripts
     * If used deactivation feedback  in multiple plugins
     * scripts should load only one time.
     *
     * @return void
     */
    public function modal_scripts() {

        if (!self::$has_scripts) {
            add_action('admin_enqueue_scripts', function () {
                wp_enqueue_script('lprw-deactivation-feedback-script', $this->url('script.js'), ['jquery', 'wp-i18n'], $this->version('script.js'), true);
                wp_localize_script(
                    'lprw-deactivation-feedback-script', 
                    'leo_feedback_data', 
                    [
                        'ajax_url' =>  admin_url('admin-ajax.php'),
                        'security' => wp_create_nonce('valid-feedback-submit')
                    ]
                );
                wp_enqueue_style('lprw-deactivation-feedback-style', $this->url('style.css'), [], $this->version('style.css'));
            });

            self::$has_scripts = true;
        }
    }

    /**
     * Deactivation popup modal view.
     */
    public function add_modal() {
        echo $this->modal();
    }

    /**
     * Deactivation Modal
     * 
     * @return html markup for deactivation modal.
     */
    public function modal() {
        $settings = $this->settings;

        $modal_id   = 'lprw-feedback-modal-' . $settings['plugin_slug'];
        $form_id    = 'feedback-form-' . $settings['plugin_slug'];
        $modal_html = '';

        $modal_html .= '<div class="lprw-feedback-modal" id="' . $modal_id . '">';
        $modal_html .= ' <div class="lprw-feedback-modal-background"></div>';
        $modal_html .= '<div class="lprw-feedback-modal-card">';

        $modal_html .= '<header class="lprw-feedback-modal-card-head">';

        if ($settings['feedback_heading']) {
            $modal_html .= '<p class="lprw-feedback-modal-card-title">' . $settings['feedback_heading'] . '</p>';
            $modal_html .= '<a href="'.$settings['support']['support_url'].'" class="button" target="_blank">'.$settings['support']['title'].'</a>';
            $modal_html .= '<button class="lprw-feedback-modal-close" aria-label="close"></button>';
        }

        $modal_html .= '</header>';

        $modal_html .= '<section class="lprw-feedback-modal-card-body">';

        if (isset($settings['form_heading']) && $settings['form_heading'] !== ''):
            $modal_html .= '<h3 class="lprw-feedback-form-heading">' . $settings['form_heading'] . '</h3>';
        endif;

        $modal_html .= '<div class="lprw-feedback-modal-body">';
        $modal_html .= '<form class="' . $form_id . '">';

        foreach ($settings['fields'] as $field):
            $reason        = isset($field['reason']) ? $field['reason'] : '';
            $category      = (isset($field['category']) && $field['category'] !== '') ? $field['category'] : 'undefined';
            $input_field   = (isset($field['input_field']) && $field['input_field'] !== '') ? $field['input_field'] : false;
            $placeholder   = (isset($field['placeholder']) && $field['placeholder'] !== '') ? $field['placeholder'] : '';
            $input_default = (isset($field['input_default']) && $field['input_default'] !== '') ? $field['input_default'] : '';
            $instruction    = (isset($field['instruction']) && $field['instruction'] !== '') ? $field['instruction'] : false;

            $modal_html .= '<fieldset>';
            $modal_html .= sprintf('<label><input type="radio" class="reason" name="reason" value="%1$s"/>%2$s</label>', $category, $reason);

            if ($input_field || $instruction):

                $modal_html .= '<div class="lprw-inner-field">';

                if ($input_field && $input_field === 'textarea'):
                    $modal_html .= sprintf('<textarea  name="%1$s" placeholder="%2$s" value="%3$s"></textarea>', $category, $placeholder, $input_default);
                elseif ($input_field):
                    $modal_html .= sprintf('<input type="%1$s" name="%2$s" placeholder="%3$s" value="%4$s" />', $input_field, $category, $placeholder, $input_default);
                endif;

                if ($instruction):
                    $modal_html .= '<p>' . $field['instruction'] . '</p>';
                endif;

                $modal_html .= '</div>';
            endif;
            $modal_html .= '</fieldset>';
        endforeach;

        $modal_html .= '</form>';
        $modal_html .= '</div>';
        $modal_html .= '</section>';

        $modal_html .= '<footer class="lprw-feedback-modal-card-foot">';
        $modal_html .= '<div class="lprw-submit-wrap"><span class="error">'.__('Please select a reason.','leo-product-recommendations').'</span><button class="button button-primary">'.__('Send & Deactive','leo-product-recommendations').'</button><span class="loading" style="background-image: url(' . home_url() . '/wp-admin/images/spinner.gif)"></span></div>';
        $modal_html .= '<a href="" class="lprw-feedback-deactivation-link">'.__('Skip & Deactivate','leo-product-recommendations').'</a>';
        $modal_html .= '</footer>';

        $modal_html .= '</div>';
        $modal_html .= '</div>';

        return $modal_html;
    }

    /**
     * Create Ajax Handler for submit feedback
     */
    public function submit_feedback() {
        add_action('wp_ajax_deactivation_feedback_'.$this->settings['plugin_slug'], [$this, 'deactivation_feedback']);
    }

    /**
     * Submit feedback data to remote server
     */
    public function deactivation_feedback() {

        if ((!isset($_POST['formData']) || !wp_verify_nonce($_POST['security'], 'valid-feedback-submit'))) {
            wp_send_json_error('Invalid  Request!');
        }

        $settings        = $this->settings;
        $form_data       = $_POST['formData'];
        $reason_category = $form_data['reason'];
        $reason_text     = isset($form_data[$reason_category]) ? esc_html($form_data[$reason_category]) : '';

        if ('temporary_deactivation' === $reason_category) {
            wp_send_json_success(true);
        }

        $plugin         = $settings['plugin_name'];
        $plugin_version = isset($settings['plugin_version']) ? $settings['plugin_version'] : '1.0.0';

        $theme = [
            'is_child_theme'   => is_child_theme(),
            'parent_theme'     => wp_get_theme(get_template())->get('Name'),
            'theme_name'       => wp_get_theme()->get('Name'),
            'theme_version'    => wp_get_theme()->get('Version'),
            'theme_uri'        => wp_get_theme(get_template())->get('ThemeURI'),
            'theme_author'     => wp_get_theme(get_template())->get('Author'),
            'theme_author_uri' => wp_get_theme(get_template())->get('AuthorURI'),
        ];

        $database_version = wc_get_server_database_version();
        $active_plugins   = (array) get_option('active_plugins', []);

        if (is_multisite()) {
            $network_activated_plugins = array_keys(get_site_option('active_sitewide_plugins', []));
            $active_plugins            = array_merge($active_plugins, $network_activated_plugins);
        }

        $environment = [
            'is_multisite'         => is_multisite(),
            'site_url'             => get_option('siteurl'),
            'home_url'             => get_option('home'),
            'php_version'          => phpversion(),
            'mysql_version'        => $database_version['number'],
            'mysql_version_string' => $database_version['string'],
            'wc_version'           => WC()->version,
            'wp_version'           => get_bloginfo('version'),
            'server_info'          => isset($_SERVER['SERVER_SOFTWARE']) ? wc_clean(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : '',
        ];

        $response = wp_remote_post($settings['api_url'], [
            'sslverify' => false,
            'timeout'   => 60,
            'body'      => [
                'plugin'          => $plugin,
                'version'         => $plugin_version,
                'reason_category' => $reason_category,
                'reason_text'     => $reason_text,
                'theme'           => $theme,
                'plugins'         => $active_plugins,
                'environment'     => $environment,
            ],
        ]);

        $response_code = wp_remote_retrieve_response_code($response);

        if (!is_wp_error($response) && $response_code >= 200 && $response_code <= 299) {
            wp_send_json_success(wp_remote_retrieve_body($response));
        } else {
            wp_send_json_error(wp_remote_retrieve_response_message($response));
        }

    }

    /**
     * Script version of the plugin
     * Version will change based on file change time
     *
     * @since      1.6
     * @param      string file path 
     * @return     string of version based on file changed time
     */
    public function version($file) {
        return filemtime(plugin_dir_path(__FILE__) . $file);
    }

    /**
     * URL of the assets
     * @since      1.6
     * @return string of file url 
     */
    public function url($file) {
        $dir_url = plugin_dir_url(__FILE__);
        return $dir_url . $file;
    }
}
