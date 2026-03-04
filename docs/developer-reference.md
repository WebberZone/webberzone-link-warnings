# Developer Reference

This document covers the filters, actions, PHP functions, and integration points available in WebberZone Link Warnings. All hooks use the `wzlw` prefix.

## PHP wrapper functions

The following global functions are defined in `includes/options-api.php` and are available after the plugin loads.

### `wzbel_get_settings()`

Returns the full settings array, merged with defaults.

```php
$settings = wzbel_get_settings();
```

**Returns:** `array`

### `wzbel_get_option( $key, $default_value )`

Returns a single setting value. Falls back to the registered default if the key is not set. If `$default_value` is provided, it takes priority over the registered default.

```php
$method = wzbel_get_option( 'warning_method', 'inline' );
```

**Parameters:**

- `$key` *(string)* — The setting key.
- `$default_value` *(mixed, optional)* — Fallback value. Default `null`.

**Returns:** `mixed`

### `wzbel_update_option( $key, $value )`

Updates a single setting in the database and the in-memory cache.

```php
wzbel_update_option( 'warning_method', 'modal' );
```

**Parameters:**

- `$key` *(string)* — The setting key.
- `$value` *(mixed)* — The new value.

**Returns:** `bool` — `true` if the option was updated.

### `wzbel_delete_option( $key )`

Removes a single key from the settings array.

```php
wzbel_delete_option( 'excluded_domains' );
```

**Returns:** `bool`

### `wzbel_update_settings( $settings, $merge, $autoload )`

Replaces or merges the entire settings array.

```php
// Merge new values into existing settings.
wzbel_update_settings( array( 'warning_method' => 'redirect' ) );

// Replace all settings (no merge).
wzbel_update_settings( $new_settings, false );
```

**Parameters:**

- `$settings` *(array)* — Settings to save.
- `$merge` *(bool, optional)* — Whether to merge with existing settings. Default `true`.
- `$autoload` *(bool, optional)* — Whether to autoload the option. Default `true`.

**Returns:** `bool`

### `wzbel_settings_defaults()`

Returns the default settings array as derived from the registered settings fields.

```php
$defaults = wzbel_settings_defaults();
```

**Returns:** `array`

### `wzbel_get_default_option( $key )`

Returns the default value for a specific setting key.

```php
$default_method = wzbel_get_default_option( 'warning_method' ); // 'inline_modal'
```

**Returns:** `mixed` — The default value, or `false` if the key does not exist.

### `wzbel_settings_reset()`

Resets all settings to their defaults.

```php
wzbel_settings_reset();
```

**Returns:** `bool`

## Filter hooks

### `wzlw_get_settings`

Filters the full settings array after it is retrieved and merged with defaults.

```php
add_filter( 'wzlw_get_settings', function ( array $settings ): array {
    // Force modal method on all sites.
    $settings['warning_method'] = 'modal';
    return $settings;
} );
```

**Parameters:**

- `$settings` *(array)* — The merged settings array.

### `wzlw_get_option`

Filters the value of any individual setting when retrieved via `wzbel_get_option()`.

```php
add_filter( 'wzlw_get_option', function ( $value, string $key, $default ) {
    if ( 'redirect_countdown' === $key ) {
        return 10; // Override countdown to 10 seconds.
    }
    return $value;
}, 10, 3 );
```

**Parameters:**

- `$value` *(mixed)* — The setting value.
- `$key` *(string)* — The setting key.
- `$default_value` *(mixed)* — The default value.

### `wzlw_get_option_{$key}`

Key-specific variant of the above filter. Fires only for the named key.

```php
add_filter( 'wzlw_get_option_warning_method', function ( $value ) {
    if ( wp_is_mobile() ) {
        return 'inline'; // Use inline-only on mobile devices.
    }
    return $value;
} );
```

### `wzlw_update_option`

Filters a setting value before it is saved to the database.

```php
add_filter( 'wzlw_update_option', function ( $value, string $key ) {
    if ( 'redirect_countdown' === $key ) {
        return max( 3, (int) $value ); // Enforce minimum 3 seconds.
    }
    return $value;
}, 10, 2 );
```

**Parameters:**

- `$value` *(mixed)* — The value being saved.
- `$key` *(string)* — The setting key.

### `wzlw_excluded_domains`

Filters the list of excluded domains before the external link check runs. Use this to add domains programmatically without modifying the settings.

```php
add_filter( 'wzlw_excluded_domains', function ( array $domains, string $link_host ): array {
    $domains[] = 'cdn.example.com';
    $domains[] = 'assets.example.com';
    return $domains;
}, 10, 2 );
```

**Parameters:**

- `$domains` *(array)* — Array of excluded domain strings.
- `$link_host` *(string)* — The host of the link being evaluated.

### `wzlw_settings_defaults`

Filters the default settings array. Useful for changing defaults in a must-use plugin or theme.

```php
add_filter( 'wzlw_settings_defaults', function ( array $defaults ): array {
    $defaults['warning_method'] = 'redirect';
    $defaults['redirect_countdown'] = 10;
    return $defaults;
} );
```

### `wzlw_registered_settings`

Filters the registered settings array. Use this to add, remove, or modify settings fields on the admin page.

```php
add_filter( 'wzlw_registered_settings', function ( array $settings ): array {
    // Remove the redirect countdown field.
    unset( $settings['display']['redirect_countdown'] );
    return $settings;
} );
```

### `wzlw_settings_sections`

Filters the settings page tab definitions.

```php
add_filter( 'wzlw_settings_sections', function ( array $sections ): array {
    $sections['custom'] = esc_html__( 'Custom', 'my-plugin' );
    return $sections;
} );
```

### Section-specific filters

Each settings section has its own filter, fired when the section's fields are defined:

- `wzlw_settings_general` — General tab fields.
- `wzlw_settings_display` — Display tab fields.
- `wzlw_settings_advanced` — Advanced tab fields.

```php
add_filter( 'wzlw_settings_advanced', function ( array $settings ): array {
    $settings['my_custom_field'] = array(
        'id'      => 'my_custom_field',
        'name'    => 'My Custom Field',
        'desc'    => 'Description of the field.',
        'type'    => 'text',
        'default' => '',
    );
    return $settings;
} );
```

### `wzlw_settings_sanitize`

Filters the settings array immediately before it is saved. Runs on every settings save.

```php
add_filter( 'wzlw_settings_sanitize', function ( array $settings ): array {
    // Ensure countdown is never below 3.
    if ( isset( $settings['redirect_countdown'] ) ) {
        $settings['redirect_countdown'] = max( 3, (int) $settings['redirect_countdown'] );
    }
    return $settings;
} );
```

## Accessing the plugin instance

The main plugin singleton is accessible via the `wzlw()` function:

```php
$plugin = wzlw();

// Access sub-components.
$plugin->content_processor;
$plugin->frontend_handler;
$plugin->redirect_handler;
$plugin->admin; // Only available in admin context.
```

## Content processing hooks

The plugin filters content on two standard WordPress hooks:

- `the_content` at priority 999
- `the_excerpt` at priority 999

The high priority ensures the plugin runs after most other content filters. If you need to run after the plugin, use a priority above 999.

To prevent the plugin from processing specific content, you can remove the filter temporarily:

```php
remove_filter( 'the_content', array( wzlw()->content_processor, 'process_content' ), 999 );
echo apply_filters( 'the_content', $my_content );
add_filter( 'the_content', array( wzlw()->content_processor, 'process_content' ), 999 );
```

## JavaScript objects

The plugin exposes two JavaScript objects on the frontend, depending on the active warning method.

### `wzBelSettings`

Available when the warning method includes a modal or redirect component. Localised via `wp_localize_script()` on the `wz-bel-modal` handle.

```js
wzBelSettings.modalTitle    // Modal heading text.
wzBelSettings.modalMessage  // Modal body text.
wzBelSettings.continueText  // Continue button label.
wzBelSettings.cancelText    // Cancel button label.
wzBelSettings.warningMethod // Active warning method string.
```

### `wzBelRedirect`

Available on the redirect interstitial page only. Localised on the `wz-bel-redirect` handle.

```js
wzBelRedirect.destination // The external URL.
wzBelRedirect.countdown   // Countdown duration in seconds.
```

## Data attributes

The plugin adds the following `data-` attributes to processed external links when a modal or redirect method is active:

| Attribute | Value |
| --- | --- |
| `data-wz-bel-external` | `"true"` — marks the link as an external link handled by the plugin. |
| `data-wz-bel-url` | The escaped external URL. |
| `data-wz-bel-redirect-url` | The full redirect interstitial URL for this destination. |

The frontend JavaScript uses `data-wz-bel-external` as the selector for click delegation.

## CSS handles

Use these handles when declaring stylesheet dependencies:

| Handle | File | Loaded on |
| --- | --- | --- |
| `wz-bel-frontend` | `includes/assets/css/frontend.css` | All frontend pages. |
| `wz-bel-redirect` | `includes/assets/css/redirect.css` | Redirect interstitial page only. |

## Script handles

| Handle | File | Loaded on |
| --- | --- | --- |
| `wz-bel-modal` | `includes/admin/js/modal.js` | Frontend, when method includes modal or redirect. |
| `wz-bel-redirect` | `includes/assets/js/redirect.js` | Redirect interstitial page only. |
