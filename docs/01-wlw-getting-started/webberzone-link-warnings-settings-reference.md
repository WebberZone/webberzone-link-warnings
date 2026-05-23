---
id: 9735
slug: webberzone-link-warnings-settings-reference
title: "WebberZone Link Warnings Settings Reference"
products: [link-warnings]
sections: [01-wlw-getting-started]
status: publish
order: 0
---

<div class="wp-block-kadence-tableofcontents">

</div>

This document describes all available settings for the WebberZone Link Warnings plugin. All plugin settings are available at **Settings \> Link Warnings**. The settings page is organised into three tabs: General, Display, and Advanced.

Settings are stored in a single WordPress option: `wzlw_settings`.

## General tab

### Warning Method

Controls how users are warned about external links.

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Value</th>
<th>Label</th>
<th>Behaviour</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>inline</code></td>
<td>Inline indicators only</td>
<td>Adds visual indicators and screen reader text inside links. No click interception.</td>
</tr>
<tr>
<td><code>modal</code></td>
<td>Modal dialog</td>
<td>Intercepts clicks and shows a confirmation dialog. No inline indicators.</td>
</tr>
<tr>
<td><code>redirect</code></td>
<td>Redirect screen</td>
<td>Intercepts clicks and navigates to an interstitial page with a countdown. No inline indicators.</td>
</tr>
<tr>
<td><code>inline_modal</code></td>
<td>Inline indicators + Modal dialog</td>
<td>Adds inline indicators and intercepts clicks to show a modal.</td>
</tr>
<tr>
<td><code>inline_redirect</code></td>
<td>Inline indicators + Redirect screen</td>
<td>Adds inline indicators and intercepts clicks to show a redirect page.</td>
</tr>
</tbody>
</table>
</figure>

**Default:** `inline_modal`\
**Setting key:** `warning_method`

### Inline Indicator Scope

Determines which links receive inline indicators. Modal and redirect warnings always apply to external links only.

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Value</th>
<th>Label</th>
<th>Behaviour</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>external</code></td>
<td>External links only</td>
<td>Processes links whose host differs from the site host.</td>
</tr>
<tr>
<td><code>both</code></td>
<td>External links and internal links opening in a new tab</td>
<td>Processes external links and any internal link with <code>target="_blank"</code>.</td>
</tr>
</tbody>
</table>
</figure>

**Default:** `external`\
**Setting key:** `scope`

### Enabled Post Types

Select which post types the plugin processes. Only singular views of the selected post types are affected. Archive pages, search results, and other non-singular views are not processed.

**Default:** `post, page`\
**Setting key:** `enabled_post_types`

## Display tab

The Display tab is divided into three sections: Inline Indicators, Modal Dialog, and Redirect Screen.

### Inline Indicators

These settings control the visual indicators appended to processed links. They apply when the warning method includes an inline component (`inline`, `inline_modal`, or `inline_redirect`).

#### Visual Indicator

<figure class="wp-block-table">
<table class="has-fixed-layout">
<thead>
<tr>
<th>Value</th>
<th>Label</th>
<th>Output</th>
</tr>
</thead>
<tbody>
<tr>
<td><code>icon</code></td>
<td>Icon (↗)</td>
<td>Appends a <code>&lt;span class="wzlw-icon"&gt;</code> whose content is rendered via CSS.</td>
</tr>
<tr>
<td><code>text</code></td>
<td>Text</td>
<td>Appends a <code>&lt;span class="wzlw-text"&gt;</code> containing the configured indicator text.</td>
</tr>
<tr>
<td><code>both</code></td>
<td>Icon + text</td>
<td>Appends both the icon and text spans.</td>
</tr>
<tr>
<td><code>none</code></td>
<td>None (screen reader only)</td>
<td>No visible indicator. Only the screen reader text span is added.</td>
</tr>
</tbody>
</table>
</figure>

**Default:** `icon`\
**Setting key:** `visual_indicator`

#### Icon Style

Selects which icon to display next to external links. Options include several built-in arrow and external link symbols, plus a Custom option that uses whatever you enter in the Custom Icon field.

**Default:** `arrow_ne`\
**Setting key:** `icon_style`

#### Custom Icon

A custom icon character or symbol, used only when Icon Style is set to “Custom”. Accepts Unicode symbols or emoji.

**Default:** empty\
**Setting key:** `custom_icon`

#### Icon Color

The colour for the icon.

**Default:** `#595959`\
**Setting key:** `icon_color`

#### Icon Background Color

Background colour for the icon. Leave empty for transparent.

**Default:** empty\
**Setting key:** `icon_background`

#### Indicator Text

The visible text displayed next to links when the visual indicator is set to “Text” or “Icon + text”.

**Default:** `(opens in new window)`\
**Setting key:** `indicator_text`

#### Screen Reader Text

Hidden text added inside a `<span class="screen-reader-text">` element for assistive technology. This is always added to processed links regardless of the visual indicator setting.

**Default:** `Opens in a new window`\
**Setting key:** `screen_reader_text`

### Modal Dialog

These settings control the confirmation dialog shown when the warning method includes a modal component (`modal` or `inline_modal`).

#### Modal Title

The heading displayed at the top of the modal dialog.

**Default:** `You are leaving this site`\
**Setting key:** `modal_title`

#### Modal Message

The body text displayed in the modal dialog, below the title.

**Default:** `You are about to visit an external website. Continue?`\
**Setting key:** `modal_message`

#### Continue Button Text

The label for the button that opens the external link.

**Default:** `Continue`\
**Setting key:** `modal_continue_text`

#### Cancel Button Text

The label for the button that closes the modal and returns the user to the page.

**Default:** `Cancel`\
**Setting key:** `modal_cancel_text`

### Redirect Screen

These settings control the interstitial redirect page shown when the warning method includes a redirect component (`redirect` or `inline_redirect`).

#### Redirect Message

The message displayed on the redirect page above the destination URL.

**Default:** `You are being redirected to an external site.`\
**Setting key:** `redirect_message`

#### Redirect Countdown

The number of seconds before the page automatically redirects to the external URL. Set to `0` to disable the automatic redirect entirely — the user must click the “Continue to site” link manually.

**Default:** `5`\
**Range:** 0–60\
**Setting key:** `redirect_countdown`

## Advanced tab

### Excluded Domains

A list of domains (one per line) that should be treated as internal. Links pointing to these domains are not processed by the plugin, even if they would otherwise be classified as external.

Enter domain names without the protocol. Two entry formats are supported:

**Plain entry** — matches that exact domain only:

``` wp-block-code
example.com
```

`example.com` matches `https://example.com/` but not `https://sub.example.com/`.

**Wildcard entry** — matches subdomains only, not the base domain:

``` wp-block-code
*.example.com
```

`*.example.com` matches `https://sub.example.com/` and `https://deep.sub.example.com/` but not `https://example.com/`.

To exclude a domain and all its subdomains, add both entries:

``` wp-block-code
example.com
*.example.com
```

**Default:** empty\
**Setting key:** `excluded_domains`

### Suppress Icon Class

The CSS class name that suppresses the visual indicator when added directly to an `<a>` tag. The icon and modal are hidden for that link; screen reader text is still added if the link opens in a new tab.

Accepts a comma-separated list of class names. A link carrying any of the listed classes is treated as a match.

**Default:** `wzlw-no-icon`\
**Setting key:** `no_icon_class`

### Suppress Icon Wrapper Class

The CSS class name that suppresses visual indicators on all links inside a wrapper element. Add it to any containing element to exclude every link inside it.

Accepts a comma-separated list of class names.

**Default:** `wzlw-no-icon-wrapper`\
**Setting key:** `no_icon_wrapper_class`

### Force External Class

The CSS class name that forces a specific link to be treated as external, regardless of its URL. Add it directly to an `<a>` tag.

Accepts a comma-separated list of class names.

**Default:** `wzlw-force-external`\
**Setting key:** `force_external_class`

### Force External Wrapper Class

The CSS class name that forces all links inside a wrapper element to be treated as external. Add it to any containing element.

Accepts a comma-separated list of class names.

**Default:** `wzlw-force-external-wrapper`\
**Setting key:** `force_external_wrapper_class`

## Programmatic access

All settings can be read and modified programmatically using the wrapper functions defined in `includes/options-api.php`:

``` wp-block-code
// Get all settings (merged with defaults).
$settings = wzlw_get_settings();

// Get a single setting with an optional fallback.
$method = wzlw_get_option( 'warning_method', 'inline' );

// Update a single setting.
wzlw_update_option( 'warning_method', 'modal' );

// Reset all settings to defaults.
wzlw_settings_reset();
```

See the [Developer Reference](developer-reference.md) for the full list of functions and filter hooks.
