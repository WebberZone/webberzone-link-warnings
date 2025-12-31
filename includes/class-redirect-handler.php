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

		if ( 'redirect' !== $method ) {
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
	private function is_valid_url( $url ) {
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
	 * Render redirect template.
	 *
	 * @since 1.0.0
	 * @param string $destination Destination URL.
	 */
	private function render_redirect_template( $destination ) {
		$settings = wzbel_get_settings();
		$message  = isset( $settings['redirect_message'] ) ? $settings['redirect_message'] : __( 'You are being redirected to an external site.', 'better-external-links' );

		// Parse destination for display.
		$parsed_url = wp_parse_url( $destination );
		$domain     = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';

		get_header();
		?>
		<div class="wz-ela-redirect-container">
			<div class="wz-ela-redirect-content">
				<div class="wz-ela-redirect-icon">
					<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<polyline points="15 3 21 3 21 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<line x1="10" y1="14" x2="21" y2="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</div>
				
				<h1 class="wz-ela-redirect-title">
					<?php esc_html_e( 'Leaving this site', 'better-external-links' ); ?>
				</h1>
				
				<p class="wz-ela-redirect-message">
					<?php echo esc_html( $message ); ?>
				</p>
				
				<div class="wz-ela-redirect-url-container">
					<p class="wz-ela-redirect-url-label">
						<?php esc_html_e( 'Destination:', 'better-external-links' ); ?>
					</p>
					<p class="wz-ela-redirect-url">
						<strong><?php echo esc_html( $domain ); ?></strong>
					</p>
					<p class="wz-ela-redirect-url-full">
						<?php echo esc_html( $destination ); ?>
					</p>
				</div>
				
				<div class="wz-ela-redirect-actions">
					<a href="<?php echo esc_url( $destination ); ?>" class="wz-ela-redirect-button wz-ela-redirect-continue" rel="noopener noreferrer">
						<?php esc_html_e( 'Continue to site', 'better-external-links' ); ?>
						<span aria-hidden="true">→</span>
					</a>
					<a href="<?php echo esc_url( wp_get_referer() ? wp_get_referer() : home_url() ); ?>" class="wz-ela-redirect-button wz-ela-redirect-back">
						<?php esc_html_e( 'Go back', 'better-external-links' ); ?>
					</a>
				</div>
				
				<p class="wz-ela-redirect-countdown" data-countdown="5">
					<?php
					/* translators: %s: countdown number */
					printf( esc_html__( 'Redirecting automatically in %s seconds...', 'better-external-links' ), '<span class="wz-ela-countdown-number">5</span>' );
					?>
				</p>
			</div>
		</div>
		<?php
		get_footer();
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

		wp_localize_script(
			'wz-ela-redirect',
			'wzElaRedirect',
			array(
				'destination' => $destination,
				'countdown'   => 5,
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