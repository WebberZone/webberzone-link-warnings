<?php
/**
 * Redirect Handler class.
 *
 * Handles redirect screen functionality.
 *
 * @package WebberZone\Better_External_Links
 * @since 1.0.0
 */

namespace WebberZone\Better_External_Links;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WebberZone\Better_External_Links\Util\Hook_Registry;

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
			'index.php?wz_ela_redirect=1',
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
		$vars[] = 'wz_ela_redirect';
		$vars[] = 'wz_ela_url';
		return $vars;
	}

	/**
	 * Handle redirect template.
	 *
	 * @since 1.0.0
	 */
	public function handle_redirect() {
		if ( ! get_query_var( 'wz_ela_redirect' ) ) {
			return;
		}

		$settings = wzbel_get_settings();
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

		// Verify that the destination matches the one used in the redirect URL generation to prevent open redirect bypass.
		$expected_url = get_query_var( 'wz_ela_url' );
		if ( ! empty( $expected_url ) && esc_url_raw( $expected_url ) !== $destination ) {
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
		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$url_host  = wp_parse_url( $url, PHP_URL_HOST );

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
				'better-external-links/redirect-screen.php',
				'better-external-links/redirect.php',
			)
		);

		if ( ! empty( $theme_template ) ) {
			return $theme_template;
		}

		return trailingslashit( WZ_BEL_PLUGIN_DIR ) . 'includes/templates/redirect-screen.php';
	}

	/**
	 * Render redirect template.
	 *
	 * @since 1.0.0
	 * @param string $destination Destination URL.
	 */
	protected function render_redirect_template( $destination ) {
		$message   = (string) wzbel_get_option( 'redirect_message', __( 'You are being redirected to an external site.', 'better-external-links' ) );
		$countdown = absint( wzbel_get_option( 'redirect_countdown', 5 ) );

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
		if ( ! get_query_var( 'wz_ela_redirect' ) ) {
			return;
		}

		$rtl_suffix = is_rtl() ? '-rtl' : '';
		$min_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_style(
			'wz-bel-redirect',
			WZ_BEL_PLUGIN_URL . 'includes/assets/css/redirect' . $rtl_suffix . $min_suffix . '.css',
			array(),
			WZ_BEL_VERSION
		);

		wp_enqueue_script(
			'wz-bel-redirect',
			WZ_BEL_PLUGIN_URL . 'includes/assets/js/redirect' . $min_suffix . '.js',
			array(),
			WZ_BEL_VERSION,
			true
		);

		// Pass destination URL to script.
		$destination = isset( $_GET['url'] ) ? esc_url_raw( wp_unslash( $_GET['url'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$settings  = wzbel_get_settings();
		$countdown = isset( $settings['redirect_countdown'] ) ? absint( $settings['redirect_countdown'] ) : 5;

		wp_localize_script(
			'wz-bel-redirect',
			'wzElaRedirect',
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
				'url' => rawurlencode( $destination ),
			),
			home_url( 'external-redirect/' )
		);
	}
}
