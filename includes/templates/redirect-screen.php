<?php
/**
 * Redirect screen template.
 *
 * @since 1.0.0
 *
 * @package WebberZone\Better_External_Links
 *
 * @var string $destination Destination URL.
 * @var string $message     Redirect message.
 * @var string $domain      Destination domain.
 * @var int    $countdown   Countdown in seconds. 0 disables auto redirect.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

		<?php if ( $countdown > 0 ) : ?>
			<?php
			$wz_bel_countdown_str = (string) $countdown; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			/* translators: %s: countdown number */
			$wz_bel_countdown_text = esc_html__( 'Redirecting automatically in %s seconds...', 'better-external-links' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

			$wz_bel_countdown_markup = sprintf( // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				$wz_bel_countdown_text,
				'<span class="wz-ela-countdown-number">' . esc_html( $wz_bel_countdown_str ) . '</span>'
			);
			?>
			<p class="wz-ela-redirect-countdown" data-countdown="<?php echo esc_attr( $wz_bel_countdown_str ); ?>" aria-live="polite">
				<?php echo wp_kses( $wz_bel_countdown_markup, array( 'span' => array( 'class' => true ) ) ); ?>
			</p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
