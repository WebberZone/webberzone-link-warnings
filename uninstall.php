<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Everything in uninstall.php will be executed when the user decides
 * to delete the plugin from the WordPress admin.
 *
 * @since 1.0.0
 *
 * @package WebberZone\Better_External_Links
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Fired when the plugin is uninstalled.
 *
 * @since 1.0.0
 */
do_action( 'wz_bel_uninstall' );

global $wpdb;

if ( is_multisite() ) {

	$sites = get_sites(
		array(
			'archived' => 0,
			'spam'     => 0,
			'deleted'  => 0,
		)
	);

	foreach ( $sites as $site ) {
		switch_to_blog( (int) $site->blog_id );
		wz_bel_delete_data();
		restore_current_blog();
	}
} else {
	wz_bel_delete_data();
}

/**
 * Delete plugin data for the current site.
 *
 * @since 1.0.0
 */
function wz_bel_delete_data() {

	// Delete plugin settings.
	delete_option( 'wz_bel_settings' );

	// Delete wizard options.
	delete_option( 'wz_bel_show_wizard' );
	delete_option( 'wz_bel_wizard_completed' );
	delete_option( 'wz_bel_wizard_completed_date' );
	delete_option( 'wz_bel_wizard_current_step' );

	// Delete transients.
	delete_transient( 'wz_bel_activation_redirect' );
	delete_transient( 'wz_bel_show_wizard_activation_redirect' );

	// Flush rewrite rules.
	global $wp_rewrite;
	$wp_rewrite->init();
	flush_rewrite_rules();
}
