<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Removes the plugin's own settings and saved counts. Per-product recommendations
 * (product meta) are kept, so they are back if the plugin is installed again.
 *
 * @since      1.0.0
 * @author     LeoCoder
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

// Wrapped in a function so nothing is left in the global scope.
call_user_func(function () {
	$clean = function () {
		delete_option('lc_lpr_settings');
		delete_option('lc_lpr_settings_backup');
		delete_option('lpr_milestones');
		delete_transient('lpr_smart_teaser');
	};

	if (!is_multisite()) {
		$clean();
		return;
	}

	// Multisite: every site.
	foreach (get_sites(array('fields' => 'ids', 'number' => 0)) as $blog_id) {
		switch_to_blog($blog_id);
		$clean();
		restore_current_blog();
	}
});
