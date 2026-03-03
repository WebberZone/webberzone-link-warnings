<?php
/**
 * Admin class.
 *
 * @since 1.0.0
 *
 * @package WebberZone\Link_Warnings\Admin
 */

namespace WebberZone\Link_Warnings\Admin;

/**
 * Class to register the WebberZone Link Warnings Admin Area.
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
			'prefix'     => 'wzlw',
			'screen_ids' => array(
				'settings_page_wzlw-settings',
				'options-general_page_wzlw-settings',
			),
			'page_slugs' => array(
				'wzlw-settings',
			),
			'strings'    => array(
				'region_label' => esc_html__( 'WebberZone Link Warnings quick links', 'webberzone-link-warnings' ),
				'nav_label'    => esc_html__( 'WebberZone Link Warnings admin shortcuts', 'webberzone-link-warnings' ),
				'eyebrow'      => esc_html__( 'WebberZone Link Warnings', 'webberzone-link-warnings' ),
				'title'        => esc_html__( 'Enhance your site\'s accessibility and user experience.', 'webberzone-link-warnings' ),
				'text'         => esc_html__( 'Configure link warnings, visual indicators, and redirect screens to keep your users informed.', 'webberzone-link-warnings' ),
			),
			'sections'   => array(
				'settings' => array(
					'label'      => esc_html__( 'Settings', 'webberzone-link-warnings' ),
					'url'        => admin_url( 'options-general.php?page=wzlw-settings' ),
					'screen_ids' => array( 'settings_page_wzlw-settings', 'options-general_page_wzlw-settings' ),
					'page_slugs' => array( 'wzlw-settings' ),
				),
				'support'  => array(
					'label'  => esc_html__( 'Support', 'webberzone-link-warnings' ),
					'url'    => 'https://webberzone.com/support/',
					'type'   => 'secondary',
					'target' => '_blank',
					'rel'    => 'noopener noreferrer',
				),
				'plugins'  => array(
					'label'  => esc_html__( 'More Plugins', 'webberzone-link-warnings' ),
					'url'    => 'https://webberzone.com/plugins/',
					'type'   => 'secondary',
					'target' => '_blank',
					'rel'    => 'noopener noreferrer',
				),
			),
		);
	}
}
