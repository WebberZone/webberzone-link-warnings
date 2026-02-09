<?php
/**
 * Register Settings.
 *
 * @since 1.0.0
 *
 * @package WebberZone\Better_External_Links
 */

namespace WebberZone\Better_External_Links\Admin;

use WebberZone\Better_External_Links\Util\Hook_Registry;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class to register the settings.
 *
 * @since 1.0.0
 */
class Settings {

	/**
	 * Settings API.
	 *
	 * @since 1.0.0
	 *
	 * @var object Settings API.
	 */
	public $settings_api;

	/**
	 * Settings Page in Admin area.
	 *
	 * @since 1.0.0
	 *
	 * @var string Settings Page.
	 */
	public $settings_page;

	/**
	 * Prefix which is used for creating the unique filters and actions.
	 *
	 * @since 1.0.0
	 *
	 * @var string Prefix.
	 */
	public static $prefix;

	/**
	 * Settings Key.
	 *
	 * @since 1.0.0
	 *
	 * @var string Settings Key.
	 */
	public $settings_key;

	/**
	 * The slug name to refer to this menu by (should be unique for this menu).
	 *
	 * @since 1.0.0
	 *
	 * @var string Menu slug.
	 */
	public $menu_slug;

	/**
	 * Main constructor class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->settings_key = 'wz_bel_settings';
		self::$prefix       = 'wz_bel';
		$this->menu_slug    = 'wz-bel-settings';

		Hook_Registry::add_action( 'admin_menu', array( $this, 'initialise_settings' ) );
		Hook_Registry::add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 11, 2 );
		Hook_Registry::add_filter( 'plugin_action_links_' . plugin_basename( WZ_BEL_PLUGIN_FILE ), array( $this, 'plugin_actions_links' ) );

		Hook_Registry::add_filter( self::$prefix . '_settings_sanitize', array( $this, 'change_settings_on_save' ), 99 );
	}

	/**
	 * Initialise the settings API.
	 *
	 * @since 1.0.0
	 */
	public function initialise_settings() {
		$props = array(
			'default_tab'       => 'general',
			'help_sidebar'      => $this->get_help_sidebar(),
			'help_tabs'         => $this->get_help_tabs(),
			'admin_footer_text' => $this->get_admin_footer_text(),
			'menus'             => $this->get_menus(),
		);

		$args = array(
			'props'               => $props,
			'translation_strings' => $this->get_translation_strings(),
			'settings_sections'   => $this->get_settings_sections(),
			'registered_settings' => $this->get_registered_settings(),
			'upgraded_settings'   => array(),
		);

		$this->settings_api = new Settings\Settings_API( $this->settings_key, self::$prefix, $args );
	}

	/**
	 * Get settings defaults.
	 *
	 * @since 1.0.0
	 *
	 * @return array Default settings.
	 */
	public static function settings_defaults() {
		$defaults = array();

		$settings      = self::get_registered_settings();
		$default_types = array(
			'color',
			'css',
			'csv',
			'file',
			'html',
			'multicheck',
			'number',
			'numbercsv',
			'password',
			'postids',
			'posttypes',
			'radio',
			'radiodesc',
			'repeater',
			'select',
			'sensitive',
			'taxonomies',
			'text',
			'textarea',
			'thumbsizes',
			'url',
			'wysiwyg',
		);

		foreach ( $settings as $section_settings ) {
			foreach ( $section_settings as $setting ) {
				if ( ! isset( $setting['id'] ) ) {
					continue;
				}

				$setting_id    = $setting['id'];
				$setting_type  = $setting['type'] ?? '';
				$default_value = '';

				if ( 'checkbox' === $setting_type ) {
					$default_value = isset( $setting['default'] ) ? (int) (bool) $setting['default'] : 0;
				} elseif ( isset( $setting['default'] ) && in_array( $setting_type, $default_types, true ) ) {
					$default_value = $setting['default'];
				}

				$defaults[ $setting_id ] = $default_value;
			}
		}

		return apply_filters( self::$prefix . '_settings_defaults', $defaults );
	}

	/**
	 * Array containing the translation strings.
	 *
	 * @since 1.0.0
	 *
	 * @return array Translation strings.
	 */
	public function get_translation_strings() {
		$strings = array(
			'page_title'           => esc_html__( 'Better External Links Settings', 'better-external-links' ),
			'menu_title'           => esc_html__( 'Settings', 'better-external-links' ),
			'page_header'          => esc_html__( 'Better External Links Settings', 'better-external-links' ),
			'reset_message'        => esc_html__( 'Settings have been reset to their default values. Reload this page to view the updated settings.', 'better-external-links' ),
			'success_message'      => esc_html__( 'Settings updated.', 'better-external-links' ),
			'save_changes'         => esc_html__( 'Save Changes', 'better-external-links' ),
			'reset_settings'       => esc_html__( 'Reset all settings', 'better-external-links' ),
			'reset_button_confirm' => esc_html__( 'Do you really want to reset all these settings to their default values?', 'better-external-links' ),
			'checkbox_modified'    => esc_html__( 'Modified from default setting', 'better-external-links' ),
			'button_label'         => esc_html__( 'Choose File', 'better-external-links' ),
			'previous_saved'       => esc_html__( 'Previously saved', 'better-external-links' ),
		);

		return apply_filters( self::$prefix . '_translation_strings', $strings );
	}

	/**
	 * Get the admin menus.
	 *
	 * @return array Admin menus.
	 */
	public function get_menus() {
		$menus = array();

		$menus[] = array(
			'settings_page' => true,
			'type'          => 'submenu',
			'parent_slug'   => 'options-general.php',
			'page_title'    => esc_html__( 'Better External Links Settings', 'better-external-links' ),
			'menu_title'    => esc_html__( 'Better External Links', 'better-external-links' ),
			'menu_slug'     => $this->menu_slug,
		);

		return apply_filters( self::$prefix . '_settings_menus', $menus );
	}

	/**
	 * Array containing the settings' sections.
	 *
	 * @since 1.0.0
	 *
	 * @return array Settings sections.
	 */
	public static function get_settings_sections() {
		$settings_sections = array(
			'general'  => esc_html__( 'General', 'better-external-links' ),
			'display'  => esc_html__( 'Display', 'better-external-links' ),
			'advanced' => esc_html__( 'Advanced', 'better-external-links' ),
		);

		return apply_filters( self::$prefix . '_settings_sections', $settings_sections );
	}

	/**
	 * Array containing the settings' fields.
	 *
	 * @since 1.0.0
	 *
	 * @return array Settings fields.
	 */
	public static function get_registered_settings() {
		$settings = array(
			'general'  => self::settings_general(),
			'display'  => self::settings_display(),
			'advanced' => self::settings_advanced(),
		);

		return apply_filters( self::$prefix . '_registered_settings', $settings );
	}

	/**
	 * General settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array General settings.
	 */
	public static function settings_general() {
		$settings = array(
			'warning_method'     => array(
				'id'      => 'warning_method',
				'name'    => esc_html__( 'Warning Method', 'better-external-links' ),
				'desc'    => esc_html__( 'Choose how to warn users about external links.', 'better-external-links' ),
				'type'    => 'radio',
				'default' => 'inline_modal',
				'options' => array(
					'inline'          => esc_html__( 'Inline indicators only', 'better-external-links' ),
					'modal'           => esc_html__( 'Modal dialog', 'better-external-links' ),
					'redirect'        => esc_html__( 'Redirect screen', 'better-external-links' ),
					'inline_modal'    => esc_html__( 'Inline indicators + Modal dialog', 'better-external-links' ),
					'inline_redirect' => esc_html__( 'Inline indicators + Redirect screen', 'better-external-links' ),
				),
			),
			'scope'              => array(
				'id'      => 'scope',
				'name'    => esc_html__( 'Link Scope', 'better-external-links' ),
				'desc'    => esc_html__( 'Which links should be processed.', 'better-external-links' ),
				'type'    => 'radio',
				'default' => 'external',
				'options' => array(
					'external' => esc_html__( 'External links only', 'better-external-links' ),
					'both'     => esc_html__( 'External links and all target="_blank" links', 'better-external-links' ),
				),
			),
			'enabled_post_types' => array(
				'id'      => 'enabled_post_types',
				'name'    => esc_html__( 'Enabled Post Types', 'better-external-links' ),
				'desc'    => esc_html__( 'Select post types where link warnings should be enabled.', 'better-external-links' ),
				'type'    => 'posttypes',
				'default' => 'post,page',
				'options' => 'public',
			),
		);

		return apply_filters( self::$prefix . '_settings_general', $settings );
	}

	/**
	 * Display settings (inline indicators, modal dialog, redirect screen).
	 *
	 * @since 1.0.0
	 *
	 * @return array Display settings.
	 */
	public static function settings_display() {
		$settings = array(
			// Inline Indicators section.
			'inline_header'       => array(
				'id'   => 'inline_header',
				'name' => '<h3>' . esc_html__( 'Inline Indicators', 'better-external-links' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'visual_indicator'    => array(
				'id'      => 'visual_indicator',
				'name'    => esc_html__( 'Visual Indicator', 'better-external-links' ),
				'desc'    => esc_html__( 'Choose what visual indicator to display.', 'better-external-links' ),
				'type'    => 'radio',
				'default' => 'icon',
				'options' => array(
					'icon' => esc_html__( 'Icon (↗)', 'better-external-links' ),
					'text' => esc_html__( 'Text', 'better-external-links' ),
					'both' => esc_html__( 'Icon + text', 'better-external-links' ),
					'none' => esc_html__( 'None (screen reader only)', 'better-external-links' ),
				),
			),
			'indicator_text'      => array(
				'id'      => 'indicator_text',
				'name'    => esc_html__( 'Indicator Text', 'better-external-links' ),
				'desc'    => esc_html__( 'Text displayed next to links (when text indicator is enabled).', 'better-external-links' ),
				'type'    => 'text',
				'default' => __( '(opens in new window)', 'better-external-links' ),
			),
			'screen_reader_text'  => array(
				'id'      => 'screen_reader_text',
				'name'    => esc_html__( 'Screen Reader Text', 'better-external-links' ),
				'desc'    => esc_html__( 'Hidden text for screen readers.', 'better-external-links' ),
				'type'    => 'text',
				'default' => __( 'Opens in a new window', 'better-external-links' ),
			),

			// Modal Dialog section.
			'modal_header'        => array(
				'id'   => 'modal_header',
				'name' => '<h3>' . esc_html__( 'Modal Dialog', 'better-external-links' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'modal_title'         => array(
				'id'      => 'modal_title',
				'name'    => esc_html__( 'Modal Title', 'better-external-links' ),
				'desc'    => esc_html__( 'Title shown in the modal dialog.', 'better-external-links' ),
				'type'    => 'text',
				'default' => __( 'You are leaving this site', 'better-external-links' ),
			),
			'modal_message'       => array(
				'id'      => 'modal_message',
				'name'    => esc_html__( 'Modal Message', 'better-external-links' ),
				'desc'    => esc_html__( 'Message shown in the modal dialog.', 'better-external-links' ),
				'type'    => 'textarea',
				'default' => __( 'You are about to visit an external website. Continue?', 'better-external-links' ),
			),
			'modal_continue_text' => array(
				'id'      => 'modal_continue_text',
				'name'    => esc_html__( 'Continue Button Text', 'better-external-links' ),
				'desc'    => esc_html__( 'Text for the continue button.', 'better-external-links' ),
				'type'    => 'text',
				'default' => __( 'Continue', 'better-external-links' ),
			),
			'modal_cancel_text'   => array(
				'id'      => 'modal_cancel_text',
				'name'    => esc_html__( 'Cancel Button Text', 'better-external-links' ),
				'desc'    => esc_html__( 'Text for the cancel button.', 'better-external-links' ),
				'type'    => 'text',
				'default' => __( 'Cancel', 'better-external-links' ),
			),

			// Redirect Screen section.
			'redirect_header'     => array(
				'id'   => 'redirect_header',
				'name' => '<h3>' . esc_html__( 'Redirect Screen', 'better-external-links' ) . '</h3>',
				'desc' => '',
				'type' => 'header',
			),
			'redirect_message'    => array(
				'id'      => 'redirect_message',
				'name'    => esc_html__( 'Redirect Message', 'better-external-links' ),
				'desc'    => esc_html__( 'Message shown on the redirect page.', 'better-external-links' ),
				'type'    => 'textarea',
				'default' => __( 'You are being redirected to an external site.', 'better-external-links' ),
			),
			'redirect_countdown'  => array(
				'id'      => 'redirect_countdown',
				'name'    => esc_html__( 'Redirect Countdown', 'better-external-links' ),
				'desc'    => esc_html__( 'Number of seconds before the automatic redirect takes place. Set to 0 to disable auto-redirect.', 'better-external-links' ),
				'type'    => 'number',
				'default' => 5,
				'min'     => 0,
				'max'     => 60,
				'step'    => 1,
			),
		);

		/**
		 * Filter the display settings.
		 *
		 * @since 1.0.0
		 *
		 * @param array $settings Display settings.
		 */
		return apply_filters( self::$prefix . '_settings_display', $settings );
	}

	/**
	 * Advanced settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array Advanced settings.
	 */
	public static function settings_advanced() {
		$settings = array(
			'excluded_domains' => array(
				'id'      => 'excluded_domains',
				'name'    => esc_html__( 'Excluded Domains', 'better-external-links' ),
				'desc'    => esc_html__( 'Enter one domain per line (e.g., example.com). These domains will be treated as internal.', 'better-external-links' ),
				'type'    => 'textarea',
				'default' => '',
			),
		);

		return apply_filters( self::$prefix . '_settings_advanced', $settings );
	}

	/**
	 * Modify settings on save.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings Settings array.
	 * @return array Modified settings array.
	 */
	public function change_settings_on_save( $settings ) {
		// Flush rewrite rules so the redirect endpoint is registered.
		flush_rewrite_rules();

		return $settings;
	}

	/**
	 * Get the help sidebar.
	 *
	 * @since 1.0.0
	 *
	 * @return string Help sidebar content.
	 */
	public function get_help_sidebar() {
		$help_sidebar =
			'<p><strong>' . esc_html__( 'For more information:', 'better-external-links' ) . '</strong></p>' .
			'<p><a href="https://webberzone.com/plugins/better-external-links/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Plugin Homepage', 'better-external-links' ) . '</a></p>' .
			'<p><a href="https://webberzone.com/support/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Support', 'better-external-links' ) . '</a></p>';

		return apply_filters( self::$prefix . '_settings_help_sidebar', $help_sidebar );
	}

	/**
	 * Get the help tabs.
	 *
	 * @since 1.0.0
	 *
	 * @return array Help tabs.
	 */
	public function get_help_tabs() {
		$help_tabs = array(
			array(
				'id'      => 'wz-bel-settings-general',
				'title'   => esc_html__( 'General', 'better-external-links' ),
				'content' =>
					'<p>' . esc_html__( 'Configure the general behavior of the plugin.', 'better-external-links' ) . '</p>' .
					'<p>' . esc_html__( 'Choose your preferred warning method and which links should be processed.', 'better-external-links' ) . '</p>',
			),
		);

		return apply_filters( self::$prefix . '_settings_help_tabs', $help_tabs );
	}

	/**
	 * Get the admin footer text.
	 *
	 * @since 1.0.0
	 *
	 * @return string Admin footer text.
	 */
	public function get_admin_footer_text() {
		$footer_text = sprintf(
			/* translators: 1: Better External Links link, 2: Plugin rating link */
			__( 'Thank you for using <a href="%1$s" target="_blank" rel="noopener noreferrer">Better External Links</a>! Please <a href="%2$s" target="_blank" rel="noopener noreferrer">rate us</a> on WordPress.org', 'better-external-links' ),
			'https://webberzone.com/plugins/better-external-links/',
			'https://wordpress.org/support/plugin/better-external-links/reviews/#new-post'
		);

		return $footer_text;
	}

	/**
	 * Add plugin action links.
	 *
	 * @since 1.0.0
	 *
	 * @param array $links Array of links.
	 * @return array Modified array of links.
	 */
	public function plugin_actions_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->menu_slug ) . '">' . esc_html__( 'Settings', 'better-external-links' ) . '</a>',
			),
			$links
		);
	}

	/**
	 * Add plugin row meta.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $links Array of links.
	 * @param string $file  Plugin file.
	 * @return array Modified array of links.
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( false !== strpos( $file, 'better-external-links.php' ) ) {
			$new_links = array(
				'support' => '<a href="https://webberzone.com/support/" target="_blank">' . esc_html__( 'Support', 'better-external-links' ) . '</a>',
				'donate'  => '<a href="https://webberzone.com/donate/" target="_blank">' . esc_html__( 'Donate', 'better-external-links' ) . '</a>',
			);

			$links = array_merge( $links, $new_links );
		}

		return $links;
	}
}
