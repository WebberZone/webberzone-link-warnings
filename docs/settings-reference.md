# Settings Reference

All plugin settings are available at **Settings > Link Warnings**. The settings page is organised into three tabs: General, Display, and Advanced.

Settings are stored in a single WordPress option: `wzlw_settings`.

## General tab

### Warning Method

Controls how users are warned about external links.

| Value | Label | Behaviour |
| --- | --- | --- |
| `inline` | Inline indicators only | Adds visual indicators and screen reader text inside links. No click interception. |
| `modal` | Modal dialog | Intercepts clicks and shows a confirmation dialog. No inline indicators. |
| `redirect` | Redirect screen | Intercepts clicks and navigates to an interstitial page with a countdown. No inline indicators. |
| `inline_modal` | Inline indicators + Modal dialog | Adds inline indicators and intercepts clicks to show a modal. |
| `inline_redirect` | Inline indicators + Redirect screen | Adds inline indicators and intercepts clicks to show a redirect page. |

**Default:** `inline_modal`
**Setting key:** `warning_method`

### Inline Indicator Scope

Determines which links receive inline indicators. Modal and redirect warnings always apply to external links only.

| Value | Label | Behaviour |
| --- | --- | --- |
| `external` | External links only | Processes links whose host differs from the site host. |
| `both` | External links and internal links opening in a new tab | Processes external links and any internal link with `target="_blank"`. |

**Default:** `external`
**Setting key:** `scope`

### Enabled Post Types

Select which post types the plugin processes. Only singular views of the selected post types are affected. Archive pages, search results, and other non-singular views are not processed.

**Default:** `post, page`
**Setting key:** `enabled_post_types`

## Display tab

The Display tab is divided into three sections: Inline Indicators, Modal Dialog, and Redirect Screen.

### Inline Indicators

These settings control the visual indicators appended inside processed links. They apply when the warning method includes an inline component (`inline`, `inline_modal`, or `inline_redirect`).

#### Visual Indicator

| Value | Label | Output |
| --- | --- | --- |
| `icon` | Icon (↗) | Appends a `<span class="wzlw-icon">` whose content is rendered via CSS. |
| `text` | Text | Appends a `<span class="wzlw-text">` containing the configured indicator text. |
| `both` | Icon + text | Appends both the icon and text spans. |
| `none` | None (screen reader only) | No visible indicator. Only the screen reader text span is added. |

**Default:** `icon`
**Setting key:** `visual_indicator`

#### Icon Style

Selects which icon to display next to external links. Options include several built-in arrow and external link symbols, plus a Custom option that uses whatever you enter in the Custom Icon field.

**Default:** `arrow_ne`
**Setting key:** `icon_style`

#### Custom Icon

A custom icon character or symbol, used only when Icon Style is set to "Custom". Accepts Unicode symbols or emoji.

**Default:** empty
**Setting key:** `custom_icon`

#### Icon Color

The colour for the icon.

**Default:** `#595959`
**Setting key:** `icon_color`

#### Icon Background Color

Background colour for the icon. Leave empty for transparent.

**Default:** empty
**Setting key:** `icon_background`

#### Indicator Text

The visible text displayed next to links when the visual indicator is set to "Text" or "Icon + text".

**Default:** `(opens in new window)`
**Setting key:** `indicator_text`

#### Screen Reader Text

Hidden text added inside a `<span class="screen-reader-text">` element for assistive technology. This is always added to processed links regardless of the visual indicator setting.

**Default:** `Opens in a new window`
**Setting key:** `screen_reader_text`

### Modal Dialog

These settings control the confirmation dialog shown when the warning method includes a modal component (`modal` or `inline_modal`).

#### Modal Title

The heading displayed at the top of the modal dialog.

**Default:** `You are leaving this site`
**Setting key:** `modal_title`

#### Modal Message

The body text displayed in the modal dialog, below the title.

**Default:** `You are about to visit an external website. Continue?`
**Setting key:** `modal_message`

#### Continue Button Text

The label for the button that opens the external link.

**Default:** `Continue`
**Setting key:** `modal_continue_text`

#### Cancel Button Text

The label for the button that closes the modal and returns the user to the page.

**Default:** `Cancel`
**Setting key:** `modal_cancel_text`

### Redirect Screen

These settings control the interstitial redirect page shown when the warning method includes a redirect component (`redirect` or `inline_redirect`).

#### Redirect Message

The message displayed on the redirect page above the destination URL.

**Default:** `You are being redirected to an external site.`
**Setting key:** `redirect_message`

#### Redirect Countdown

The number of seconds before the page automatically redirects to the external URL. Set to `0` to disable the automatic redirect entirely — the user must click the "Continue to site" link manually.

**Default:** `5`
**Range:** 0–60
**Setting key:** `redirect_countdown`

## Advanced tab

### Excluded Domains

A list of domains (one per line) that should be treated as internal. Links pointing to these domains are not processed by the plugin, even if they would otherwise be classified as external.

Enter domain names without the protocol. For example:

```text
example.com
subdomain.example.com
trusted-partner.org
```

The matching is substring-based: if the link's host contains the excluded domain string, it is treated as internal.

**Default:** empty
**Setting key:** `excluded_domains`

### Force External Class

The CSS class that forces a link — or all links inside a wrapper element — to be treated as external, overriding automatic URL-based detection. Add this class directly to an `<a>` tag or to any containing element.

```html
<!-- Force a single internal link to be treated as external -->
<a class="wzlw-force-external" href="/affiliate/partner/">Partner link</a>

<!-- Force all links inside a wrapper to be treated as external -->
<div class="wzlw-force-external">
  <a href="/go/product-a/">Product A</a>
  <a href="/go/product-b/">Product B</a>
</div>
```

Links matched this way receive the same treatment as genuinely external links: the `wzlw-external` class, data attributes for modal/redirect handling, ARIA labels, and inline indicators.

Change this setting if your theme or another plugin already uses the default class name for a different purpose.

**Default:** `wzlw-force-external`
**Setting key:** `force_external_class`

## Programmatic access

All settings can be read and modified programmatically using the wrapper functions defined in `includes/options-api.php`:

```php
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
