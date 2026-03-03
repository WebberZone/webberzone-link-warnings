<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Everything in uninstall.php will be executed when the user decides
 * to delete the plugin from the WordPress admin.
 *
 * @since 1.0.0
 *
 * @package WebberZone\Link_Warnings
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
do_action( 'wzlw_uninstall' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

global $wpdb;

if ( is_multisite() ) {

	$sites = get_sites( // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		array(
			'archived' => 0,
			'spam'     => 0,
			'deleted'  => 0,
		)
	);

	foreach ( $sites as $site ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		switch_to_blog( (int) $site->blog_id );
		wzlw_delete_data();
		restore_current_blog();
	}
} else {
	wzlw_delete_data();
}

/**
 * Delete plugin data for the current site.
 *
 * @since 1.0.0
 */
function wzlw_delete_data() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound

	// Delete plugin settings.
	delete_option( 'wzlw_settings' );

	// Delete wizard options.
	delete_option( 'wzlw_show_wizard' );
	delete_option( 'wzlw_wizard_completed' );
	delete_option( 'wzlw_wizard_completed_date' );
	delete_option( 'wzlw_wizard_current_step' );

	// Delete transients.
	delete_transient( 'wzlw_activation_redirect' );
	delete_transient( 'wzlw_show_wizard_activation_redirect' );

	// Flush rewrite rules.
	global $wp_rewrite;
	$wp_rewrite->init();
	flush_rewrite_rules();
}
