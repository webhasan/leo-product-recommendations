<?php
/**
 * Deactivation Feedback
 * Show feedback form to reason of deactivation plugin
 * @since      1.0.0
 * @author     Md Hasanuzzaman <webhasan24@gmail.com>
 */

class Pgfy_Deactivation_Feedback {
    protected $settings;
    
    public function __construct($settings) {
        $this->settings = $settings;
        $this->init();
    }

    public function modal() {
        $modal_id = 'pgfy-feedback-modal-'.$this->settings['plugin_slug'];
        $modal_html = '';

        $modal_html .= '<div class="modal" id="'.$modal_id.'"> <div class="modal-background"></div>';
        $modal_html .=  '<div class="modal-card">';

            $modal_html .=  '<header class="modal-card-head">';

                if($this->settings['feedback_heading']) {
                    $modal_html .=  '<p class="modal-card-title">'.$this->settings['feedback_heading'].'</p>';    
                }

                if($this->settings['feedback_description']) {
                    $modal_html .=  '<p class="modal-card-description">'.$this->settings['feedback_description'].'</p>';    
                }
                
            $modal_html .=  '</header>';

                $modal_html .=  '<section class="modal-card-body">';
                    $modal_html .=  '<h2>Body will go here.</h2>';
                $modal_html .=  '</section>';
                
                $modal_html .=  '<footer class="modal-card-foot">';
                    $modal_html .=  '<button class="button is-success">Save changes</button>';
                    $modal_html .=  '<button class="button">Cancel</button>';
                $modal_html .=  '</footer>';

        $modal_html .= '</div>';
        $modal_html .= '</div>';

        return $modal_html;
    }


    protected function init() {
        add_action('admin_enqueue_scripts', array($this, 'modal_scripts')); 
        add_action('admin_footer', array($this, 'add_modal'));
    }

    public function modal_scripts() {
        wp_enqueue_script('pgfy-deactivation-feedback-script', $this->url('script.js'), array('jquery'), false, true);
        wp_localize_script( 'pgfy-deactivation-feedback-script', 'ajax_url', admin_url( 'admin-ajax.php' ));

        wp_enqueue_style( 'pgfy-deactivation-feedback-style', $this->url('style.css'));
    }

    public function add_modal() {
        echo $this->modal();
    }

    public function url($filte) {
        $dir_url = plugin_dir_url(  __FILE__);
        return $dir_url . '/' . $filte;
    }
}
