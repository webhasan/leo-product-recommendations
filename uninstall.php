<?php 
/**
 * Accomplish Uninstall / Delete woocommerce prodcut recommend data
 * 
 * @since      1.0.0
*/

if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit; // Exit if accessed directly

delete_post_meta_by_key( '_pgfy_pr_data' );  // remove all data 