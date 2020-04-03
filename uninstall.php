<?php 
/**
 * Accomplish Uninstall / Delete woocommerce prodcut recommend data
 * 
 * @since      1.0.0
*/

if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit; // Exit if accessed directly

$all_plugins = get_plugins();
$check_pro_version = array_key_exists('woocommerce-product-recommend-pro/woocommerce-product-recommend-pro.php', $all_plugins);

if(!$check_pro_version) {
    delete_post_meta_by_key( '_pgfy_pr_data' );  // remove all data 
}
