<?php
/**
 * Redirect screen template.
 *
 * @since 1.0.0
 *
 * @package WebberZone\Link_Warnings
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
<div class="wzlw-redirect-container">
	<div class="wzlw-redirect-content">
		<div class="wzlw-redirect-icon">
			<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<polyline points="15 3 21 3 21 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<line x1="10" y1="14" x2="21" y2="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
		</div>

		<h1 class="wzlw-redirect-title">
			<?php esc_html_e( 'Leaving this site', 'webberzone-link-warnings' ); ?>
		</h1>

		<p class="wzlw-redirect-message">
			<?php echo esc_html( $message ); ?>
		</p>

		<div class="wzlw-redirect-url-container">
			<p class="wzlw-redirect-url-label">
				<?php esc_html_e( 'Destination:', 'webberzone-link-warnings' ); ?>
			</p>
			<p class="wzlw-redirect-url">
				<strong><?php echo esc_html( $domain ); ?></strong>
			</p>
			<p class="wzlw-redirect-url-full">
				<?php echo esc_html( $destination ); ?>
			</p>
		</div>

		<div class="wzlw-redirect-actions">
			<a href="<?php echo esc_url( $destination ); ?>" class="wzlw-redirect-button wzlw-redirect-continue" rel="noopener noreferrer">
				<?php esc_html_e( 'Continue to site', 'webberzone-link-warnings' ); ?>
				<span aria-hidden="true">→</span>
			</a>
			<a href="<?php echo esc_url( wp_get_referer() ? wp_get_referer() : home_url() ); ?>" class="wzlw-redirect-button wzlw-redirect-back">
				<?php esc_html_e( 'Go back', 'webberzone-link-warnings' ); ?>
			</a>
		</div>

		<?php if ( $countdown > 0 ) : ?>
			<?php
			$wzlw_countdown_str = (string) $countdown; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			/* translators: %s: countdown number */
			$wzlw_countdown_text = esc_html__( 'Redirecting automatically in %s seconds...', 'webberzone-link-warnings' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

			$wzlw_countdown_markup = sprintf( // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				$wzlw_countdown_text,
				'<span class="wzlw-countdown-number">' . esc_html( $wzlw_countdown_str ) . '</span>'
			);
			?>
			<p class="wzlw-redirect-countdown" data-countdown="<?php echo esc_attr( $wzlw_countdown_str ); ?>" aria-live="polite">
				<?php echo wp_kses( $wzlw_countdown_markup, array( 'span' => array( 'class' => true ) ) ); ?>
			</p>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
