<?php
/**
 * Admin class.
 *
 * @since 1.0.0
 *
 * @package WebberZone\Better_External_Links\Admin
 */

namespace WebberZone\Better_External_Links\Admin;

/**
 * Class to register the Better External Links Admin Area.
 *
 * @since 1.0.0
 */
class Admin {

	/**
	 * Settings API.
	 *
	 * @since 1.0.0
	 *
	 * @var Settings Settings API.
	 */
	public Settings $settings;

	/**
	 * Activator class.
	 *
	 * @since 1.0.0
	 *
	 * @var Activator Activator class.
	 */
	public Activator $activator;

	/**
	 * Settings wizard.
	 *
	 * @since 1.0.0
	 *
	 * @var Settings_Wizard|null Settings wizard instance.
	 */
	public ?Settings_Wizard $settings_wizard = null;

	/**
	 * Admin banner helper instance.
	 *
	 * @since 1.0.0
	 *
	 * @var Admin_Banner
	 */
	public Admin_Banner $admin_banner;

	/**
	 * Main constructor class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Initialise admin classes.
		$this->settings        = new Settings();
		$this->activator       = new Activator();
		$this->settings_wizard = new Settings_Wizard();
		$this->admin_banner    = new Admin_Banner( $this->get_admin_banner_config() );
	}

	/**
	 * Retrieve the configuration array for the admin banner.
	 *
	 * @since 1.0.0
	 *
	 * @return array<string, mixed>
	 */
	private function get_admin_banner_config(): array {
		return array(
			'capability' => 'manage_options',
			'prefix'     => 'wz-bel',
			'screen_ids' => array(
				'settings_page_wz-bel-settings',
				'options-general_page_wz-bel-settings',
			),
			'page_slugs' => array(
				'wz-bel-settings',
			),
			'strings'    => array(
				'region_label' => esc_html__( 'Better External Links quick links', 'better-external-links' ),
				'nav_label'    => esc_html__( 'Better External Links admin shortcuts', 'better-external-links' ),
				'eyebrow'      => esc_html__( 'Better External Links', 'better-external-links' ),
				'title'        => esc_html__( 'Enhance your site’s accessibility and user experience.', 'better-external-links' ),
				'text'         => esc_html__( 'Configure link warnings, visual indicators, and redirect screens to keep your users informed.', 'better-external-links' ),
			),
			'sections'   => array(
				'settings' => array(
					'label'      => esc_html__( 'Settings', 'better-external-links' ),
					'url'        => admin_url( 'options-general.php?page=wz-bel-settings' ),
					'screen_ids' => array( 'settings_page_wz-bel-settings', 'options-general_page_wz-bel-settings' ),
					'page_slugs' => array( 'wz-bel-settings' ),
				),
				'support'  => array(
					'label'  => esc_html__( 'Support', 'better-external-links' ),
					'url'    => 'https://webberzone.com/support/',
					'type'   => 'secondary',
					'target' => '_blank',
					'rel'    => 'noopener noreferrer',
				),
				'plugins'  => array(
					'label'  => esc_html__( 'More Plugins', 'better-external-links' ),
					'url'    => 'https://webberzone.com/plugins/',
					'type'   => 'secondary',
					'target' => '_blank',
					'rel'    => 'noopener noreferrer',
				),
			),
		);
	}
}
