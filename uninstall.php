<?php 
/**
 * Accomplish Uninstall / Delete woocommerce prodcut recommend 
 * 
 * @since      1.0.0
*/

if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit; // Exit if accessed directly

$allposts = get_posts( 'post_type=post' );
foreach($allposts as $single_post) {
    delete_post_meta($single_post->ID, 'pgfy_pr_data');
}
