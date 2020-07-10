<?php
/**
 * Plugin Name: Woo Product Recommendations
 * Plugin URI: https://leocoder.com/woo-product-recommendations
 * Description: Recommend products smartly and increase sales by nice-looking add to cart popup
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 5.6
 * Author: LeoCoder
 * Author URI: https://leocoder.com/
 * Text Domain: woo-product-recommendations
 * Domain Path: /languages
 * WC requires at least: 3.5
 * WC tested up to: 4.3
 * License: GPLv3 or later License
 * URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

// Exit if accessed directly
if (!class_exists('LC_Woo_Product_Recommendations')) {
    require plugin_dir_path(__FILE__) . 'includes/class-lc-woo-product-recommendations.php';
}

/**
 * Plugin execution
 * @since    1.0.0
 */
LC_Woo_Product_Recommendations::init(__FILE__);
