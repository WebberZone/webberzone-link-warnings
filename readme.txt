=== WebberZone Link Warnings ===
Contributors: webberzone, ajay
Tags: accessibility, external links, wcag, target blank, compliance
Donate link: https://ajaydsouza.com/donate/
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add accessible warnings for external links and `target="_blank"` links in WordPress — using icons, modals, or redirect screens.

== Description ==

WebberZone Link Warnings helps you warn users when links open in a new window or take them to external websites. It adds accessible indicators, confirmation dialogs, or redirect screens — helping you align with accessibility best practices without rewriting your content.

WebberZone Link Warnings uses WordPress's native `WP_HTML_Tag_Processor` class to parse and modify external and `target="_blank"` links in your content efficiently. It adds appropriate ARIA attributes, visual indicators, and optional JavaScript-based warnings based on your configuration. The plugin processes content during the `the_content` and `the_excerpt` filters, making it compatible with most themes and page builders. Your stored content remains untouched — the plugin only alters rendered output and does not interfere with REST API responses or admin editing screens.

### Why warn users about external links?

* `target="_blank"` can disorient screen reader users
* Sudden context changes impact usability
* Accessibility audits often recommend user warnings
* Agencies and site owners often need documented user warnings during accessibility reviews

### Key features

* __Multiple Warning Methods__: Choose from inline indicators, modal dialogs, or redirect screens — or combine them
* __Flexible Scope__: Target external links only, or external links plus all `target="_blank"` links
* __Customizable Indicators__: Configure visual icons, text, or screen reader-only warnings
* __Modal Dialog__: Show a confirmation dialog before users navigate to external sites with keyboard navigation and focus management
* __Redirect Screen__: Display an intermediate page with a configurable countdown before external navigation
* __Force External__: Add a class to any link or wrapper element to force it to be treated as external — useful for affiliate links or tracking URLs that use internal paths
* __Domain Exclusions__: Allow trusted domains to treat them as internal links
* __Post Type Control__: Enable warnings on specific post types only
* __Built to support accessibility best practices for external link behaviour in WordPress__: Adds screen reader text, ARIA attributes, and keyboard-friendly modal confirmations
* __Setup Wizard__: Get started quickly with a guided setup wizard on first activation
* __Template Override__: Override the redirect screen template in your theme for full design control
* __RTL Support__: Full right-to-left language support for all frontend and admin styles
* __Multisite Compatible__: Network activate and configure per-site settings
* __Privacy Focused__: Does not collect personal data or send link data to third-party services
* __Performance Optimized__: Uses WordPress's native `WP_HTML_Tag_Processor` class to process links at display time
* __Developer-Friendly__: Filters and actions allow developers to customize behavior, exclude domains, and output

> This plugin assists with user awareness of external navigation. It does not automatically make your website fully accessible or legally compliant.

### How it works

After activation, the setup wizard guides you through the initial configuration. You can also configure the plugin at __Settings > WebberZone Link Warnings__.

__Warning Methods:__

* Inline indicators only (visual and/or screen reader text)
* Modal dialog (JavaScript-based confirmation)
* Redirect screen (intermediate page with countdown)
* Combined approach (inline + modal)
* Combined approach (inline + redirect)

__Link Scope:__

* External links only
* External links and all `target="_blank"` links

__Visual Indicators:__

* Icon only (↗)
* Text only (customizable)
* Icon + text
* None (screen reader only)

__Advanced Settings:__

* Custom modal messages and button text
* Custom redirect page content and countdown duration
* Domain exclusion list
* Post type selection
* Force-external class name (default: `wzlw-force-external`)

### GDPR

WebberZone Link Warnings doesn't collect personal data or send information to external services — making it GDPR-friendly by default.

You remain responsible for your site's overall GDPR compliance.

### Contribute

WebberZone Link Warnings is also available on [Github](https://github.com/WebberZone/webberzone-link-warnings).
So, if you've got a cool feature you'd like to implement in the plugin or a bug you've fixed, consider forking the project and sending me a pull request.

Bug reports are [welcomed on GitHub](https://github.com/WebberZone/webberzone-link-warnings/issues). Please note that GitHub is _not_ a support forum, and issues that aren't suitably qualified as bugs will be closed.

### Translations

WebberZone Link Warnings is available for [translation directly on WordPress.org](https://translate.wordpress.org/projects/wp-plugins/webberzone-link-warnings). Check out the official [Translator Handbook](https://make.wordpress.org/polyglots/handbook/plugin-theme-authors-guide/) to contribute.

### Other Plugins by WebberZone

WebberZone Link Warnings is one of the many plugins developed by WebberZone. Check out our other plugins:

* [Contextual Related Posts](https://wordpress.org/plugins/contextual-related-posts/) - Display related posts on your WordPress blog and feed
* [Top 10](https://wordpress.org/plugins/top-10/) - Track daily and total visits to your blog posts and display the popular and trending posts
* [WebberZone Snippetz](https://wordpress.org/plugins/add-to-all/) - The ultimate snippet manager for WordPress to create and manage custom HTML, CSS or JS code snippets
* [Knowledge Base](https://wordpress.org/plugins/knowledgebase/) - Create a knowledge base or FAQ section on your WordPress site
* [Better Search](https://wordpress.org/plugins/better-search/) - Enhance the default WordPress search with contextual results sorted by relevance
* [Auto-Close](https://wordpress.org/plugins/autoclose/) - Automatically close comments, pingbacks and trackbacks and manage revisions
* [Popular Authors](https://wordpress.org/plugins/popular-authors/) - Display popular authors in your WordPress widget
* [Followed Posts](https://wordpress.org/plugins/where-did-they-go-from-here/) - Show a list of related posts based on what your users have read

== Installation ==

= WordPress install (the easy way) =

1. Navigate to Plugins > Add New
2. Search for "WebberZone Link Warnings"
3. Click "Install Now" and then "Activate"

= Manual install =

1. Upload the `webberzone-link-warnings` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > WebberZone Link Warnings to configure

== Frequently Asked Questions ==

= Does this plugin affect SEO? =

No. WebberZone Link Warnings only modifies how links are displayed to users. It does not alter the href attribute, link structure, or indexing behaviour. Search engines see your links exactly as you created them.

= Is it accessible? =

Yes. The plugin adds screen reader text, ARIA attributes, and (for modal mode) keyboard navigation and focus management.

= Does it work with multilingual sites? =

Yes. WebberZone Link Warnings is translation-ready. It uses standard WordPress translation functions and works with popular multilingual plugins such as WPML and Polylang.

= Does this work with page builders? =

Yes. The plugin processes content through standard WordPress filters (`the_content` and `the_excerpt`), making it compatible with most themes, page builders, and the block editor.

= Can I customize the redirect screen template? =

Yes. Copy the template file to `your-theme/webberzone-link-warnings/redirect-screen.php` to override the default redirect screen with your own design.

= How can I force an internal link to be treated as external? =

Add the class `wzlw-force-external` directly to the `<a>` tag:

`<a href="/go/partner/" class="wzlw-force-external">Partner link</a>`

To force all links inside a container, add `wzlw-force-external-wrapper` to the wrapper element instead:

`<div class="wzlw-force-external-wrapper"><a href="/go/product-a/">Product A</a><a href="/go/product-b/">Product B</a></div>`

Both class names are configurable under Settings > WebberZone Link Warnings > Advanced.

= How can I prevent icons from appearing on specific links? =

Add the class `wzlw-no-icon` to any link where you don't want an icon to appear. For example:

`<a href="https://example.com" class="wzlw-no-icon">Link without icon</a>`

This will exclude the link from having visual indicators while still maintaining other accessibility features like screen reader text and ARIA attributes.

To exclude all links inside a wrapper element (e.g. a navigation block or card), add the class `wzlw-no-icon-wrapper` to the containing element:

`<div class="wzlw-no-icon-wrapper"><a href="https://example.com">Link without icon</a></div>`

All links inside that wrapper will be excluded from visual indicators.

= Can I customize the icon that appears? =

Yes. Go to Settings > WebberZone Link Warnings > Display tab and find the "Icon Style" dropdown. You can choose from several preset icons:

- ↗ Arrow (North-East) - default
- → Arrow (Right)
- ⬈ Arrow (Up-Right)
- ⧉ External Link Symbol
- 🔗 Link Emoji
- 🌐 Globe Emoji
- * Asterisk
- Custom (enter your own)

**Using Custom Icons:** Select "Custom" and enter any Unicode symbol or emoji in the "Custom Icon" field. Examples: →, ⇗, 🔗, 🌐, *, +

= Does this modify my database content? =

No. The plugin only alters rendered output. Your stored content remains unchanged.

= What happens if I deactivate the plugin? =

Your links return to their original state. The plugin doesn't modify your content in the database — it only changes how links are displayed.

= Where do I report security bugs found in this plugin? =

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/webberzone-link-warnings)

== Screenshots ==

1. External link with icon indicator
2. Modal dialog warning before navigation

== Changelog ==

= 1.2.0 (14 April 2026) =

[Version 1.2.0 Release Post](https://webberzone.com/announcements/link-warnings-v1-2-0/)

**New Features**

* Add `wzlw-force-external` / `wzlw-force-external-wrapper` class support to force links to be treated as external regardless of automatic detection. Both class names are configurable under Settings > Advanced.
* The `wzlw-no-icon` and `wzlw-no-icon-wrapper` class names are now configurable under Settings > Advanced.

**Security**

* Redirect endpoint now requires an HMAC signature on every URL. Unsigned or tampered URLs are rejected, preventing open-redirect abuse.

**Bug Fixes**

* Redirect URLs with HMAC signatures were broken due to double-encoding of the `&` separator in HTML output.
* Redundant URL encoding in `get_redirect_url()` caused malformed redirect URLs.
* Same-host check now normalises hostnames before comparison, so variants like `EXAMPLE.COM` or `example.com.` are treated as internal.
* Excluded domains now match correctly when entered with a scheme or trailing path.

= 1.1.0 (14 March 2026) =

[Version 1.1.0 Release Post](https://webberzone.com/announcements/link-warnings-v1-1-0/)

**New Features**

* Add `wzlw-no-icon-wrapper` class support — add it to any wrapper element to exclude all links inside it from visual indicators.

**Improvements**

* Enhanced modal accessibility: background content is now hidden from screen readers when the modal is open, URL display includes a screen reader label, buttons have fallback accessible names, and the Continue button announces "opens in a new window" for `target="_blank"` links.

= 1.0.0 (7 March 2026) =

[Version 1.0.0 Release Post](https://webberzone.com/announcements/link-warnings-v1-0-0/)

* Initial release.

== Upgrade Notice ==

= 1.2.0 =
Adds `wzlw-force-external` class support, makes the `wzlw-no-icon` / `wzlw-no-icon-wrapper` class names configurable, and fixes an open-redirect vulnerability in the redirect endpoint.

= 1.1.0 =
Adds support for excluding links inside wrapper elements using the `wzlw-no-icon-wrapper` class.
