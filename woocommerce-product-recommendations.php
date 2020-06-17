<?php

/**
 * Plugin Name: WooCommerce Product Recommendations
 * Plugin URI: https://leocoder.com/
 * Description: WooCommerce Product Recommendations is the best sell boosting plugin by recommending products to the customers based on purchased a product. After adding a product to the cart it will show the recommend products on a popup.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 5.6
 * Author: LeoCoder
 * Author URI: https://leocoder.com/
 * License: GPLv2 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: woocommerce-product-recommendations
 * Domain Path: /languages
 * WC requires at least: 3.5
 * WC tested up to: 4.2.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('LC_Woo_Product_Recommendations')) {
	require plugin_dir_path(__FILE__) . 'includes/class-lc-woo-product-recommendations.php';
}

/**
 * Begins execution of the plugin.
 * @since    1.0.0
 */

LC_Woo_Product_Recommendations::init(__FILE__);
