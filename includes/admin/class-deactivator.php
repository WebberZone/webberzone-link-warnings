<?php
/**
 * Deactivator class.
 *
 * Handles deactivation tasks for the Better External Links plugin.
 *
 * @since 1.0.0
 *
 * @package WebberZone\Better_External_Links\Admin
 */

namespace WebberZone\Better_External_Links\Admin;

/**
 * Class Deactivator
 *
 * Handles deactivation tasks for the Better External Links plugin.
 */
class Deactivator {

	/**
	 * Deactivation method.
	 *
	 * @since 1.0.0
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses
	 *                              "Network Deactivate" action, false if
	 *                              WPMU is disabled or plugin is
	 *                              deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {
		if ( is_multisite() && $network_wide ) {
			$sites = get_sites(
				array(
					'archived' => 0,
					'spam'     => 0,
					'deleted'  => 0,
				)
			);

			foreach ( $sites as $site ) {
				switch_to_blog( (int) $site->blog_id );
				self::single_deactivate();
				restore_current_blog();
			}
		} else {
			self::single_deactivate();
		}

		/**
		 * Fires after plugin deactivation.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wz_bel_deactivate' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
	}

	/**
	 * Single site deactivation method.
	 *
	 * @since 1.0.0
	 */
	public static function single_deactivate() {
		// Clean up transients.
		delete_transient( 'wz_bel_activation_redirect' );

		// Clear any cached data.
		wp_cache_flush();

		// Flush the rewrite rules.
		global $wp_rewrite;
		$wp_rewrite->init();
		flush_rewrite_rules();
	}
}
