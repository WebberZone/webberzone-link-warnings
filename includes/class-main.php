<?php
/**
 * Main plugin class.
 *
 * Handles plugin initialization and component loading.
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
 * Main plugin class.
 *
 * @since 1.0.0
 */
class Main {

	/**
	 * Plugin instance.
	 *
	 * @since 1.0.0
	 * @var Main
	 */
	private static $instance = null;

	/**
	 * Admin instance.
	 *
	 * @since 1.0.0
	 * @var Admin\Admin
	 */
	public $admin;

	/**
	 * Content processor instance.
	 *
	 * @since 1.0.0
	 * @var Content_Processor
	 */
	public $content_processor;

	/**
	 * Frontend handler instance.
	 *
	 * @since 1.0.0
	 * @var Frontend_Handler
	 */
	public $frontend_handler;

	/**
	 * Redirect handler instance.
	 *
	 * @since 1.0.0
	 * @var Redirect_Handler
	 */
	public $redirect_handler;

	/**
	 * Get plugin instance.
	 *
	 * @since 1.0.0
	 * @return Main
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load required files.
	 *
	 * @since 1.0.0
	 */
	private function load_dependencies() {
		require_once WZ_BEL_PLUGIN_DIR . 'includes/options-api.php';
	}

	/**
	 * Initialize plugin hooks.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {
		Hook_Registry::add_action( 'plugins_loaded', array( $this, 'init' ) );
		Hook_Registry::add_action( 'init', array( $this, 'load_textdomain' ) );
		Hook_Registry::add_action( 'init', array( $this, 'init_admin' ) );
	}

	/**
	 * Initialize plugin components.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->content_processor = new Content_Processor();
		$this->frontend_handler  = new Frontend_Handler();
		$this->redirect_handler  = new Redirect_Handler();
	}

	/**
	 * Initialize admin components.
	 *
	 * @since 1.0.0
	 */
	public function init_admin(): void {
		if ( is_admin() ) {
			$this->admin = new Admin\Admin();
		}
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'better-external-links',
			false,
			dirname( WZ_BEL_PLUGIN_BASENAME ) . '/languages'
		);
	}
}
