<?php
/**
 * Plugin Name: Leo Product Recommendations for WooCommerce
 * Plugin URI: https://leocoder.com/leo-product-recommendations
 * Description: Recommend products smartly for boosting WooCommerce sales by nice-looking add to cart popup
 * Version: 2.6.0
 * Requires at least: 5.7
 * Requires PHP: 7.4
 * Author: Md Hasanuzzaman
 * Author URI: https://leocoder.com/
 * Text Domain: leo-product-recommendations
 * Domain Path: /languages
 * WC requires at least: 5.0
 * WC tested up to: 7.7.0
 * License: GPLv3 or later License
 * URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

use LoeCoder\Plugin\ProductRecommendations\Product_Recommendations;

if (!class_exists(Product_Recommendations::class)) {
   require plugin_dir_path(__FILE__) . 'includes/class-product-recommendations.php';
}

/**
 * Plugin execution
 * @since    1.0.0
 */
function leo_product_recommendations() {
    return Product_Recommendations::init(__FILE__);
}
leo_product_recommendations();
