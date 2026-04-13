# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Plugin Overview

**WebberZone Link Warnings** (v1.2.0) adds configurable warnings to external links, affiliate links, or any URL pattern — displaying a modal countdown or inline icon before the visitor leaves the site. Namespace: `WebberZone\Link_Warnings`. Constants: `WZLW_VERSION`, `WZLW_PLUGIN_FILE`, `WZLW_PLUGIN_DIR`, `WZLW_PLUGIN_URL`, `WZLW_PLUGIN_BASENAME`. Requires WordPress 6.6+, PHP 7.4+. No Freemius.

Settings prefix/key: `wzlw` / `wzlw_settings` (wp_options). Access via `wzlw_get_option($key)` / `wzlw_get_settings()`.

## Build Commands

```bash
# Minify JS/CSS and generate RTL versions
npm run build:assets

# Install production PHP dependencies
composer build:vendor

# Run all quality checks (phpcs, phpcompat, phpstan)
composer test

# Individual checks
composer phpcs       # PHP CodeSniffer
composer phpstan     # Static analysis
composer phpcompat   # PHP compatibility check

# Create distribution zip
npm run zip
```

Build requires Node.js with `clean-css-cli`, `terser`, `rtlcss`, and `@wordpress/scripts` (installed via `npm install`).

## Architecture

**Namespace:** `WebberZone\Link_Warnings`
**Autoloading:** PSR-4 via `includes/autoloader.php` — class `Foo_Bar` maps to `includes/class-foo-bar.php`

**Entry point:** `webberzone-link-warnings.php` defines constants (`WZLW_VERSION`, `WZLW_PLUGIN_DIR`, etc.) and boots the plugin via `wzlw()` singleton.

**Core classes:**

| Class | File | Purpose |
|-------|------|---------|
| `Main` | `includes/class-main.php` | Bootstrap, singleton, registers all hooks |
| `Content_Processor` | `includes/class-content-processor.php` | Hooks into `the_content`/`the_excerpt` (priority 999); parses and modifies links using `WP_HTML_Tag_Processor` |
| `Frontend_Handler` | `includes/class-frontend-handler.php` | Enqueues frontend assets, renders modal HTML |
| `Redirect_Handler` | `includes/class-redirect-handler.php` | Handles redirect endpoint and countdown |
| `Options_API` | `includes/class-options-api.php` | Settings storage wrapper |
| `Util\Hook_Registry` | `includes/util/class-hook-registry.php` | Centralized hook registration with deduplication |
| `Util\Icon_Helper` | `includes/util/class-icon-helper.php` | Icon selection utilities |
| `Admin\Admin` | `includes/admin/class-admin.php` | Admin bootstrap (only loaded on admin pages) |
| `Admin\Settings` | `includes/admin/class-settings.php` | Settings pages and forms |

**Settings helpers** (procedural, in `includes/options-api.php`): `wzlw_get_settings()`, `wzlw_get_option()`, `wzlw_update_option()`

## Asset Pipeline

The `build-assets.js` script (run via `npm run build:assets`) recursively finds all `.css` and `.js` files (excluding `node_modules`, `vendor`, `*.min.*`, `*-rtl.css`, `build-assets.js`), then:

1. Minifies CSS → `*.min.css`
2. Minifies JS → `*.min.js`
3. Generates RTL CSS → `*-rtl.css` and `*-rtl.min.css`
4. Formats RTL files with wp-scripts prettier

After adding or significantly editing any `.css` or `.js` file, run `npm run build:assets` to regenerate minified and RTL versions.

## Key Conventions

- Link exclusion: Links with the configured no-icon class (default `wzlw-no-icon`, setting `no_icon_class`) have visual indicators suppressed. Links inside elements with the configured wrapper class (default `wzlw-no-icon-wrapper`, setting `no_icon_wrapper_class`) are also suppressed. All three special class names are configurable under Advanced settings.
- Force external: Links with the configured force-external class (default `wzlw-force-external`, setting `force_external_class`) placed directly on an `<a>` tag are treated as external. Adding the wrapper class (default `wzlw-force-external-wrapper`, setting `force_external_wrapper_class`) to any container forces all descendant links to be treated as external.
- Both exclusion and force-external use a depth counter (`$skip_depth`, `$force_external_depth`) in `Content_Processor::process_content()` to track nesting across the `WP_HTML_Tag_Processor` token stream.
- Content processing uses WordPress's native `WP_HTML_Tag_Processor` (requires WordPress 6.6+, PHP 7.4+).
- All admin components are loaded conditionally (only on admin pages) within `Admin\Admin`.
- The redirect endpoint uses WordPress rewrite rules managed by `Redirect_Handler`.
