<?php
/**
 * Activator class.
 *
 * Handles activation tasks for the Better External Links plugin.
 *
 * @since 1.0.0
 *
 * @package WebberZone\Better_External_Links\Admin
 */

namespace WebberZone\Better_External_Links\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WebberZone\Better_External_Links\Util\Hook_Registry;

/**
 * Class Activator
 *
 * Handles activation tasks for the Better External Links plugin.
 */
class Activator {

	/**
	 * Constructor class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'wp_initialize_site', array( $this, 'activate_new_site' ) );
	}

	/**
	 * Activation method.
	 *
	 * @since 1.0.0
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses
	 *                              "Network Activate" action, false if
	 *                              WPMU is disabled or plugin is
	 *                              activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
		// Check PHP version.
		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			deactivate_plugins( plugin_basename( WZ_BEL_PLUGIN_FILE ) );
			/* translators: %s: Current PHP version */
			wp_die( wp_kses_post( sprintf( __( 'Better External Links requires PHP 7.4 or higher. You are running PHP %s', 'better-external-links' ), esc_html( PHP_VERSION ) ) ) );
		}

		// Check WordPress version.
		if ( version_compare( $GLOBALS['wp_version'], '6.6', '<' ) ) {
			deactivate_plugins( plugin_basename( WZ_BEL_PLUGIN_FILE ) );
			/* translators: %s: Current WordPress version */
			wp_die( wp_kses_post( sprintf( __( 'Better External Links requires WordPress 6.6 or higher. You are running WordPress %s', 'better-external-links' ), esc_html( $GLOBALS['wp_version'] ) ) ) );
		}

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
				self::single_activate();
			}

			// Switch back to the current blog.
			restore_current_blog();

		} else {
			self::single_activate();
		}
	}

	/**
	 * Activates the plugin on a new site.
	 *
	 * @since 1.0.0
	 *
	 * @param int|\WP_Site $blog The blog ID.
	 */
	public function activate_new_site( $blog ) {
		if ( ! is_plugin_active_for_network( plugin_basename( WZ_BEL_PLUGIN_FILE ) ) ) {
			return;
		}

		if ( ! is_int( $blog ) ) {
			$blog = $blog->id;
		}

		switch_to_blog( $blog );
		self::single_activate();
		restore_current_blog();
	}

	/**
	 * Single site activation method.
	 *
	 * @since 1.0.0
	 */
	public static function single_activate() {
		// Set default options using Options API.
		$defaults = array(
			'warning_method'      => 'inline_modal',
			'scope'               => 'external',
			'visual_indicator'    => 'icon',
			'indicator_text'      => __( '(opens in new window)', 'better-external-links' ),
			'screen_reader_text'  => __( 'Opens in a new window', 'better-external-links' ),
			'modal_title'         => __( 'You are leaving this site', 'better-external-links' ),
			'modal_message'       => __( 'You are about to visit an external website. Continue?', 'better-external-links' ),
			'modal_continue_text' => __( 'Continue', 'better-external-links' ),
			'modal_cancel_text'   => __( 'Cancel', 'better-external-links' ),
			'redirect_message'    => __( 'You are being redirected to an external site.', 'better-external-links' ),
			'excluded_domains'    => '',
			'enabled_post_types'  => 'post,page',
		);

		// Use Options API to set defaults (merge=true preserves existing user settings on reactivation).
		\WebberZone\Better_External_Links\Options_API::update_settings( $defaults, true );

		// Flush rewrite rules.
		global $wp_rewrite;
		$wp_rewrite->init();
		flush_rewrite_rules();

		// Set a transient to trigger potential future wizard redirect (30-second expiry).
		set_transient( 'wz_bel_activation_redirect', true, 30 );

		/**
		 * Fires after plugin activation.
		 *
		 * @since 1.0.0
		 */
		do_action( 'wz_bel_activate' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
	}
}
