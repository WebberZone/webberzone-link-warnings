<?php
/**
 * Settings Wizard for Better External Links.
 *
 * Provides a guided setup experience for new users.
 *
 * @since 1.0.0
 *
 * @package WebberZone\Better_External_Links
 */

namespace WebberZone\Better_External_Links\Admin;

use WebberZone\Better_External_Links\Util\Hook_Registry;
use WebberZone\Better_External_Links\Admin\Settings\Settings_Wizard_API;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Settings Wizard class for Better External Links.
 *
 * @since 1.0.0
 */
class Settings_Wizard extends Settings_Wizard_API {

	/**
	 * Settings page URL.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $settings_page_url;

	/**
	 * Main constructor class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$settings_key = 'wz_bel_settings';
		$prefix       = 'wz_bel';

		$this->settings_page_url = admin_url( 'options-general.php?page=better-external-links' );

		$args = array(
			'steps'               => $this->get_wizard_steps(),
			'translation_strings' => $this->get_translation_strings(),
			'page_slug'           => 'wz_bel_wizard',
			'menu_args'           => array(
				'parent'     => 'options-general.php',
				'capability' => 'manage_options',
			),
		);

		parent::__construct( $settings_key, $prefix, $args );

		$this->additional_hooks();
	}

	/**
	 * Additional hooks specific to Better External Links.
	 *
	 * @since 1.0.0
	 */
	protected function additional_hooks() {
		Hook_Registry::add_action( 'wz_bel_activate', array( $this, 'trigger_wizard_on_activation' ) );
		Hook_Registry::add_action( 'admin_init', array( $this, 'register_wizard_notice' ) );
	}

	/**
	 * Get the skip wizard link URL.
	 *
	 * @since 1.0.0
	 *
	 * @return string Skip wizard link URL.
	 */
	protected function get_skip_link_url() {
		return $this->settings_page_url;
	}

	/**
	 * Get wizard steps configuration.
	 *
	 * @since 1.0.0
	 *
	 * @return array Wizard steps.
	 */
	public function get_wizard_steps() {
		return array(
			'welcome' => array(
				'title'   => esc_html__( 'Welcome', 'better-external-links' ),
				'content' => array( $this, 'welcome_content' ),
				'handler' => '',
			),
			'basic'   => array(
				'title'   => esc_html__( 'Basic Settings', 'better-external-links' ),
				'content' => array( $this, 'basic_content' ),
				'handler' => array( $this, 'basic_save' ),
			),
			'content' => array(
				'title'   => esc_html__( 'Content Settings', 'better-external-links' ),
				'content' => array( $this, 'content_content' ),
				'handler' => array( $this, 'content_save' ),
			),
			'modal'   => array(
				'title'   => esc_html__( 'Modal Settings', 'better-external-links' ),
				'content' => array( $this, 'modal_content' ),
				'handler' => array( $this, 'modal_save' ),
			),
			'ready'   => array(
				'title'   => esc_html__( 'Ready!', 'better-external-links' ),
				'content' => array( $this, 'ready_content' ),
				'handler' => array( $this, 'ready_save' ),
			),
		);
	}

	/**
	 * Get translation strings.
	 *
	 * @since 1.0.0
	 *
	 * @return array Translation strings.
	 */
	public function get_translation_strings() {
		return array(
			'branding'           => esc_html__( 'Better External Links', 'better-external-links' ),
			'welcome_title'      => esc_html__( 'Welcome to Better External Links!', 'better-external-links' ),
			'welcome_subtitle'   => esc_html__( 'Thank you for installing! This quick setup wizard will help you configure the basic settings.', 'better-external-links' ),
			'btn_start'          => esc_html__( 'Let\'s go!', 'better-external-links' ),
			'btn_skip'           => esc_html__( 'Not right now', 'better-external-links' ),
			'btn_next'           => esc_html__( 'Continue', 'better-external-links' ),
			'btn_previous'       => esc_html__( 'Previous', 'better-external-links' ),
			'btn_finish'         => esc_html__( 'Finish', 'better-external-links' ),
			'completed_title'    => esc_html__( 'You\'re all set!', 'better-external-links' ),
			'completed_subtitle' => esc_html__( 'Your plugin is now configured and ready to use.', 'better-external-links' ),
		);
	}

	/**
	 * Welcome step content.
	 *
	 * @since 1.0.0
	 */
	public function welcome_content() {
		?>
		<p><?php esc_html_e( 'This wizard will walk you through:', 'better-external-links' ); ?></p>
		<ul>
			<li><?php esc_html_e( 'Choosing your warning method', 'better-external-links' ); ?></li>
			<li><?php esc_html_e( 'Configuring visual indicators', 'better-external-links' ); ?></li>
			<li><?php esc_html_e( 'Setting up modal dialogs', 'better-external-links' ); ?></li>
		</ul>
		<p><strong><?php esc_html_e( 'No worries - you can always change these settings later in the plugin settings page.', 'better-external-links' ); ?></strong></p>
		<?php
	}

	/**
	 * Basic settings content.
	 *
	 * @since 1.0.0
	 */
	public function basic_content() {
		$settings = $this->settings_form->get_settings();
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="warning_method"><?php esc_html_e( 'Warning Method', 'better-external-links' ); ?></label>
				</th>
				<td>
					<?php
					$methods = array(
						'inline'       => esc_html__( 'Inline indicators only', 'better-external-links' ),
						'modal'        => esc_html__( 'Modal dialog', 'better-external-links' ),
						'redirect'     => esc_html__( 'Redirect screen', 'better-external-links' ),
						'inline_modal' => esc_html__( 'Inline + modal (for external only)', 'better-external-links' ),
					);
					foreach ( $methods as $value => $label ) {
						?>
						<label>
							<input type="radio" name="warning_method" value="<?php echo esc_attr( $value ); ?>" <?php checked( $settings['warning_method'], $value ); ?> required>
							<?php echo esc_html( $label ); ?>
						</label><br>
						<?php
					}
					?>
					<p class="description"><?php esc_html_e( 'Choose how to warn users about external links.', 'better-external-links' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="scope"><?php esc_html_e( 'Link Scope', 'better-external-links' ); ?></label>
				</th>
				<td>
					<?php
					$scopes = array(
						'external'     => esc_html__( 'External links only', 'better-external-links' ),
						'target_blank' => esc_html__( 'All target="_blank" links', 'better-external-links' ),
						'both'         => esc_html__( 'Both (with different treatments)', 'better-external-links' ),
					);
					foreach ( $scopes as $value => $label ) {
						?>
						<label>
							<input type="radio" name="scope" value="<?php echo esc_attr( $value ); ?>" <?php checked( $settings['scope'], $value ); ?> required>
							<?php echo esc_html( $label ); ?>
						</label><br>
						<?php
					}
					?>
					<p class="description"><?php esc_html_e( 'Which links should be processed.', 'better-external-links' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Basic settings save handler.
	 *
	 * @since 1.0.0
	 */
	public function basic_save() {
		// Nonce is verified by the parent class.
		// Nonce is verified by the parent class before calling this handler.
		if ( isset( $_POST['warning_method'] ) && isset( $_POST['scope'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Nonce is verified by the parent class before calling this handler.
			$settings = $this->settings_form->get_settings();
			// Nonce is verified by the parent class before calling this handler.
			$settings['warning_method'] = sanitize_key( $_POST['warning_method'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Nonce is verified by the parent class before calling this handler.
			$settings['scope'] = sanitize_key( $_POST['scope'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$this->settings_form->update_settings( $settings );
		}
	}

	/**
	 * Content settings content.
	 *
	 * @since 1.0.0
	 */
	public function content_content() {
		$settings = $this->settings_form->get_settings();
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="visual_indicator"><?php esc_html_e( 'Visual Indicator', 'better-external-links' ); ?></label>
				</th>
				<td>
					<?php
					$indicators = array(
						'icon' => esc_html__( 'Icon (↗)', 'better-external-links' ),
						'text' => esc_html__( 'Text', 'better-external-links' ),
						'both' => esc_html__( 'Icon + text', 'better-external-links' ),
						'none' => esc_html__( 'None (screen reader only)', 'better-external-links' ),
					);
					foreach ( $indicators as $value => $label ) {
						?>
						<label>
							<input type="radio" name="visual_indicator" value="<?php echo esc_attr( $value ); ?>" <?php checked( $settings['visual_indicator'], $value ); ?> required>
							<?php echo esc_html( $label ); ?>
						</label><br>
						<?php
					}
					?>
					<p class="description"><?php esc_html_e( 'Choose what visual indicator to display.', 'better-external-links' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="indicator_text"><?php esc_html_e( 'Indicator Text', 'better-external-links' ); ?></label>
				</th>
				<td>
					<input type="text" id="indicator_text" name="indicator_text" value="<?php echo esc_attr( $settings['indicator_text'] ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Text displayed next to links (when text indicator is enabled).', 'better-external-links' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="screen_reader_text"><?php esc_html_e( 'Screen Reader Text', 'better-external-links' ); ?></label>
				</th>
				<td>
					<input type="text" id="screen_reader_text" name="screen_reader_text" value="<?php echo esc_attr( $settings['screen_reader_text'] ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Hidden text for screen readers.', 'better-external-links' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Content settings save handler.
	 *
	 * @since 1.0.0
	 */
	public function content_save() {
		// Nonce is verified by the parent class.
		// Nonce is verified by the parent class before calling this handler.
		if ( isset( $_POST['visual_indicator'] ) && isset( $_POST['indicator_text'] ) && isset( $_POST['screen_reader_text'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Nonce is verified by the parent class before calling this handler.
			$settings = $this->settings_form->get_settings();
			// Nonce is verified by the parent class before calling this handler.
			$settings['visual_indicator'] = sanitize_key( $_POST['visual_indicator'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Nonce is verified by the parent class before calling this handler.
			$settings['indicator_text'] = sanitize_text_field( wp_unslash( $_POST['indicator_text'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Nonce is verified by the parent class before calling this handler.
			$settings['screen_reader_text'] = sanitize_text_field( wp_unslash( $_POST['screen_reader_text'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$this->settings_form->update_settings( $settings );
		}
	}

	/**
	 * Modal settings content.
	 *
	 * @since 1.0.0
	 */
	public function modal_content() {
		$settings = $this->settings_form->get_settings();
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="modal_title"><?php esc_html_e( 'Modal Title', 'better-external-links' ); ?></label>
				</th>
				<td>
					<input type="text" id="modal_title" name="modal_title" value="<?php echo esc_attr( $settings['modal_title'] ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Title shown in the modal dialog.', 'better-external-links' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="modal_message"><?php esc_html_e( 'Modal Message', 'better-external-links' ); ?></label>
				</th>
				<td>
					<textarea id="modal_message" name="modal_message" rows="3" class="large-text"><?php echo esc_textarea( $settings['modal_message'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Message shown in the modal dialog.', 'better-external-links' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="modal_continue_text"><?php esc_html_e( 'Continue Button Text', 'better-external-links' ); ?></label>
				</th>
				<td>
					<input type="text" id="modal_continue_text" name="modal_continue_text" value="<?php echo esc_attr( $settings['modal_continue_text'] ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Text for the continue button.', 'better-external-links' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="modal_cancel_text"><?php esc_html_e( 'Cancel Button Text', 'better-external-links' ); ?></label>
				</th>
				<td>
					<input type="text" id="modal_cancel_text" name="modal_cancel_text" value="<?php echo esc_attr( $settings['modal_cancel_text'] ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Text for the cancel button.', 'better-external-links' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Modal settings save handler.
	 *
	 * @since 1.0.0
	 */
	public function modal_save() {
		// Nonce is verified by the parent class.
		// Nonce is verified by the parent class before calling this handler.
		if ( isset( $_POST['modal_title'] ) && isset( $_POST['modal_message'] ) && isset( $_POST['modal_continue_text'] ) && isset( $_POST['modal_cancel_text'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Nonce is verified by the parent class before calling this handler.
			$settings = $this->settings_form->get_settings();
			// Nonce is verified by the parent class before calling this handler.
			$settings['modal_title'] = sanitize_text_field( wp_unslash( $_POST['modal_title'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Nonce is verified by the parent class before calling this handler.
			$settings['modal_message'] = sanitize_textarea_field( wp_unslash( $_POST['modal_message'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Nonce is verified by the parent class before calling this handler.
			$settings['modal_continue_text'] = sanitize_text_field( wp_unslash( $_POST['modal_continue_text'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Nonce is verified by the parent class before calling this handler.
			$settings['modal_cancel_text'] = sanitize_text_field( wp_unslash( $_POST['modal_cancel_text'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$this->settings_form->update_settings( $settings );
		}
	}

	/**
	 * Ready step content.
	 *
	 * @since 1.0.0
	 */
	public function ready_content() {
		?>
		<div class="wz-bel-setup-features">
			<h3><?php esc_html_e( 'What\'s next?', 'better-external-links' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'Your external links will now be automatically processed according to your settings', 'better-external-links' ); ?></li>
				<li><?php esc_html_e( 'You can fine-tune all settings in the plugin settings page', 'better-external-links' ); ?></li>
				<li><?php esc_html_e( 'Check out our documentation for advanced features and tips', 'better-external-links' ); ?></li>
			</ul>
		</div>
		
		<div class="wz-bel-setup-support">
			<h3><?php esc_html_e( 'Need help?', 'better-external-links' ); ?></h3>
			<p><?php esc_html_e( 'If you have any questions or need assistance, please don\'t hesitate to reach out to our support team.', 'better-external-links' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Ready step save handler.
	 *
	 * @since 1.0.0
	 */
	public function ready_save() {
		update_option( 'wz_bel_setup_completed', true );
	}
}
