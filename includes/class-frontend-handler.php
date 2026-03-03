<?php
/**
 * Frontend Handler class.
 *
 * Handles frontend JavaScript and modal functionality.
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
 * Frontend Handler class.
 *
 * @since 1.0.0
 */
class Frontend_Handler {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		Hook_Registry::add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		Hook_Registry::add_action( 'wp_footer', array( $this, 'render_modal' ) );
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets() {
		$settings = wzlw_get_settings();
		$method   = isset( $settings['warning_method'] ) ? $settings['warning_method'] : 'inline';

		$rtl_suffix = is_rtl() ? '-rtl' : '';
		$min_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// Enqueue styles for all methods.
		wp_enqueue_style(
			'wzlw-frontend',
			WZLW_PLUGIN_URL . 'includes/assets/css/frontend' . $rtl_suffix . $min_suffix . '.css',
			array(),
			WZLW_VERSION
		);

		// Enqueue JavaScript for modal and redirect methods.
		if ( in_array( $method, array( 'modal', 'inline_modal', 'redirect', 'inline_redirect' ), true ) ) {
			wp_enqueue_script(
				'wzlw-modal',
				WZLW_PLUGIN_URL . 'includes/admin/js/modal' . $min_suffix . '.js',
				array(),
				WZLW_VERSION,
				true
			);

			// Pass settings to JavaScript.
			wp_localize_script(
				'wzlw-modal',
				'wzlwSettings',
				array(
					'modalTitle'    => isset( $settings['modal_title'] ) ? $settings['modal_title'] : __( 'You are leaving this site', 'webberzone-link-warnings' ),
					'modalMessage'  => isset( $settings['modal_message'] ) ? $settings['modal_message'] : __( 'You are about to visit an external website. Continue?', 'webberzone-link-warnings' ),
					'continueText'  => isset( $settings['modal_continue_text'] ) ? $settings['modal_continue_text'] : __( 'Continue', 'webberzone-link-warnings' ),
					'cancelText'    => isset( $settings['modal_cancel_text'] ) ? $settings['modal_cancel_text'] : __( 'Cancel', 'webberzone-link-warnings' ),
					'warningMethod' => $method,
				)
			);
		}
	}

	/**
	 * Render modal HTML.
	 *
	 * @since 1.0.0
	 */
	public function render_modal() {
		$settings = wzlw_get_settings();
		$method   = isset( $settings['warning_method'] ) ? $settings['warning_method'] : 'inline';

		if ( ! in_array( $method, array( 'modal', 'inline_modal' ), true ) ) {
			return;
		}

		?>
		<div id="wzlw-modal" class="wzlw-modal" role="dialog" aria-modal="true" aria-labelledby="wzlw-modal-title" aria-describedby="wzlw-modal-message" hidden>
			<div class="wzlw-modal-overlay" data-wzlw-close role="presentation"></div>
			<div class="wzlw-modal-container">
				<button type="button" class="wzlw-modal-close-btn" data-wzlw-close aria-label="<?php esc_attr_e( 'Close dialog', 'webberzone-link-warnings' ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
				<div class="wzlw-modal-content">
					<h2 id="wzlw-modal-title" class="wzlw-modal-title"></h2>
					<div id="wzlw-modal-message" class="wzlw-modal-message"></div>
					<div class="wzlw-modal-url"></div>
					<div class="wzlw-modal-actions">
						<button type="button" class="wzlw-modal-button wzlw-modal-cancel" data-wzlw-close></button>
						<button type="button" class="wzlw-modal-button wzlw-modal-continue" data-wzlw-continue></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}