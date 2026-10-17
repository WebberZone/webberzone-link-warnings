---
slug: webberzone-link-warnings-settings-reference
title: "WebberZone Link Warnings Settings Reference"
products: [link-warnings]
sections: ["01-wlw-getting-started"]
tags: [link-warnings, settings]
status: publish
order: 0
toc: true
---

[toc]

This document describes all available settings for the [WebberZone Link Warnings](https://webberzone.com/plugins/webberzone-link-warnings/) plugin. All plugin settings are available at **Settings > Link Warnings**. The settings page is organized into four tabs: General, Display, Rules, and Advanced.

Settings are stored in a single WordPress option: `wzlw_settings`.

## General tab

### Warning Method

Controls how users are warned about external links.

| Value | Label | Behavior |
| --- | --- | --- |
| `inline` | Inline indicators only | Adds visual indicators and screen reader text inside links. No click interception. |
| `modal` | Modal dialog | Intercepts clicks and shows a confirmation dialog. No inline indicators. |
| `redirect` | Redirect screen | Intercepts clicks and navigates to an interstitial page with a countdown. No inline indicators. |
| `inline_modal` | Inline indicators + Modal dialog | Adds inline indicators and intercepts clicks to show a modal. |
| `inline_redirect` | Inline indicators + Redirect screen | Adds inline indicators and intercepts clicks to show a redirect page. |

**Default:** `inline_modal`
**Setting key:** `warning_method`

### Inline Indicator Scope

Determines which links receive warnings. With `both`, modal and redirect warnings also apply to internal links that open in a new tab.

| Value | Label | Behavior |
| --- | --- | --- |
| `external` | External links only | Processes links whose host differs from the site host. |
| `both` | External links and internal links opening in a new tab | Processes external links and any internal link with `target="_blank"`. |

**Default:** `external`
**Setting key:** `scope`

### Enabled Post Types

Select which post types the plugin processes. Only singular views of the selected post types are affected. Archive pages, search results, and other non-singular views are not processed.

**Default:** `post, page`
**Setting key:** `enabled_post_types`

### External Content

These toggles control the server-side filters that process links outside post content. They are enabled by default. The sitewide JavaScript scan can still process markup added later by a theme or another plugin.

#### Process Widget Output

Processes links in block, Text, and Custom HTML widget output before it is sent to the browser.

**Default:** enabled
**Setting key:** `process_widgets`

#### Process Navigation Menus

Processes links in classic navigation menu output before it is sent to the browser. Links in block-theme Navigation blocks are covered by Process Block Theme Template Parts when the block is rendered inside a template part.

**Default:** enabled
**Setting key:** `process_nav_menus`

#### Process Comments

Processes links in displayed comment text.

**Default:** enabled
**Setting key:** `process_comments`

#### Process Block Theme Template Parts

Processes the rendered output of block-theme template parts. This covers links in headers, footers, and other template parts without processing every individual block twice.

**Default:** enabled
**Setting key:** `process_template_parts`

### Disable Warnings For

Hides warnings from logged-in users, for example while you edit your own site. Tick **All logged-in users**, or tick individual roles. Visitors who are not logged in always see warnings.

For the users you select, the plugin adds no visible indicators, data attributes or modal markup, and link rules do not apply. The configured Link Attributes are still added, and links that open in a new tab keep their hidden screen reader text, so screen reader users with an account are still told when a new tab opens.

Most page caches skip logged-in users. If yours caches pages for logged-in users, it may serve a cached page with or without warnings to the wrong users.

**Default:** none selected
**Setting key:** `disable_for` (values: `logged_in`, or `role:` followed by the role slug, such as `role:administrator`)

## Display tab

The Display tab is divided into three sections: Inline Indicators, Modal Dialog, and Redirect Screen.

### Inline Indicators

These settings control the visual indicators appended to processed links. They apply when the warning method includes an inline component (`inline`, `inline_modal`, or `inline_redirect`).

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

A custom icon character or symbol, used only when Icon Style is set to “Custom”. Accepts Unicode symbols or emoji.

**Default:** empty
**Setting key:** `custom_icon`

#### Icon Color

The color for the icon.

**Default:** `#595959`
**Setting key:** `icon_color`

#### Icon Background Color

Background color for the icon. Leave empty for transparent.

**Default:** empty
**Setting key:** `icon_background`

#### Indicator Text

The visible text displayed next to links when the visual indicator is set to “Text” or “Icon + text”.

**Default:** `(opens in new window)`
**Setting key:** `indicator_text`

#### Screen Reader Text

Hidden text added inside a `<span class="screen-reader-text">` element for assistive technology. It is added to processed links that open in a new tab (`target="_blank"`), regardless of the visual indicator setting. Links that open in the same tab do not get it.

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

#### Download Modal Title

The heading displayed when a visitor follows a link whose URL path ends in a configured downloadable file extension.

**Default:** `You are about to download a file`
**Setting key:** `download_modal_title`

#### Download Modal Message

The body text displayed for configured downloadable file links.

**Default:** `This link will download a file. Continue?`
**Setting key:** `download_modal_message`

#### Continue Button Text

The label for the button that opens the external link.

**Default:** `Continue`
**Setting key:** `modal_continue_text`

#### Cancel Button Text

The label for the button that closes the modal and returns the user to the page.

**Default:** `Cancel`
**Setting key:** `modal_cancel_text`

#### Modal Frequency

Controls how often the modal is shown to the same visitor. Leaving this on **Always show the modal** keeps the original behavior — every click on an external link opens the modal.

Choosing either of the other options adds a **Don't show again** checkbox to the modal. When a visitor ticks it and clicks Continue, the dismissal is stored in their browser and the modal is skipped for subsequent clicks — the link then behaves like a normal link.

- **Always show the modal** — no checkbox, the modal always appears.
- **Once per browser session** — the dismissal is stored in `sessionStorage` and cleared when the browser tab is closed.
- **Once every N days** — the dismissal is stored in `localStorage` and expires after the number of days set below.

Dismissals are stored per browser, not per user account, so nothing is written to your database.

**Default:** `always`
**Setting key:** `modal_frequency`

#### Remember Dismissal For

The number of days a dismissal is remembered. Only used when the frequency is set to **Once every N days**.

**Default:** `30`
**Range:** 1–365
**Setting key:** `modal_frequency_days`

#### Dismissal Scope

Determines how broadly a dismissal applies.

- **Per destination domain** — dismissing the warning for `example.com` suppresses the modal for that domain only. Other external links still show the warning.
- **All external links** — a single dismissal suppresses the modal for every external link on the site.

**Default:** `domain`
**Setting key:** `modal_frequency_scope`

#### Don't Show Again Label

The label displayed next to the checkbox in the modal.

**Default:** `Don't show this warning again`
**Setting key:** `modal_dismiss_text`

### Redirect Screen

These settings control the interstitial redirect page shown when the warning method includes a redirect component (`redirect` or `inline_redirect`).

#### Redirect Message

The message displayed on the redirect page above the destination URL.

**Default:** `You are being redirected to an external site.`
**Setting key:** `redirect_message`

#### Redirect Countdown

The number of seconds before the page automatically redirects to the external URL. Set to `0` to disable the automatic redirect entirely — the user must click the “Continue to site” link manually.

The countdown only runs for visitors who reach the redirect screen from a page on your site. Browsers report this in the `Sec-Fetch-Site` header, which is sent even with `rel="noreferrer"` or `Referrer-Policy: no-referrer`; older browsers without it fall back to the `Referer` header. Opened from an email, a bookmark or another site, the screen waits for the visitor to click “Continue to site”. Links in comments, links from previews and links with `rel="ugc"` always wait for the click, unless they point to your own site or match a redirect rule. Together, this stops anyone from using your redirect screen as an automatic redirect to a site of their choosing.

The redirect screen is never cached. Redirect links in cached pages keep working after you change the warning method or rules. Redirect links signed by versions before 1.7.0 still open the redirect screen but always wait for the click.

**Default:** `5`
**Range:** 0–60
**Setting key:** `redirect_countdown`

## Rules tab

### Link Rules

An ordered list of rules that give links matching a URL pattern their own warning. Rules are checked from top to bottom and the first match wins. Links that match no rule use the settings on the other tabs.

Each rule has three fields:

| Field | Description |
| --- | --- |
| URL Pattern | The pattern to match. See below. |
| Action | `modal`, `inline`, `inline_modal`, `redirect`, `inline_redirect`, or `exclude`. The first five are the same as the Warning Method options. `exclude` gives the link no warning. |
| Message | Optional. Replaces the modal message, or the redirect screen message, for links that match this rule. |

**Pattern syntax.** `*` matches any characters and is the only wildcard. Matching ignores the case of ASCII letters, the `http://` or `https://` scheme, ports and fragments.

| Pattern | Matches |
| --- | --- |
| `example.com` | Every link to `example.com`, whatever the path. Not its subdomains. |
| `*.example.com` | Every link to a subdomain of `example.com`. Not `example.com` itself. |
| `example.com/go/*` | Links to `example.com` whose path starts with `/go/`. |
| `amazon.*/dp/*` | Product links on any `amazon.` domain, such as `amazon.com` or `amazon.co.uk`. |
| `/go/*` | Links on your own site whose path starts with `/go/`, such as cloaked affiliate links. |

A pattern without a slash matches the domain only. A pattern with a slash matches the domain and the path, including any query string, so it must match to the end: use a trailing `*` to match everything below a path. Relative links that do not start with a slash, such as `page/`, are not matched.

**How rules combine with other settings:**

* A rule with a warning action applies to the link even if it is internal, outside the Inline Indicator Scope, or on the Excluded Domains list.
* An `exclude` rule behaves like an Excluded Domains entry. The Affiliate Link and Force External classes still override it.
* The Suppress Icon class and wrapper class still hide the icon on a matching link.
* The modal markup and the redirect screen are enabled when any rule uses them, even if the Warning Method does not.

**Default:** no rules
**Setting key:** `link_rules`

## Advanced tab

### Download Links

#### Downloadable File Extensions

A comma-separated list of file extensions whose links should receive a download warning. Matching uses the URL path, is case-insensitive, and ignores query strings and fragments. Internal and external URLs are both supported. Do not include the leading dot.

**Default:** `pdf, zip, doc, docx, xls, xlsx, exe, dmg`
**Setting key:** `download_extensions`

#### Download Warning Scope

Chooses which download links get a download warning.

| Value | Label | Behavior |
| --- | --- | --- |
| `all` | All download links | Every link to a configured file extension, whatever the host. |
| `external` | Download links to external sites only | Skips files on your own site, including root-relative links such as `/wp-content/uploads/file.pdf`. Links with the Force External or Affiliate Link class still count as external. |

**Default:** `all`
**Setting key:** `download_scope`

### Link Attributes

Automatically add `rel` and `target` attributes to matching links at render time. Two independent sets of checkboxes are available — one for **External Links**, one for **Affiliate Links** (see Affiliate Link Class below) — so you can, for example, add `nofollow` to affiliate links only while leaving other external links untouched.

| Option | Adds |
| --- | --- |
| Add rel="nofollow" | `rel="nofollow"` |
| Add rel="sponsored" | `rel="sponsored"` |
| Add rel="ugc" | `rel="ugc"` |
| Open in a new tab | `target="_blank"` |
| Add rel="noopener" for new-tab links | `rel="noopener"` — only on links that open in a new tab (existing `target="_blank"` or the option above). |
| Add rel="noreferrer" for new-tab links | `rel="noreferrer"` — same condition as `noopener`. This also stops the referrer being sent, which can break referrer-based affiliate attribution. |

A link that already has a `rel` value keeps it — new values are appended, and matching ignores case, so a link with `rel="NoFollow"` is not given a duplicate.

Every option is off by default; nothing is added until you tick it.

**Default:** none selected
**Setting keys:** `link_attributes_external`, `link_attributes_affiliate`

### Affiliate Link Class

The CSS class that marks a specific link as an affiliate link. Add this class directly to an `<a>` tag. A link with this class also receives the **Affiliate Links** attribute set above, and is treated as external for warning purposes even if it points to your own domain (the same way the Force External Class does).

Accepts a comma-separated list of class names.

**Default:** `wzlw-affiliate`
**Setting key:** `affiliate_class`

### Affiliate Link Wrapper Class

The CSS class that marks every link inside a wrapper element as an affiliate link. Add it to any containing element.

Accepts a comma-separated list of class names.

**Default:** `wzlw-affiliate-wrapper`
**Setting key:** `affiliate_wrapper_class`

### Excluded Domains

A list of domains (one per line) that should be treated as internal. Links pointing to these domains are not processed by the plugin, even if they would otherwise be classified as external.

Enter domain names without the protocol. Two entry formats are supported:

**Plain entry** — matches that exact domain only:

```text
example.com
```

`example.com` matches `https://example.com/` but not `https://sub.example.com/`.

**Wildcard entry** — matches subdomains only, not the base domain:

```text
*.example.com
```

`*.example.com` matches `https://sub.example.com/` and `https://deep.sub.example.com/` but not `https://example.com/`.

To exclude a domain and all its subdomains, add both entries:

```text
example.com
*.example.com
```

**Default:** empty
**Setting key:** `excluded_domains`

### Suppress Icon Class

The CSS class name that suppresses the visual indicator (icon and/or text) when added directly to an `<a>` tag. The modal or redirect warning still applies if your warning method includes one. Screen reader text is still added if the link opens in a new tab.

Accepts a comma-separated list of class names. A link carrying any of the listed classes is treated as a match.

**Default:** `wzlw-no-icon`
**Setting key:** `no_icon_class`

### Suppress Icon Wrapper Class

The CSS class name that suppresses visual indicators on all links inside a wrapper element. Add it to any containing element to exclude every link inside it.

Accepts a comma-separated list of class names.

**Default:** `wzlw-no-icon-wrapper`
**Setting key:** `no_icon_wrapper_class`

### Force External Class

The CSS class name that forces a specific link to be treated as external, regardless of its URL. Add it directly to an `<a>` tag.

Accepts a comma-separated list of class names.

**Default:** `wzlw-force-external`
**Setting key:** `force_external_class`

### Force External Wrapper Class

The CSS class name that forces all links inside a wrapper element to be treated as external. Add it to any containing element.

Accepts a comma-separated list of class names.

**Default:** `wzlw-force-external-wrapper`
**Setting key:** `force_external_wrapper_class`

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

See the [Developer Reference](https://webberzone.com/support/knowledgebase/webberzone-link-warnings-developer-reference/) for the full list of functions and filter hooks.
