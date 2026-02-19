# Getting Started with Better External Links

Better External Links adds accessible warnings to external links and `target="_blank"` links in your WordPress content. It supports inline visual indicators, modal confirmation dialogs, and redirect interstitial screens — or combinations of these.

This guide covers installation, initial setup, and the basics of how the plugin processes your content.

## Requirements

- WordPress 6.6 or later
- PHP 7.4 or later

## Installation

### From the WordPress dashboard

1. Navigate to **Plugins > Add New**.
2. Search for "Better External Links".
3. Click **Install Now**, then **Activate**.

### Manual upload

1. Download the plugin archive from [GitHub releases](https://github.com/WebberZone/better-external-links/releases) or [WordPress.org](https://wordpress.org/plugins/better-external-links/).
2. Upload the `better-external-links` folder to `/wp-content/plugins/`.
3. Activate the plugin from the **Plugins** screen.

## First-run wizard

On first activation, an admin notice appears offering a guided setup wizard. The wizard walks through the core decisions:

1. **Warning method** — inline indicators, modal dialog, redirect screen, or a combined approach.
2. **Link scope** — external links only, or external links plus all `target="_blank"` links.
3. **Visual indicator** — icon, text, both, or screen-reader-only.
4. **Modal settings** — title, message, and button text (if using a modal method).
5. **Redirect settings** — message and countdown duration (if using a redirect method).

You can dismiss the wizard and configure settings manually at any time.

## Manual configuration

All settings are available at **Settings > Better External Links**. The settings page is organised into three tabs:

- **General** — warning method, link scope, and enabled post types.
- **Display** — inline indicator options, modal dialog text, and redirect screen text.
- **Advanced** — excluded domains.

See the [Settings Reference](settings-reference.md) for a full description of every option.

## How content processing works

Better External Links does not modify your stored content. It filters rendered output using the `the_content` and `the_excerpt` hooks at priority 999, which means it runs after most other content filters.

For each `<a>` tag in the output, the plugin:

1. Checks whether the current post type is enabled in settings.
2. Determines whether the link is external by comparing its host against the site host and the excluded domains list.
3. Applies scope rules — external links only, or external links plus `target="_blank"` links.
4. Adds CSS classes (`wz-bel-processed`, `wz-bel-external`) for styling.
5. Appends the configured screen reader text to any existing `aria-label` attribute.
6. For modal and redirect methods, adds `data-wz-bel-external`, `data-wz-bel-url`, and `data-wz-bel-redirect-url` attributes used by the frontend JavaScript.
7. For inline methods, appends visual indicator markup (icon, text, or both) inside the link.

The plugin uses WordPress's native `WP_HTML_Tag_Processor` class for HTML parsing, which avoids regex-based content manipulation for the structural changes.

## Warning methods at a glance

| Method | Behaviour |
| --- | --- |
| **Inline** | Adds a visual indicator (icon ↗, text, or both) and screen reader text inside the link. No click interception. |
| **Modal** | Intercepts clicks on external links and shows a confirmation dialog. The user can continue (opens in a new window) or cancel. |
| **Redirect** | Intercepts clicks and navigates to an interstitial page with a countdown timer before redirecting to the external URL. |
| **Inline + Modal** | Combines inline indicators with the modal dialog on click. |
| **Inline + Redirect** | Combines inline indicators with the redirect interstitial on click. |

## What happens on deactivation

Deactivating the plugin removes all transients, flushes the rewrite rules cache, and clears the object cache. Your content is unaffected — links return to their original rendered state because the plugin never modifies stored content.

## Next steps

- [Settings Reference](settings-reference.md) — detailed breakdown of every option.
- [Styling Better External Links](styling.md) — customise colours, icons, and layout with CSS.
- [Developer Reference](developer-reference.md) — filters, actions, and PHP functions for integration.
