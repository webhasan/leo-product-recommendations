<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @since      1.0.0
 * @author     LeoCoder
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

// Delete plugin settings option.
$lc_pr_settings_id = 'lc_lpr_settings';
delete_option($lc_pr_settings_id);

// For multisite installations, delete from all sites.
if (is_multisite()) {
	global $wpdb;
	$lc_pr_blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
	
	foreach ($lc_pr_blog_ids as $lc_pr_blog_id) {
		switch_to_blog($lc_pr_blog_id);
		delete_option($lc_pr_settings_id);
		restore_current_blog();
	}
}