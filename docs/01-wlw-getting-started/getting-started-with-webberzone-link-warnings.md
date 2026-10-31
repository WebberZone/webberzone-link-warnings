---
slug: getting-started-with-webberzone-link-warnings
title: "Getting started with WebberZone Link Warnings"
products: [link-warnings]
sections: ["01-wlw-getting-started"]
tags: [installation, link-warnings]
status: publish
order: 0
toc: true
---

[toc]

[WebberZone Link Warnings](https://webberzone.com/plugins/webberzone-link-warnings/) adds accessible warnings to external links and `target="_blank"` links in your WordPress content. It supports inline visual indicators, modal confirmation dialogs, and redirect interstitial screens — or combinations of these.

## Requirements

- WordPress 6.6 or later
- PHP 7.4 or later

## WordPress install (The easy way)

1. Navigate to **Plugins > Add New**.
2. Search for “WebberZone Link Warnings”.
3. Click **Install Now**, then **Activate**.

## Manual install

1. Download the plugin archive from [GitHub releases](https://github.com/WebberZone/webberzone-link-warnings/releases) or [WordPress.org](https://wordpress.org/plugins/webberzone-link-warnings/).
2. Upload the `webberzone-link-warnings` folder to `/wp-content/plugins/`.
3. Activate the plugin from the **Plugins** screen.

## Installing via WP CLI

Install and activate on a single site:

```bash
wp plugin install webberzone-link-warnings --activate
```

To activate across a multisite network:

```bash
wp plugin install webberzone-link-warnings --activate-network
```

## First-run wizard

On first activation, an admin notice appears offering a guided setup wizard. It runs through five sequential steps:

1. **Welcome** — introduction and the option to skip the wizard.
2. **General Settings** — warning method and link scope.
3. **Visual Indicators** — icon, text, both, or screen-reader-only, plus screen reader text.
4. **Modal Dialog** — title, message, button text, modal frequency, and dismissal scope.
5. **Redirect Screen** — message and countdown duration.

You can dismiss the wizard at any time and configure settings manually.

## Manual configuration

All settings are available at **Settings > Link Warnings**. The settings page is organized into four tabs:

- **General** — warning method, link scope, enabled post types, external content processing, and the Disable Warnings For list.
- **Display** — inline indicator options, modal dialog text, download modal text, and redirect screen text.
- **Rules** — link rules that give links matching a URL pattern their own warning method, or exclude them.
- **Advanced** — automatic link attributes (`rel`/`target`), affiliate link marking, download extensions, download warning scope, excluded domains, and CSS class overrides.

See the [Settings Reference](https://webberzone.com/support/knowledgebase/webberzone-link-warnings-settings-reference/) for a full description of every option.

## How content processing works

WebberZone Link Warnings does not modify your stored content. It filters rendered output using the `the_content` and `the_excerpt` hooks at priority 999, which means it runs after most other content filters.

For each `<a>` tag in the output, the plugin:

1. Checks whether the current post type is enabled in settings.
2. Checks the link against the Link Rules on the Rules tab. The first matching rule sets the link's warning method, or excludes it.
3. Determines whether the link is external by comparing its host against the site host and the excluded domains list, or whether it carries the Affiliate Link Class or sits inside an Affiliate Link Wrapper Class (affiliate links are always treated as external).
4. Applies the configured Link Attributes (`nofollow`, `sponsored`, `ugc`, `target="_blank"`, `noopener`, `noreferrer`) for external and/or affiliate links, merging into any existing `rel` value without duplicating.
5. Applies scope rules — external links only, or external links plus internal `target="_blank"` links. A link that just gained `target="_blank"` from step 4 is included here.
6. Adds CSS classes (`wzlw-processed`, `wzlw-external`) for styling.
7. For links that open in a new tab, appends the configured screen reader text to any existing `aria-label` attribute.
8. For modal and redirect methods, adds `data-wzlw-url` and either `data-wzlw-external` (external links) or `data-wzlw-blank` (processed non-external links opening in a new tab) — plus `data-wzlw-download` for configured download links and `data-wzlw-redirect-url` for the redirect methods specifically. These attributes are used by the frontend JavaScript.
9. For inline methods, appends the configured visual indicator markup inside the link. New-tab links also receive hidden screen reader text. Suppressed indicators and icon-only links can receive only the hidden text.

When enabled, separate server-side filters also process block, Text, and Custom HTML widgets, classic navigation menus, displayed comment text, and complete block-theme template parts. Each source has its own toggle in the **External Content** section of the **General** tab. The filters reuse the same link classification, exclusions, indicators, and warning methods as post content.

Enabled Post Types controls server processing of post content and excerpts on singular views. It does not disable the sitewide browser scan or the separately configured external-content filters.

The frontend JavaScript scan still processes navigation menus, widgets, footers, and other theme output that a theme or another plugin adds after the server-side filters run. It also covers markup that does not pass through a WordPress content filter.

## Download links

Links whose URL path ends in a configured file extension receive a download warning. By default this includes files hosted on your own site; set **Download Warning Scope** to **Download links to external sites only** to skip their download warnings. A matching rule, new-tab scope, or force-external/affiliate class can still cause a warning. The default extensions are `pdf`, `zip`, `doc`, `docx`, `xls`, `xlsx`, `exe`, and `dmg`.

Configure the list under **Advanced > Download Links**. Query strings and URL fragments are ignored when matching the file extension, and the separate **Download Modal Title** and **Download Modal Message** settings let download warnings use different text from external-link warnings.

The plugin uses WordPress’s native `WP_HTML_Tag_Processor` class for HTML parsing, which avoids regex-based content manipulation for the structural changes.

## Warning methods at a glance

| Method | Behavior |
| --- | --- |
| **Inline** | Adds configured visual indicators. Processed new-tab links also receive hidden screen reader text. No click interception. |
| **Modal** | Shows a confirmation dialog for matching links. The visitor can continue or cancel. |
| **Redirect** | Opens an interstitial page for matching links. Automatic redirection depends on the configured countdown, signature, destination, and arrival from your site. |
| **Inline + Modal** | Combines inline indicators with the modal dialog on click. |
| **Inline + Redirect** | Combines inline indicators with the redirect interstitial on click. |

## What happens on deactivation

Deactivating the plugin deletes the `wzlw_activation_redirect` transient, flushes the rewrite rules cache, and clears the object cache. Your content is unaffected — links return to their original rendered state because the plugin never modifies stored content.
