<?php
/**
 * Frontend Handler class.
 *
 * Handles frontend JavaScript and modal functionality.
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
		$settings = wzbel_get_settings();
		$method   = isset( $settings['warning_method'] ) ? $settings['warning_method'] : 'inline';

		$rtl_suffix = is_rtl() ? '-rtl' : '';
		$min_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// Enqueue styles for all methods.
		wp_enqueue_style(
			'wz-bel-frontend',
			WZ_BEL_PLUGIN_URL . 'includes/assets/css/frontend' . $rtl_suffix . $min_suffix . '.css',
			array(),
			WZ_BEL_VERSION
		);

		// Enqueue JavaScript for modal and redirect methods.
		if ( in_array( $method, array( 'modal', 'inline_modal', 'redirect', 'inline_redirect' ), true ) ) {
			wp_enqueue_script(
				'wz-bel-modal',
				WZ_BEL_PLUGIN_URL . 'includes/admin/js/modal' . $min_suffix . '.js',
				array(),
				WZ_BEL_VERSION,
				true
			);

			// Pass settings to JavaScript.
			wp_localize_script(
				'wz-bel-modal',
				'wzElaSettings',
				array(
					'modalTitle'    => isset( $settings['modal_title'] ) ? $settings['modal_title'] : __( 'You are leaving this site', 'better-external-links' ),
					'modalMessage'  => isset( $settings['modal_message'] ) ? $settings['modal_message'] : __( 'You are about to visit an external website. Continue?', 'better-external-links' ),
					'continueText'  => isset( $settings['modal_continue_text'] ) ? $settings['modal_continue_text'] : __( 'Continue', 'better-external-links' ),
					'cancelText'    => isset( $settings['modal_cancel_text'] ) ? $settings['modal_cancel_text'] : __( 'Cancel', 'better-external-links' ),
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
		$settings = wzbel_get_settings();
		$method   = isset( $settings['warning_method'] ) ? $settings['warning_method'] : 'inline';

		if ( ! in_array( $method, array( 'modal', 'inline_modal' ), true ) ) {
			return;
		}

		?>
		<div id="wz-ela-modal" class="wz-ela-modal" role="dialog" aria-modal="true" aria-labelledby="wz-ela-modal-title" aria-describedby="wz-ela-modal-message" hidden>
			<div class="wz-ela-modal-overlay" data-wz-ela-close></div>
			<div class="wz-ela-modal-container">
				<button type="button" class="wz-ela-modal-close-btn" data-wz-ela-close aria-label="<?php esc_attr_e( 'Close dialog', 'better-external-links' ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
				<div class="wz-ela-modal-content">
					<h2 id="wz-ela-modal-title" class="wz-ela-modal-title"></h2>
					<div id="wz-ela-modal-message" class="wz-ela-modal-message"></div>
					<div class="wz-ela-modal-url"></div>
					<div class="wz-ela-modal-actions">
						<button type="button" class="wz-ela-modal-button wz-ela-modal-cancel" data-wz-ela-close></button>
						<button type="button" class="wz-ela-modal-button wz-ela-modal-continue" data-wz-ela-continue></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}