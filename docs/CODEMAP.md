# WebberZone Link Warnings - Codemap

## Plugin Overview

**Name**: WebberZone Link Warnings  
**Version**: 1.0.0  
**Namespace**: `WebberZone\Link_Warnings`  
**Text Domain**: `webberzone-link-warnings`  
**Minimum PHP**: 7.4  
**Minimum WordPress**: 6.0  

## Purpose

Enhances accessibility by warning users when links open in new windows or navigate to external sites. Supports WCAG 2.1 compliance.

## Architecture

### Core Structure

- **Pattern**: Singleton pattern for main class
- **Autoloading**: Custom PSR-4 style autoloader
- **Settings**: WordPress Settings API (needs modernization)
- **Content Processing**: Uses `WP_HTML_Tag_Processor` for efficient HTML parsing

### File Structure

```
webberzone-link-warnings/
├── webberzone-link-warnings.php          # Bootstrap file
├── includes/
│   ├── class-autoloader.php           # PSR-4 autoloader (needs update)
│   ├── class-main.php                 # Main singleton class
│   ├── class-settings.php             # Settings management (needs migration to Settings API)
│   ├── class-content-processor.php    # Content parsing/modification
│   ├── class-frontend-handler.php     # Modal functionality
│   └── class-redirect-handler.php     # Redirect screen handling
├── assets/
│   ├── css/
│   │   ├── admin.css                  # Admin styles
│   │   ├── frontend.css               # Frontend indicators + modal
│   │   └── redirect.css               # Redirect page styles
│   └── js/
│       ├── modal.js                   # Modal dialog functionality
│       └── redirect.js                # Redirect countdown timer
└── languages/                          # Translation files
```

## Class Breakdown

### 1. Main (`class-main.php`)

**Namespace**: `WebberZone\Link_Warnings\Main`  
**Pattern**: Singleton  
**Purpose**: Plugin initialization and component loading

**Properties**:

- `$instance` - Singleton instance
- `$settings` - Settings instance
- `$content_processor` - Content processor instance
- `$frontend_handler` - Frontend handler instance
- `$redirect_handler` - Redirect handler instance

**Methods**:

- `get_instance()` - Get singleton instance
- `__construct()` - Private constructor
- `load_dependencies()` - Load required files
- `init_hooks()` - Initialize plugin hooks
- `init()` - Initialize plugin components
- `load_textdomain()` - Load translations

**Hooks**:

- `plugins_loaded` → `init()`
- `init` → `load_textdomain()`

### 2. Settings (`class-settings.php`)

**Namespace**: `WebberZone\External_Link_Accessibility\Settings`  
**Note**: Namespace mismatch - needs fixing!  
**Purpose**: Settings management and admin interface

**Properties**:

- `$option_name` - Settings option name (`wz_ela_settings`)

**Settings Structure**:

```php
array(
    'warning_method'      => 'inline|modal|redirect|inline_modal',
    'scope'               => 'external|target_blank|both',
    'visual_indicator'    => 'icon|text|both|none',
    'indicator_text'      => string,
    'screen_reader_text'  => string,
    'modal_title'         => string,
    'modal_message'       => string,
    'modal_continue_text' => string,
    'modal_cancel_text'   => string,
    'redirect_message'    => string,
    'excluded_domains'    => array,
    'enabled_post_types'  => array,
)
```

**Methods**:

- `__construct()` - Register hooks
- `add_settings_page()` - Add settings page to admin menu
- `register_settings()` - Register settings and fields
- `render_settings_page()` - Render settings page
- `render_*_field()` - Render individual fields
- `sanitize_settings()` - Sanitize input
- `get_settings()` - Get plugin settings
- `enqueue_admin_assets()` - Enqueue admin assets

**Hooks**:

- `admin_menu` → `add_settings_page()`
- `admin_init` → `register_settings()`
- `admin_enqueue_scripts` → `enqueue_admin_assets()`

### 3. Content Processor (`class-content-processor.php`)

**Namespace**: `WebberZone\External_Link_Accessibility\Content_Processor`  
**Note**: Namespace mismatch - needs fixing!  
**Purpose**: Process content to add accessibility features

**Properties**:

- `$settings` - Plugin settings
- `$site_host` - Current site hostname

**Methods**:

- `__construct()` - Initialize and add filters
- `process_content()` - Main content processing
- `add_visual_indicators()` - Add visual indicators to links
- `add_indicator_to_link()` - Add indicator to single link
- `get_visual_indicator()` - Get indicator HTML
- `get_screen_reader_text()` - Get screen reader HTML
- `get_aria_label()` - Get ARIA label for link
- `is_external_link()` - Check if link is external
- `should_process_link()` - Determine if link should be processed
- `is_post_type_enabled()` - Check if current post type is enabled

**Hooks**:

- `the_content` → `process_content()` (priority 999)
- `the_excerpt` → `process_content()` (priority 999)

**Processing Flow**:

1. Check if post type is enabled
2. Parse content with `WP_HTML_Tag_Processor`
3. Identify external/target="_blank" links
4. Add data attributes for JS handling
5. Add ARIA attributes
6. Add CSS classes
7. Add visual indicators (if inline method)

### 4. Frontend Handler (`class-frontend-handler.php`)

**Namespace**: `WebberZone\External_Link_Accessibility\Frontend_Handler`  
**Note**: Namespace mismatch - needs fixing!  
**Purpose**: Handle frontend JavaScript and modal functionality

**Methods**:

- `__construct()` - Register hooks
- `enqueue_assets()` - Enqueue frontend assets
- `render_modal()` - Render modal HTML in footer

**Hooks**:

- `wp_enqueue_scripts` → `enqueue_assets()`
- `wp_footer` → `render_modal()`

**Assets**:

- `wz-ela-frontend` - Frontend CSS (always loaded)
- `wz-ela-modal` - Modal JS (conditional)

**JS Localization** (`wzElaSettings`):

- `modalTitle`
- `modalMessage`
- `continueText`
- `cancelText`

### 5. Redirect Handler (`class-redirect-handler.php`)

**Namespace**: `WebberZone\External_Link_Accessibility\Redirect_Handler`  
**Note**: Namespace mismatch - needs fixing!  
**Purpose**: Handle redirect screen functionality

**Methods**:

- `__construct()` - Register hooks
- `add_rewrite_rules()` - Add rewrite rules for redirect endpoint
- `add_query_vars()` - Add query vars
- `handle_redirect()` - Handle redirect template
- `is_valid_url()` - Validate URL
- `render_redirect_template()` - Render redirect template
- `enqueue_redirect_assets()` - Enqueue redirect assets
- `get_redirect_url()` - Get redirect URL (static)

**Hooks**:

- `init` → `add_rewrite_rules()`
- `template_redirect` → `handle_redirect()`
- `query_vars` → `add_query_vars()`
- `wp_enqueue_scripts` → `enqueue_redirect_assets()`

**Rewrite Rules**:

- Pattern: `^external-redirect/?`
- Query: `index.php?wz_ela_redirect=1`

**Query Vars**:

- `wz_ela_redirect`
- `wz_ela_url`

**JS Localization** (`wzElaRedirect`):

- `destination`
- `countdown`

### 6. Autoloader (`class-autoloader.php`)

**Namespace**: `WebberZone\Link_Warnings\Autoloader`  
**Purpose**: PSR-4 style autoloader

**Methods**:

- `init()` - Initialize autoloader
- `autoload()` - Autoload classes

**Current Issues**:

- Basic implementation
- Needs modernization to match KB Pro pattern
- No support for subdirectories

## Constants

```php
WZLW_VERSION         // Plugin version
WZLW_PLUGIN_FILE     // Main plugin file path
WZLW_PLUGIN_DIR      // Plugin directory path
WZLW_PLUGIN_URL      // Plugin URL
WZLW_PLUGIN_BASENAME // Plugin basename
```

## Database

### Options

- `wzlw_settings` - Main settings array

## CSS Classes

### Frontend

- `.wz-ela-processed` - Processed link
- `.wz-ela-external` - External link
- `.wz-ela-icon` - Visual icon
- `.wz-ela-text` - Visual text
- `.screen-reader-text` - Screen reader only text

### Modal

- `.wz-ela-modal` - Modal container
- `.wz-ela-modal-overlay` - Modal overlay
- `.wz-ela-modal-container` - Modal content container
- `.wz-ela-modal-content` - Modal content
- `.wz-ela-modal-title` - Modal title
- `.wz-ela-modal-message` - Modal message
- `.wz-ela-modal-url` - Modal URL display
- `.wz-ela-modal-actions` - Modal action buttons
- `.wz-ela-modal-button` - Modal button
- `.wz-ela-modal-cancel` - Cancel button
- `.wz-ela-modal-continue` - Continue button

### Redirect

- `.wz-ela-redirect-container` - Redirect page container
- `.wz-ela-redirect-content` - Redirect content
- `.wz-ela-redirect-icon` - Redirect icon
- `.wz-ela-redirect-title` - Redirect title
- `.wz-ela-redirect-message` - Redirect message
- `.wz-ela-redirect-url-container` - URL container
- `.wz-ela-redirect-url-label` - URL label
- `.wz-ela-redirect-url` - URL display
- `.wz-ela-redirect-url-full` - Full URL display
- `.wz-ela-redirect-actions` - Action buttons
- `.wz-ela-redirect-button` - Button
- `.wz-ela-redirect-continue` - Continue button
- `.wz-ela-redirect-back` - Back button
- `.wz-ela-redirect-countdown` - Countdown display
- `.wz-ela-countdown-number` - Countdown number

## JavaScript

### Modal (`modal.js`)

**Global Object**: `wzElaSettings`

**Events**:

- `wzlw_modal_opened` - Fired when modal opens
- `wzlw_modal_closed` - Fired when modal closes

**Functions**:

- Initialize modal listeners
- Handle external link clicks
- Show/hide modal
- Handle continue/cancel actions

### Redirect (`redirect.js`)

**Global Object**: `wzElaRedirect`

**Functions**:

- Initialize countdown timer
- Auto-redirect after countdown
- Update countdown display

## Hooks & Filters

### Actions

```php
do_action( 'wzlw_before_init' );
do_action( 'wzlw_after_init' );
```

### Filters

```php
apply_filters( 'wzlw_settings', $settings );
apply_filters( 'wzlw_processed_content', $content, $original_content );
apply_filters( 'wzlw_indicator_html', $html, $link_url, $is_external );
apply_filters( 'wzlw_excluded_domains', $domains );
```

## Issues to Fix

### Critical

1. **Namespace Inconsistency**: Multiple classes use `WebberZone\External_Link_Accessibility` instead of `WebberZone\Link_Warnings`
2. **No composer.json**: Missing dependency management
3. **Basic Autoloader**: Needs modernization
4. **Old Settings API**: Not using WebberZone Settings API pattern

### Improvements Needed

1. Add `class-options-api.php` for centralized settings access
2. Add `util/class-hook-registry.php` for hook management
3. Migrate to WebberZone Settings API
4. Add PHP 7.4+ typed properties
5. Add proper admin structure (`includes/admin/`)
6. Add activation/deactivation classes
7. Add uninstaller
8. Add phpcs.xml.dist, phpstan.neon.dist
9. Add proper .gitignore
10. Add build-assets.js

## Modernization Plan

### Phase 1: Foundation

1. Add composer.json
2. Initialize .git folder
3. Update autoloader to KB Pro pattern
4. Fix namespace inconsistencies

### Phase 2: Settings API

1. Copy Settings API folder from KB Pro
2. Create new `includes/admin/class-settings.php`
3. Migrate settings to Settings API
4. Add Options API class

### Phase 3: Architecture

1. Add Hook Registry
2. Add typed properties
3. Add activator/deactivator classes
4. Restructure admin files

### Phase 4: Quality

1. Add coding standards config
2. Add PHPStan config
3. Add unit tests
4. Add build scripts

## Dependencies

### WordPress Core

- `WP_HTML_Tag_Processor` (WP 6.0+)
- Settings API
- Rewrite API
- Localization API

### PHP

- PHP 7.4+ (for typed properties after modernization)
- SPL autoloading

## Compatibility Notes

- ✅ Classic Editor
- ✅ Block Editor (Gutenberg)
- ✅ Page Builders (Elementor, Beaver Builder, Divi)
- ✅ Multisite
- ✅ Translation plugins (WPML, Polylang)
- ✅ WooCommerce

## Performance Considerations

1. Content processing runs at priority 999 on `the_content`/`the_excerpt`
2. Uses native `WP_HTML_Tag_Processor` for efficient parsing
3. Settings cached via `get_option()`
4. Assets conditionally loaded based on warning method
5. No external API calls or database queries during content processing
