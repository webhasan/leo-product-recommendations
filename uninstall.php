<?php 
/**
 * Delete WooCommerce product recommendations data
 * 
 * @since      1.0.0
*/

if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit; // Exit if accessed directly

$all_plugins = get_plugins();
$check_pro_version = array_key_exists('woocommerce-product-recommendations-pro/woocommerce-product-recommendations-pro.php', $all_plugins);

if(!$check_pro_version) {
    delete_post_meta_by_key( '_lc_wpr_data' );  // remove all data 
}
