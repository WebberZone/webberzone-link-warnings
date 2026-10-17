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
	 * Whether the current redirect screen needs the visitor to click Continue.
	 *
	 * @since 1.7.0
	 * @var bool
	 */
	private $confirm_only = false;

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
		Hook_Registry::add_action( 'wp_ajax_wzlw_sign_urls', array( $this, 'ajax_sign_urls' ) );
		Hook_Registry::add_action( 'wp_ajax_nopriv_wzlw_sign_urls', array( $this, 'ajax_sign_urls' ) );
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
		$vars[] = 'wzlw_confirm';
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

		$global_redirect = in_array( $method, array( 'redirect', 'inline_redirect' ), true );
		$rules           = Link_Rules::get_rules( $settings );
		if ( ! $global_redirect && ! Link_Rules::uses_action( array( 'redirect', 'inline_redirect' ), $rules ) ) {
			return;
		}

		// Get and validate destination URL.
		$destination = isset( $_GET['url'] ) && is_string( $_GET['url'] ) ? esc_url_raw( wp_unslash( $_GET['url'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( empty( $destination ) || ! $this->is_valid_url( $destination ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}

		// Verify HMAC signature to prevent open redirect abuse. A confirm-only signature
		// has its own HMAC input, so it cannot be turned into an auto-redirecting one.
		$confirm_only = isset( $_GET['wzlw_confirm'] ) && '1' === $_GET['wzlw_confirm']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$provided_sig = isset( $_GET['wzlw_sig'] ) && is_string( $_GET['wzlw_sig'] ) ? sanitize_text_field( wp_unslash( $_GET['wzlw_sig'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! hash_equals( self::sign( $destination, $confirm_only ), $provided_sig ) ) {
			// Signatures from before 1.7.0 used the bare destination and anyone could get
			// one. They still work, so cached pages keep working, but only as confirm-only.
			if ( $confirm_only || ! hash_equals( hash_hmac( 'sha256', $destination, wp_salt( 'auth' ) ), $provided_sig ) ) {
				wp_safe_redirect( home_url() );
				exit;
			}
			$confirm_only = true;
		}

		// The rule comes from the signed destination, never from the request.
		$rule = $this->get_redirect_rule( $destination, $rules );
		if ( ! $global_redirect && ! $rule ) {
			wp_safe_redirect( home_url() );
			exit;
		}

		// The screen differs by referrer, so it must never be cached.
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		nocache_headers();

		// Render redirect template.
		$this->confirm_only = ! $this->allows_countdown( $destination, $confirm_only );
		$this->render_redirect_template( $destination, $rule ? $rule['message'] : '' );
		exit;
	}

	/**
	 * Get the redirect rule that matches a destination.
	 *
	 * @since 1.7.0
	 * @param string       $destination Destination URL.
	 * @param array[]|null $rules       Rules from Link_Rules::get_rules(). Defaults to the saved rules.
	 * @return array|null The first matching rule if its action is a redirect, or null.
	 */
	protected function get_redirect_rule( $destination, $rules = null ) {
		return self::find_redirect_rule( $destination, null === $rules ? Link_Rules::get_rules() : $rules );
	}

	/**
	 * Check whether the redirect screen may count down and redirect automatically.
	 *
	 * A signed URL is a reusable token, so wherever it was signed, it only redirects
	 * automatically for a visitor who arrives from a page on this site. Opened from an
	 * email or another site, the screen waits for the visitor to click Continue.
	 * Destinations on this site are always safe to redirect to.
	 *
	 * @since 1.7.0
	 * @param string $destination  Destination URL.
	 * @param bool   $confirm_only Whether the signature is confirm-only.
	 * @return bool True if the countdown may run.
	 */
	protected function allows_countdown( $destination, $confirm_only ) {
		if ( $confirm_only ) {
			return false;
		}

		return self::is_same_site( $destination ) || self::is_same_site( isset( $_SERVER['HTTP_REFERER'] ) && is_string( $_SERVER['HTTP_REFERER'] ) ? wp_unslash( $_SERVER['HTTP_REFERER'] ) : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Only compared, never output.
	}

	/**
	 * Check whether a URL points to this site, including its port.
	 *
	 * @since 1.7.0
	 * @param string $url URL.
	 * @return bool True if the URL is on this site.
	 */
	public static function is_same_site( $url ) {
		$url = Link_Rules::normalize_href( (string) $url );
		if ( '' === $url ) {
			return false;
		}

		$site_host = Link_Rules::site_host();
		$subject   = Link_Rules::get_subject( $url, $site_host );
		if ( ! $subject || $site_host !== $subject['host'] ) {
			return false;
		}

		// Another port on the same host can be another service, so an explicit port must match.
		$port = wp_parse_url( $url, PHP_URL_PORT );
		if ( null === $port || false === $port ) {
			return true;
		}

		$home_port = wp_parse_url( home_url(), PHP_URL_PORT );
		if ( ! $home_port ) {
			$home_port = 'http' === wp_parse_url( home_url(), PHP_URL_SCHEME ) ? 80 : 443;
		}

		return (int) $port === (int) $home_port;
	}

	/**
	 * Find the redirect rule that matches a destination.
	 *
	 * @since 1.7.0
	 * @param string  $destination Destination URL.
	 * @param array[] $rules       Rules from Link_Rules::get_rules().
	 * @return array|null The first matching rule if its action is a redirect, or null.
	 */
	private static function find_redirect_rule( $destination, array $rules ) {
		$rule = Link_Rules::match( $destination, Link_Rules::site_host(), $rules );

		return ( $rule && in_array( $rule['action'], array( 'redirect', 'inline_redirect' ), true ) ) ? $rule : null;
	}

	/**
	 * Check whether a destination may redirect automatically after the countdown.
	 *
	 * Destinations on this site and destinations covered by a redirect rule may.
	 * Any other destination from a source the site does not control, such as a
	 * comment or a URL sent to the signing endpoint, gets a confirm-only signature
	 * so the redirect screen cannot be used as an automatic open redirect.
	 *
	 * @since 1.7.0
	 * @param string  $destination Destination URL.
	 * @param array[] $rules       Rules from Link_Rules::get_rules().
	 * @return bool True if the destination may redirect automatically.
	 */
	public static function allows_auto_redirect( $destination, array $rules ) {
		return self::is_same_site( $destination ) || null !== self::find_redirect_rule( $destination, $rules );
	}

	/**
	 * Validate URL.
	 *
	 * Accepts root-relative paths, network-path references and absolute URLs. Authorisation is the HMAC, not the host.
	 *
	 * @since 1.0.0
	 * @param string $url URL to validate.
	 * @return bool True if valid.
	 */
	protected function is_valid_url( $url ) {
		$parsed_url = wp_parse_url( $url );

		// Root-relative paths (e.g. cloaked affiliate links like /go/product/) are same-site and
		// valid redirect targets on their own. A missing host is not sufficient to prove this:
		// browsers resolve scheme-bearing, authority-less URLs such as "http:evil.com" against the
		// remote host whenever the scheme differs from the page's, so require the scheme to be
		// absent too.
		if ( is_array( $parsed_url ) && empty( $parsed_url['host'] ) && empty( $parsed_url['scheme'] ) && ! empty( $parsed_url['path'] ) ) {
			return true;
		}

		// Network-path references ("//example.com/page") have a host but no scheme, so FILTER_VALIDATE_URL rejects them.
		if ( is_array( $parsed_url ) && ! empty( $parsed_url['host'] ) && empty( $parsed_url['scheme'] ) ) {
			return true;
		}

		// FILTER_VALIDATE_URL accepts scheme:path forms such as "mailto:", which the browser must never be handed here.
		if ( ! in_array( strtolower( (string) ( $parsed_url['scheme'] ?? '' ) ), array( 'http', 'https' ), true ) ) {
			return false;
		}

		// Same-site destinations are legitimate here: force-external and internal target="_blank"
		// links are signed too. The HMAC, not the host, is what prevents open-redirect abuse.
		return (bool) filter_var( $url, FILTER_VALIDATE_URL );
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
	 * @param string $rule_message Optional message from the matching link rule.
	 */
	protected function render_redirect_template( $destination, $rule_message = '' ) {
		$message   = '' !== $rule_message ? $rule_message : (string) wzlw_get_option( 'redirect_message', __( 'You are being redirected to an external site.', 'webberzone-link-warnings' ) );
		$countdown = $this->confirm_only ? 0 : absint( wzlw_get_option( 'redirect_countdown', 5 ) );

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
		$destination = isset( $_GET['url'] ) && is_string( $_GET['url'] ) ? esc_url_raw( wp_unslash( $_GET['url'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$settings  = wzlw_get_settings();
		$countdown = isset( $settings['redirect_countdown'] ) ? absint( $settings['redirect_countdown'] ) : 5;
		if ( $this->confirm_only ) {
			$countdown = 0;
		}

		// Without a countdown the script has nothing to do, so it gets no destination.
		wp_localize_script(
			'wzlw-redirect',
			'wzlwRedirect',
			array(
				'destination' => $this->confirm_only ? '' : $destination,
				'countdown'   => $countdown,
			)
		);
	}

	/**
	 * AJAX handler: return HMAC-signed redirect URLs for a batch of external URLs.
	 *
	 * @since 1.3.0
	 */
	public function ajax_sign_urls() {
		check_ajax_referer( 'wzlw_sign_urls', 'nonce' );

		$raw_urls = isset( $_POST['urls'] ) ? wp_unslash( $_POST['urls'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( ! is_array( $raw_urls ) ) {
			wp_send_json_error( 'invalid_input' );
		}

		// Without a global redirect method, only destinations that a redirect rule covers are signed.
		// Anyone can call this endpoint, so other external destinations are signed confirm-only.
		$method          = wzlw_get_option( 'warning_method', 'inline_modal' );
		$global_redirect = in_array( $method, array( 'redirect', 'inline_redirect' ), true );
		$rules           = Link_Rules::get_rules();

		// Results are keyed by the URL as sent, so the script can find them whatever esc_url_raw() changes.
		$result = array();
		foreach ( $raw_urls as $raw_url ) {
			if ( ! is_string( $raw_url ) ) {
				continue;
			}
			$url = esc_url_raw( $raw_url );
			if ( ! $url || ! $this->is_valid_url( $url ) || ( ! $global_redirect && ! $this->get_redirect_rule( $url, $rules ) ) ) {
				continue;
			}
			$result[ $raw_url ] = self::get_redirect_url( $url, ! self::allows_auto_redirect( $url, $rules ) );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Get redirect URL for a destination.
	 *
	 * @since 1.0.0
	 * @param string $destination  Destination URL.
	 * @param bool   $confirm_only Whether the redirect screen should wait for the visitor to click Continue.
	 * @return string Redirect URL.
	 */
	public static function get_redirect_url( $destination, $confirm_only = false ) {
		$destination = esc_url_raw( $destination );
		$args        = array(
			'url'      => rawurlencode( $destination ),
			'wzlw_sig' => self::sign( $destination, $confirm_only ),
		);

		if ( $confirm_only ) {
			$args['wzlw_confirm'] = '1';
		}

		return add_query_arg( $args, home_url( 'external-redirect/' ) );
	}

	/**
	 * Sign a destination.
	 *
	 * Each kind of signature has its own HMAC input, so one can never be passed off
	 * as another, and none matches the bare-destination signatures from before 1.7.0.
	 *
	 * @since 1.7.0
	 * @param string $destination  Destination URL, as passed through esc_url_raw().
	 * @param bool   $confirm_only Whether the signature is confirm-only.
	 * @return string HMAC signature.
	 */
	private static function sign( $destination, $confirm_only ) {
		return hash_hmac( 'sha256', ( $confirm_only ? 'confirm|' : 'full|v2|' ) . $destination, wp_salt( 'auth' ) );
	}
}
