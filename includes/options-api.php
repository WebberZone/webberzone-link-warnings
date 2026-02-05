<?php
/**
 * Better External Links Options API.
 *
 * @package WebberZone\Better_External_Links
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Include the Options_API class if not already loaded.
if ( ! class_exists( '\WebberZone\Better_External_Links\Options_API' ) ) {
	require_once __DIR__ . '/class-options-api.php';
}

/**
 * Get Settings.
 *
 * Retrieves all plugin settings
 *
 * @since 1.0.0
 * @return array Settings
 */
function wzbel_get_settings() {
	return \WebberZone\Better_External_Links\Options_API::get_settings();
}

/**
 * Get an option.
 *
 * Looks to see if the specified setting exists and returns the default value if it doesn't.
 *
 * @since 1.0.0
 *
 * @param string $key            Option to fetch.
 * @param mixed  $default_value  Default option.
 *
 * @return mixed The option value or the default value if the option does not exist.
 */
function wzbel_get_option( $key = '', $default_value = null ) {
	return \WebberZone\Better_External_Links\Options_API::get_option( $key, $default_value );
}

/**
 * Update an option
 *
 * Updates a setting value in both the db and the global variable.
 * Warning: Passing in an empty, false or null string value will remove
 *        the key from the wz_bel_settings array.
 *
 * @since 1.0.0
 *
 * @param  string          $key   The Key to update.
 * @param  string|bool|int $value The value to set the key to.
 * @return boolean   True if updated, false if not.
 */
function wzbel_update_option( $key = '', $value = false ) {
	return \WebberZone\Better_External_Links\Options_API::update_option( $key, $value );
}

/**
 * Remove an option
 *
 * Removes a setting value in both the db and the global variable.
 *
 * @since 1.0.0
 *
 * @param  string $key The Key to update.
 * @return boolean   True if updated, false if not.
 */
function wzbel_delete_option( $key = '' ) {
	return \WebberZone\Better_External_Links\Options_API::delete_option( $key );
}

/**
 * Default settings.
 *
 * @since 1.0.0
 *
 * @return array Default settings
 */
function wzbel_settings_defaults() {
	return \WebberZone\Better_External_Links\Options_API::get_settings_defaults();
}

/**
 * Get the default option for a specific key
 *
 * @since 1.0.0
 *
 * @param string $key Key of the option to fetch.
 * @return mixed
 */
function wzbel_get_default_option( $key = '' ) {
	return \WebberZone\Better_External_Links\Options_API::get_default_option( $key );
}

/**
 * Reset settings.
 *
 * @since 1.0.0
 * @return bool Success status.
 */
function wzbel_settings_reset() {
	return \WebberZone\Better_External_Links\Options_API::reset_settings();
}

/**
 * Update all settings at once.
 *
 * @since 1.0.0
 *
 * @param array $settings Settings array to save.
 * @param bool  $merge    Whether to merge with existing settings. Default true.
 * @param bool  $autoload Whether to autoload the option. Default true.
 * @return bool True if settings were updated, false otherwise.
 */
function wzbel_update_settings( array $settings, bool $merge = true, bool $autoload = true ) {
	return \WebberZone\Better_External_Links\Options_API::update_settings( $settings, $merge, $autoload );
}
