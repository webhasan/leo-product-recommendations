<?php

/**
 * Plugin Name: WooCommerce Product Recommend
 * Plugin URI: https://pluginsify.com/
 * Description: WooCommerce Product Recommend is the best sell boosting plugin by recommending products to the customers based on purchased a product. After adding a product to the cart it will show the recommend products on a popup.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 5.6
 * Author: Pluginsify
 * Author URI: https://pluginsify.com/
 * License: GPLv2 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: woocommerce-product-recommend
 * Domain Path: /languages
 * WC requires at least: 3.5
 * WC tested up to: 4.1.0
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Pgfy_Woo_Product_Recommend')) {
	require plugin_dir_path(__FILE__) . 'includes/class-pgfy-woo-product-recommend.php';
}

/**
 * Begins execution of the plugin.
 * @since    1.0.0
 */

Pgfy_Woo_Product_Recommend::init(__FILE__);
