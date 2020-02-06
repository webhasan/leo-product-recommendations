<?php
/**
 * Plugin Name: WooCommerce Product Recommend
 * Plugin URI: https://pluginsify.com/
 * Description: WooCommerce product recommend after added to cart.
 * Version: 1.0.0
 * Requires at least: 4.5
 * Author: Pluginsify
 * Author URI: https://pluginsify.com/
 * License: GPLv2 or later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 * Text Domain: woocommerce-product-recommend
 * Domain Path: /languages
 * WC requires at least: 3.2
 * WC tested up to: 3.8
 */

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if(!class_exists('Pgfy_Woo_Product_Recommend')) {
	require plugin_dir_path( __FILE__ ) . 'includes/class-pgfy-woo-product-recommend.php';
}

/**
 * Begins execution of the plugin.
 * @since    1.0.0
 */

Pgfy_Woo_Product_Recommend::instance()->init();
