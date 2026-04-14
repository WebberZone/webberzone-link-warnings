<?php
/**
 * Redirect Handler class.
 *
 * Handles redirect screen functionality.
 *
 * @package WebberZone\Link_Warnings
 * @since 1.0.0
 */

namespace WebberZone\Link_Warnings;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WebberZone\Link_Warnings\Util\Hook_Registry;

/**
 * Redirect Handler class.
 *
 * @since 1.0.0
 */
class Redirect_Handler {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		Hook_Registry::add_action( 'template_redirect', array( $this, 'handle_redirect' ) );
		Hook_Registry::add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		Hook_Registry::add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_redirect_assets' ) );
	}

	/**
	 * Add rewrite rules for redirect endpoint.
	 *
	 * @since 1.0.0
	 */
	public function add_rewrite_rules() {
		add_rewrite_rule(
			'^external-redirect/?',
			'index.php?wzlw_redirect=1',
			'top'
		);
	}

	/**
	 * Add query vars.
	 *
	 * @since 1.0.0
	 * @param array $vars Query vars.
	 * @return array Modified query vars.
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'wzlw_redirect';
		$vars[] = 'wzlw_sig';
		return $vars;
	}

	/**
	 * Handle redirect template.
	 *
	 * @since 1.0.0
	 */
	public function handle_redirect() {
		if ( ! get_query_var( 'wzlw_redirect' ) ) {
			return;
		}

		$settings = wzlw_get_settings();
		$method   = isset( $settings['warning_method'] ) ? $settings['warning_method'] : 'inline';

		if ( ! in_array( $method, array( 'redirect', 'inline_redirect' ), true ) ) {
			return;
		}

		// Get and validate destination URL.
		$destination = isset( $_GET['url'] ) ? esc_url_raw( wp_unslash( $_GET['url'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( empty( $destination ) || ! $this->is_valid_url( $destination ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}

		// Verify HMAC signature to prevent open redirect abuse.
		$provided_sig = isset( $_GET['wzlw_sig'] ) ? sanitize_text_field( wp_unslash( $_GET['wzlw_sig'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$expected_sig = hash_hmac( 'sha256', $destination, wp_salt( 'auth' ) );
		if ( ! hash_equals( $expected_sig, $provided_sig ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}

		// Render redirect template.
		$this->render_redirect_template( $destination );
		exit;
	}

	/**
	 * Validate URL.
	 *
	 * @since 1.0.0
	 * @param string $url URL to validate.
	 * @return bool True if valid.
	 */
	protected function is_valid_url( $url ) {
		// Basic validation.
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		// Must be external.
		$site_host = strtolower( rtrim( (string) wp_parse_url( home_url(), PHP_URL_HOST ), '.' ) );
		$url_host  = strtolower( rtrim( (string) wp_parse_url( $url, PHP_URL_HOST ), '.' ) );

		return $url_host !== $site_host;
	}

	/**
	 * Get redirect template path.
	 *
	 * Checks the child theme / parent theme first, then falls back to the plugin template.
	 *
	 * @since 1.0.0
	 *
	 * @return string Template file path.
	 */
	public function get_redirect_template_path() {
		$theme_template = locate_template(
			array(
				'webberzone-link-warnings/redirect-screen.php',
				'webberzone-link-warnings/redirect.php',
			)
		);

		if ( ! empty( $theme_template ) ) {
			return $theme_template;
		}

		return trailingslashit( WZLW_PLUGIN_DIR ) . 'includes/templates/redirect-screen.php';
	}

	/**
	 * Render redirect template.
	 *
	 * @since 1.0.0
	 * @param string $destination Destination URL.
	 */
	protected function render_redirect_template( $destination ) {
		$message   = (string) wzlw_get_option( 'redirect_message', __( 'You are being redirected to an external site.', 'webberzone-link-warnings' ) );
		$countdown = absint( wzlw_get_option( 'redirect_countdown', 5 ) );

		// Parse destination for display.
		$parsed_url = wp_parse_url( $destination );
		$domain     = isset( $parsed_url['host'] ) ? (string) $parsed_url['host'] : '';

		$template = $this->get_redirect_template_path();
		if ( ! empty( $template ) && file_exists( $template ) ) {
			include $template;
			return;
		}

		echo esc_html( $destination );
	}

	/**
	 * Enqueue redirect page assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_redirect_assets() {
		if ( ! get_query_var( 'wzlw_redirect' ) ) {
			return;
		}

		$rtl_suffix = is_rtl() ? '-rtl' : '';
		$min_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_style(
			'wzlw-redirect',
			WZLW_PLUGIN_URL . 'includes/assets/css/redirect' . $rtl_suffix . $min_suffix . '.css',
			array(),
			WZLW_VERSION
		);

		wp_enqueue_script(
			'wzlw-redirect',
			WZLW_PLUGIN_URL . 'includes/assets/js/redirect' . $min_suffix . '.js',
			array(),
			WZLW_VERSION,
			true
		);

		// Pass destination URL to script.
		$destination = isset( $_GET['url'] ) ? esc_url_raw( wp_unslash( $_GET['url'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$settings  = wzlw_get_settings();
		$countdown = isset( $settings['redirect_countdown'] ) ? absint( $settings['redirect_countdown'] ) : 5;

		wp_localize_script(
			'wzlw-redirect',
			'wzlwRedirect',
			array(
				'destination' => $destination,
				'countdown'   => $countdown,
			)
		);
	}

	/**
	 * Get redirect URL for a destination.
	 *
	 * @since 1.0.0
	 * @param string $destination Destination URL.
	 * @return string Redirect URL.
	 */
	public static function get_redirect_url( $destination ) {
		return add_query_arg(
			array(
				'url'      => $destination,
				'wzlw_sig' => hash_hmac( 'sha256', $destination, wp_salt( 'auth' ) ),
			),
			home_url( 'external-redirect/' )
		);
	}
}
