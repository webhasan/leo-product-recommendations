<?php
/**
 * LPR Settings > Get Started tab: feature request card.
 *
 * A short form where the store owner can send an idea to LeoCoder.
 * Nothing is sent until the user clicks Send. Only the idea, the optional
 * "what would it help with" text, the optional reply email and the plugin
 * edition and version are sent. No site URL or other site details.
 *
 * @package LoeCoder\Plugin\ProductRecommendations
 * @since   3.7.0
 */

namespace LoeCoder\Plugin\ProductRecommendations;

if (!defined('ABSPATH')) {
	exit;
}

class Feature_Request {

	const ACTION = 'lpr_feature_request';

	const API_URL = 'https://leocoder.com/wp-json/leocoder/v1/feature-request';

	/**
	 * @var Product_Recommendations
	 */
	private $base;

	public function __construct($base) {
		$this->base = $base;

		add_action('wp_ajax_' . self::ACTION, array($this, 'ajax_send'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue'));
	}

	/**
	 * Path of the feature request card, included by the Get Started tab (Free and Pro).
	 *
	 * @return string
	 */
	public static function template() {
		return __DIR__ . '/feature-request.php';
	}

	public function enqueue() {
		$screen = get_current_screen();
		if (!$screen || false === strpos((string) $screen->id, 'lpr-settings')) {
			return;
		}

		$file = 'assets/js/feature-request.js';
		$ver  = $this->base->script_version() . '.' . (int) @filemtime($this->base->get_path($file));
		wp_enqueue_script('lpr-feature-request', $this->base->get_url($file), array('jquery', 'wp-i18n'), $ver, true);
		wp_set_script_translations('lpr-feature-request', 'leo-product-recommendations');
		wp_localize_script('lpr-feature-request', 'lpr_feature_request', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'action'   => self::ACTION,
			'nonce'    => wp_create_nonce(self::ACTION),
		));
	}

	/**
	 * Free, or "Pro x.y.z" when Pro is active.
	 *
	 * @return string
	 */
	private function edition() {
		$edition = 'Free ' . $this->base->get_plugin_version();

		if ($this->base->is_pro_activated()) {
			if (!function_exists('get_plugins')) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			foreach (get_plugins() as $data) {
				if (isset($data['Name']) && false !== strpos($data['Name'], 'Product Recommendations for WooCommerce Pro')) {
					$edition .= ', Pro ' . $data['Version'];
					break;
				}
			}
		}

		return $edition;
	}

	public function ajax_send() {
		check_ajax_referer(self::ACTION, 'security');

		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => __('You are not allowed to do this.', 'leo-product-recommendations')), 403);
		}

		$idea  = isset($_POST['idea']) ? sanitize_textarea_field(wp_unslash($_POST['idea'])) : '';
		$why   = isset($_POST['why']) ? sanitize_textarea_field(wp_unslash($_POST['why'])) : '';
		$email_raw = isset($_POST['email']) ? trim(sanitize_text_field(wp_unslash($_POST['email']))) : '';
		$email     = sanitize_email($email_raw);

		$idea = trim(mb_substr($idea, 0, 2000));
		$why  = trim(mb_substr($why, 0, 1000));

		if ('' === $idea) {
			wp_send_json_error(array('message' => __('Please describe the feature you would like.', 'leo-product-recommendations')));
		}

		if ('' !== $email_raw && !is_email($email)) {
			wp_send_json_error(array('message' => __('That email address does not look right. Fix it or leave the field empty.', 'leo-product-recommendations')));
		}

		// One request a minute per user is plenty and stops accidental double sends.
		$lock = 'lpr_fr_sent_' . get_current_user_id();
		if (get_transient($lock)) {
			wp_send_json_error(array('message' => __('Thanks, we just got your request. Please wait a minute before sending another one.', 'leo-product-recommendations')));
		}

		$version  = $this->base->get_plugin_version();
		$response = wp_remote_post(apply_filters('lpr_feature_request_api_url', self::API_URL), array(
			'timeout'    => 20,
			// WordPress puts the site URL in its default user agent; send a neutral one.
			'user-agent' => 'LeoCoder-Feature-Request/' . $version,
			'body'       => array(
				'plugin'  => 'Leo Product Recommendations for WooCommerce',
				'version' => $version,
				'edition' => $this->edition(),
				'idea'    => $idea,
				'why'     => $why,
				'email'   => $email,
			),
		));

		$code = wp_remote_retrieve_response_code($response);

		if (is_wp_error($response) || $code < 200 || $code > 299) {
			wp_send_json_error(array('message' => __('Sorry, it could not be sent right now. Please try again in a few minutes, or post it on the support forum.', 'leo-product-recommendations')));
		}

		set_transient($lock, 1, MINUTE_IN_SECONDS);

		wp_send_json_success(array('message' => __('Thanks! Your request was sent. We read every one.', 'leo-product-recommendations')));
	}
}
