---
slug: webberzone-link-warnings-developer-reference
title: "WebberZone Link Warnings Developer Reference"
products: [link-warnings]
sections: ["03-wlw-developer-docs"]
tags: [developer, link-warnings]
status: publish
order: 0
toc: true
---

[toc]

Use the following PHP functions, hooks, and frontend integration points for [WebberZone Link Warnings](https://webberzone.com/plugins/webberzone-link-warnings/). All hooks use the `wzlw` prefix.

## PHP wrapper functions

These global functions are defined in `includes/options-api.php` and are available after the plugin loads. The plugin instance accessor has its own namespace, as shown below.

### `wzlw_get_settings()`

Returns the saved settings array after the `wzlw_get_settings` filter. It does not merge missing keys with defaults. Use `wzlw_get_option()` to read one setting with its fallback, or `Options_API::get_settings_with_defaults()` for a merged array.

```php
$settings = wzlw_get_settings();
```

**Returns:** `array`

### `wzlw_get_option( $key, $default_value )`

Returns a single setting value. Falls back to the registered default if the key is not set. If `$default_value` is provided, it takes priority over the registered default.

```php
$method = wzlw_get_option( 'warning_method', 'inline' );
```

**Parameters:**

- `$key` *(string)* — The setting key.
- `$default_value` *(mixed, optional)* — Fallback value. Default `null`.

**Returns:** `mixed`

### `wzlw_update_option( $key, $value )`

Updates a single setting in the database and the in-memory cache.

```php
wzlw_update_option( 'warning_method', 'modal' );
```

**Parameters:**

- `$key` *(string)* — The setting key.
- `$value` *(mixed)* — The new value.

**Returns:** `bool` — `true` if the option was updated.

### `wzlw_delete_option( $key )`

Removes a single key from the settings array.

```php
wzlw_delete_option( 'excluded_domains' );
```

**Returns:** `bool`

### `wzlw_update_settings( $settings, $merge, $autoload )`

Replaces or merges the entire settings array.

```php
// Merge new values into existing settings.
wzlw_update_settings( array( 'warning_method' => 'redirect' ) );

// Replace all settings (no merge).
wzlw_update_settings( $new_settings, false );
```

**Parameters:**

- `$settings` *(array)* — Settings to save.
- `$merge` *(bool, optional)* — Whether to merge with existing settings. Default `true`.
- `$autoload` *(bool, optional)* — Whether to autoload the option. Default `true`.

**Returns:** `bool`

### `wzlw_settings_defaults()`

Returns the default settings array derived from the registered settings fields. Call it after `init`, because building the field definitions uses translated strings.

```php
$defaults = wzlw_settings_defaults();
```

**Returns:** `array`

### `wzlw_get_default_option( $key )`

Returns the default value for a specific setting key.

```php
$default_method = wzlw_get_default_option( 'warning_method' ); // 'inline_modal'
```

**Returns:** `mixed` — The default value, or `false` if the key does not exist.

### `wzlw_settings_reset()`

Resets all settings to their defaults.

```php
wzlw_settings_reset();
```

**Returns:** `bool`

### `wzlw_is_disabled_for_current_user()`

Returns whether warnings are hidden from the current user under the **Disable Warnings For** setting. Link attributes are still applied for these users.

```php
if ( ! wzlw_is_disabled_for_current_user() ) {
    // Output something that only makes sense alongside the warnings.
}
```

**Returns:** `bool`

## Filter hooks

### `wzlw_get_settings`

Filters the saved settings array when the per-request settings cache is populated. Missing keys have not been merged with defaults.

```php
add_filter( 'wzlw_get_settings', function ( array $settings ): array {
    // Force modal method on all sites.
    $settings['warning_method'] = 'modal';
    return $settings;
} );
```

**Parameters:**

- `$settings` *(array)* — The saved settings array.

**Returns:** `array`

### `wzlw_is_disabled_for_current_user`

Filters whether warnings are hidden from the current user.

```php
add_filter( 'wzlw_is_disabled_for_current_user', function ( bool $disabled ): bool {
    // Hide warnings from users who can edit posts.
    return $disabled || current_user_can( 'edit_posts' );
} );
```

**Parameters:**

- `$disabled` *(bool)* — True if warnings are disabled for the current user.

**Returns:** `bool`

### `wzlw_link_rules`

Filters the Link Rules before they are validated. Rules are checked in order and the first match wins. Patterns use the same syntax as the Rules tab. Rules with a missing or invalid pattern, action or ID are dropped.

```php
add_filter( 'wzlw_link_rules', function ( array $rules ): array {
    // Always show the modal for links to partner.example, ahead of the saved rules.
    array_unshift(
        $rules,
        array(
            'id'      => 'partner',
            'pattern' => 'partner.example',
            'action'  => 'modal',
            'message' => 'You are leaving for our partner site.',
        )
    );
    return $rules;
} );
```

**Parameters:**

- `$rules` *(array[])* — Rules, each with `id`, `pattern`, `action` and optional `message` keys. `action` is one of `modal`, `inline`, `inline_modal`, `redirect`, `inline_redirect` or `exclude`.
- `$settings` *(array)* — The plugin settings.

**Returns:** `array[]`

### `wzlw_excluded_domains`

Filters the excluded domains list while a link is being classified. PHP is the only place this filter runs — the browser-side scan cannot repeat it — so links PHP excluded through this filter carry a per-request token (`data-wzlw-excluded` with the value of `Content_Processor::get_exclusion_token()`) that the JavaScript trusts.

```php
add_filter( 'wzlw_excluded_domains', function ( array $domains, string $link_host ): array {
    // Treat any host ending in .internal as excluded.
    $domains[] = '*.internal';
    return $domains;
}, 10, 2 );
```

**Parameters:**

- `$domains` *(array)* — Excluded domain entries, as configured in the Excluded Domains setting. Wildcard entries keep their `*.` prefix.
- `$link_host` *(string)* — The lowercased host of the link being checked.

**Returns:** `array`

### `wzlw_get_option`

Filters an individual option after its saved value or fallback is selected.

```php
add_filter( 'wzlw_get_option', function ( $value, $key, $default_value ) {
    return $value;
}, 10, 3 );
```

**Parameters:**

- `$value` *(mixed)* — The selected value.
- `$key` *(string)* — The option key.
- `$default_value` *(mixed)* — The resolved fallback.

**Returns:** `mixed` — The option value.

### `wzlw_get_option_{$key}`

Filters one option after the general `wzlw_get_option` filter.

```php
add_filter( 'wzlw_get_option_warning_method', function ( $value, $key, $default_value ) {
    return 'modal';
}, 10, 3 );
```

**Parameters:**

- `$value` *(mixed)* — The value after the general filter.
- `$key` *(string)* — The option key.
- `$default_value` *(mixed)* — The resolved fallback.

**Returns:** `mixed`

### `wzlw_update_option`

Filters a single setting before `wzlw_update_option()` saves it.

```php
add_filter( 'wzlw_update_option', function ( $value, $key ) {
    return $value;
}, 10, 2 );
```

**Parameters:**

- `$value` *(mixed)* — The value to save.
- `$key` *(string)* — The option key.

**Returns:** `mixed`

### `wzlw_settings_defaults`

Filters the defaults used by the settings framework and the translation-free single-option fallback path.

```php
add_filter( 'wzlw_settings_defaults', function ( array $defaults ): array {
    $defaults['download_scope'] = 'external';
    return $defaults;
} );
```

**Parameters:**

- `$defaults` *(array)* — Defaults keyed by option ID. Display-text values can be empty in the translation-free path.

**Returns:** `array`

### `wzlw_icon_options`

Filters the choices shown in **Icon Style**.

```php
add_filter( 'wzlw_icon_options', function ( array $options ): array {
    $options['custom_arrow'] = 'Custom arrow';
    return $options;
} );
```

**Parameters:**

- `$options` *(array)* — Labels keyed by icon ID.

**Returns:** `array`

### `wzlw_icon_map`

Filters the characters used for icon IDs, including the download icon.

```php
add_filter( 'wzlw_icon_map', function ( array $map ): array {
    $map['custom_arrow'] = '➜';
    return $map;
} );
```

**Parameters:**

- `$map` *(array)* — Characters keyed by icon ID. Pair new IDs with `wzlw_icon_options` so they can be selected.

**Returns:** `array`

## Action hooks

### `wzlw_activate`

Runs after the per-site activation routine flushes rewrite rules and sets its activation transient.

```php
add_action( 'wzlw_activate', function () {
    // Run an integration's activation task.
} );
```

**Parameters:** none. On network activation, this action runs inside the per-site activation routine for each selected site.

### `wzlw_deactivate`

Runs after deactivation tasks, including transient cleanup and rewrite-rule flushing.

```php
add_action( 'wzlw_deactivate', function () {
    // Run an integration's cleanup task.
} );
```

**Parameters:** none. On network deactivation, this action runs after the per-site deactivation loop.

## Accessing the plugin instance

Use the namespaced accessor defined in the main plugin file:

```php
$plugin = \WebberZone\Link_Warnings\wzlw();
$processor = $plugin->content_processor;
```

It returns `WebberZone\Link_Warnings\Main`. The public `content_processor`, `frontend_handler`, and `redirect_handler` components are initialized on `plugins_loaded`. The `admin` component is initialized on `init` in admin requests.

For a merged settings array:

```php
$settings = \WebberZone\Link_Warnings\Options_API::get_settings_with_defaults();
```

Call this after `init` because the merged-default path builds translated field definitions.

## JavaScript objects

### `wzlwSettings`

The `wzlw-modal` script receives the frontend configuration. It includes `warningMethod`, `scope`, `linkRules`, `downloadExtensions`, `downloadScope`, `warningsDisabled`, `isPreview`, `excludedDomains`, `excludedToken`, class lists, link-attribute lists, modal text, dismissal settings, and the signing endpoint configuration (`ajaxUrl`, `nonce`, `redirectBaseUrl`).

Use PHP settings and filters to configure behavior. The script captures settings during initialization; changing this object afterward does not rerun the scan.

### `wzlwRedirect`

The redirect page script receives `destination` and `countdown`. When confirmation is required, these are an empty string and `0`. The timer also requires a `.wzlw-countdown-number` element and a positive countdown.

## Data attributes

These attributes describe processed links. Treat them as plugin-managed output rather than a public way to bypass warnings.

| Attribute | Purpose |
| --- | --- |
| `data-wzlw-url` | Destination used for click handling. The browser revalidates the anchor’s actual `href`. |
| `data-wzlw-external` | Marks external-link classification. |
| `data-wzlw-blank` | Marks a processed non-external link that opens a new tab. |
| `data-wzlw-download` | Marks configured download classification. |
| `data-wzlw-redirect-url` | Signed redirect endpoint URL. |
| `data-wzlw-rule` | ID of the matching warning rule. |
| `data-wzlw-method` | Warning method assigned by that rule. |
| `data-wzlw-excluded` | Per-request token preserving a PHP exclusion in the browser scan. |
| `data-wzlw-token` | Per-request token paired with `wzlw-processed` to identify PHP-processed markup. |
| `data-wzlw-indicator` | Per-request token that prevents duplicate indicator insertion during repeated PHP processing. |
| `data-wzlw-signing` | Temporary browser marker while an AJAX signing request is pending. |

Cached markup without the current token is classified again. Matching existing indicator markup can prevent duplicate presentation, but never skips classification or click warnings.

## CSS handles

| Handle | Source file | Where loaded |
| --- | --- | --- |
| `wzlw-frontend` | `includes/assets/css/frontend.css` | Frontend pages, for indicators and modals. |
| `wzlw-redirect` | `includes/assets/css/redirect.css` | Redirect endpoint. |

Styles use `-rtl` variants when `is_rtl()` is true. Minified variants load unless `SCRIPT_DEBUG` is enabled. See [Styling WebberZone Link Warnings](https://webberzone.com/support/knowledgebase/styling-webberzone-link-warnings/) for classes and custom properties.

## Script handles

| Handle | Source file | Purpose |
| --- | --- | --- |
| `wzlw-modal` | `includes/admin/js/modal.js` | Sitewide link scan, modal handling, signing requests, and redirect navigation. |
| `wzlw-redirect` | `includes/assets/js/redirect.js` | Redirect-page countdown. |

Minified variants load unless `SCRIPT_DEBUG` is enabled. For template overrides, see [Customizing the redirect screen](https://webberzone.com/support/knowledgebase/customising-the-redirect-screen/).
