=== WebberZone Link Warnings ===
Contributors: webberzone, ajay
Tags: accessibility, external links, wcag, target blank, compliance
Donate link: https://ajaydsouza.com/donate/
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add accessible warnings for external links and `target="_blank"` links in WordPress — using icons, modals, or redirect screens.

== Description ==

WebberZone Link Warnings helps you warn users when links open in a new window or take them to external websites. It adds accessible indicators, confirmation dialogs, or redirect screens — helping you align with accessibility best practices without rewriting your content.

WebberZone Link Warnings uses a two-layer approach to process links across your entire site. For post content, it uses WordPress's native `WP_HTML_Tag_Processor` class to efficiently parse and modify links at render time. Server-side filters also process widget output, navigation menus, comment text, and block-theme template parts when their General settings are enabled. A lightweight JavaScript scan handles remaining theme output after the page loads and applies the same rules. Your stored content remains untouched — the plugin only alters rendered output and does not interfere with REST API responses or admin editing screens.

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
* __Dismissible Modals__: Let repeat visitors tick "Don't show again" so the modal is skipped for a session or for a set number of days, per destination domain or sitewide
* __Redirect Screen__: Display an intermediate page with a configurable countdown before external navigation
* __Force External__: Add a class to any link or wrapper element to force it to be treated as external — useful for tracking URLs or redirects that use internal paths
* __Automatic Link Attributes__: Add `nofollow`, `sponsored`, `ugc`, `noopener`, `noreferrer` and `target="_blank"` to external links, affiliate links, or both — no post edits needed
* __Affiliate Link Marking__: Flag a link or container with a class and give affiliate links their own attributes
* __Domain Exclusions__: Allow trusted domains to treat them as internal links
* __Link Rules__: Give links that match a URL pattern, such as `amazon.com/dp/*` or `/go/*`, their own warning method and message, or exclude them
* __Hide Warnings While You Edit__: Turn warnings off for logged-in users or selected roles
* __Sitewide Coverage__: Server-side filters process widget output, navigation menus, comment text, and block-theme template parts, while the JavaScript scan covers remaining theme output
* __Post Type Control__: PHP-side processing is scoped to configured post types; JS scanning covers the full page regardless
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
* Modal frequency, dismissal scope and "Don't show again" label
* Custom redirect page content and countdown duration
* Link attributes for external and affiliate links (`nofollow`, `sponsored`, `ugc`, open in a new tab, `noopener`, `noreferrer`)
* Affiliate link class and wrapper class (defaults: `wzlw-affiliate`, `wzlw-affiliate-wrapper`)
* Domain exclusion list
* Link rules by URL pattern, each with its own warning method and message
* Download warnings for all files, or for files on external sites only
* Warnings hidden from logged-in users or selected roles
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

Out of the box, no. WebberZone Link Warnings only modifies how links are displayed to users and never alters the `href` attribute or link structure.

If you enable the __Link Attributes__ settings under Settings > WebberZone Link Warnings > Advanced, the plugin will add `rel` and `target` attributes to matching links, which is a deliberate SEO signal that you control. Attributes on links inside post content are written server-side and are present in the HTML that search engines download. Attributes on links elsewhere on the page — navigation menus, widgets, footers and other theme output — are applied by the JavaScript scan after the page loads, so they are only visible to crawlers that execute JavaScript.

= Is it accessible? =

Yes. The plugin adds screen reader text, ARIA attributes, and (for modal mode) keyboard navigation and focus management.

= Does it work with multilingual sites? =

Yes. WebberZone Link Warnings is translation-ready. It uses standard WordPress translation functions and works with popular multilingual plugins such as WPML and Polylang.

= Does this work with page builders? =

Yes. Post content is processed via standard WordPress filters (`the_content` and `the_excerpt`). In addition, a JavaScript scan runs after the page loads and processes any links not already handled by PHP — including output generated by page builders, theme templates, navigation menus, and widgets.

= Can I customize the redirect screen template? =

Yes. Copy the template file to `your-theme/webberzone-link-warnings/redirect-screen.php` to override the default redirect screen with your own design.

= How can I force an internal link to be treated as external? =

Add the class `wzlw-force-external` directly to the `<a>` tag:

`<a href="/go/partner/" class="wzlw-force-external">Partner link</a>`

To force all links inside a container, add `wzlw-force-external-wrapper` to the wrapper element instead:

`<div class="wzlw-force-external-wrapper"><a href="/go/product-a/">Product A</a><a href="/go/product-b/">Product B</a></div>`

Both class names are configurable under Settings > WebberZone Link Warnings > Advanced.

= How do I add rel="nofollow" or rel="sponsored" automatically? =

Go to Settings > WebberZone Link Warnings > Advanced and use the __Link Attributes__ section. Tick any combination of `rel="nofollow"`, `rel="sponsored"`, `rel="ugc"`, "Open in a new tab", `rel="noopener"` and `rel="noreferrer"` under __External Links__, __Affiliate Links__, or both.

Attributes already on your links are kept. A link that carries `rel="me author"` becomes `rel="me author nofollow"` rather than losing its original values, and a link that already has `rel="NoFollow"` is not given a second copy — matching ignores case.

`noopener` and `noreferrer` are only added to links that actually open in a new tab, either because the link already has `target="_blank"` or because you enabled the new-tab option. Every option is off by default and nothing is added unless you tick it — the plugin has never rewritten `rel` on your links before this version.

They are deliberately separate options. Current browsers already imply `noopener` for `target="_blank"`, so ticking it mainly satisfies security scanners and older browsers and costs you nothing. `noreferrer` is the one with a behavioural cost: it also stops the referrer being sent, so leave it unticked for affiliate links if your merchant relies on the referrer for attribution.

= How do I mark affiliate links? =

Add the class `wzlw-affiliate` directly to the `<a>` tag:

`<a href="https://merchant.example.com/product" class="wzlw-affiliate">Buy now</a>`

To mark every link inside a container, add `wzlw-affiliate-wrapper` to the wrapper element instead:

`<div class="wzlw-affiliate-wrapper"><a href="https://merchant.example.com/a">Product A</a><a href="https://merchant.example.com/b">Product B</a></div>`

Affiliate links receive both the __External Links__ and __Affiliate Links__ attribute sets. Both class names are configurable and accept comma-separated values under Settings > WebberZone Link Warnings > Advanced.

Note that marking a link as an affiliate link also treats it as external for warning purposes, exactly like the force-external class. An internal cloaked URL such as `/go/product/` will show your configured modal, redirect screen or indicator, and a domain on your exclusion list will show a warning if you also give the link an affiliate class.

= How can I prevent icons from appearing on specific links? =

Add the class `wzlw-no-icon` to any link where you don't want an icon to appear. For example:

`<a href="https://example.com" class="wzlw-no-icon">Link without icon</a>`

This suppresses the visual indicator (icon and/or text). The modal or redirect warning still applies if your warning method includes one. Screen reader text and ARIA attributes are still added to links that open in a new tab.

To exclude all links inside a wrapper element (e.g. a navigation block or card), add the class `wzlw-no-icon-wrapper` to the containing element:

`<div class="wzlw-no-icon-wrapper"><a href="https://example.com">Link without icon</a></div>`

All links inside that wrapper will have their visual indicators suppressed.

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

= Can visitors stop the modal from showing every time? =

Yes. Go to Settings > WebberZone Link Warnings > Display and set "Modal Frequency" to either "Once per browser session" or "Once every N days". The modal then shows a "Don't show again" checkbox. When a visitor ticks it and clicks Continue, the dismissal is remembered and the link behaves like a normal link on later clicks.

"Dismissal Scope" controls how far that goes. "Per destination domain" suppresses the modal for the domain the visitor dismissed and leaves every other external link alone. "All external links" suppresses it everywhere on the site.

Dismissals live in the visitor's browser, in `sessionStorage` for the session option and `localStorage` for the N days option. No cookies are set and nothing is stored against a user account, so the setting is per browser rather than per person. Clearing site data resets it.

The default is "Always show the modal", which is the behaviour from earlier versions.

= How do I give some links a different warning? =

Go to Settings > WebberZone Link Warnings > Rules and add a rule. Each rule has a URL pattern, an action and an optional message. Rules are checked from top to bottom and the first match wins. Links that match no rule use your normal settings.

`*` matches any characters. `example.com` matches every link to that domain, `*.example.com` matches its subdomains, `amazon.com/dp/*` matches product pages, and `/go/*` matches cloaked links on your own site. The action can be any warning method, or Exclude to give the link no warning.

= How do I stop download warnings on files hosted on my own site? =

Go to Settings > WebberZone Link Warnings > Advanced > Download Links and set __Download Warning Scope__ to "Download links to external sites only". Links to files on other sites keep their download warning.

= Can I hide the warnings while I am logged in? =

Yes. Go to Settings > WebberZone Link Warnings > General and use __Disable Warnings For__. Tick "All logged-in users" or individual roles, such as Administrator and Editor. Visitors who are not logged in always see warnings. Link attributes such as `nofollow`, and the hidden screen reader text on links that open in a new tab, are still added for everyone.

= Does this modify my database content? =

No. The plugin only alters rendered output. Your stored content remains unchanged.

= What happens if I deactivate the plugin? =

Your links return to their original state. The plugin doesn't modify your content in the database — it only changes how links are displayed.

= Where do I report security bugs found in this plugin? =

Please report security bugs found in the source code of the WebberZone Link Warnings plugin through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/a72edb1c-fea7-41b5-a0a1-fbc9308e3a80). The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.

== Screenshots ==

1. External link with icon indicator
2. Modal dialog warning before navigation

== Changelog ==

= 1.7.0 =

Release date: 3 November 2026

**Added**

* Rules tab for giving links that match a URL pattern their own warning method and message, or excluding them. `*` is the wildcard, and `/go/*` matches links on your own site.
* Download Warning Scope setting under Advanced > Download Links to skip download warnings for files hosted on your own site.
* Disable Warnings For setting under General to hide warnings from logged-in users or selected roles. Link attributes and screen reader text are still added.
* `wzlw_link_rules` and `wzlw_is_disabled_for_current_user` filters, and the `wzlw_is_disabled_for_current_user()` function.

**Fixed**

* Links that open in the same tab were announced to screen readers as opening in a new window.
* Links written with backslashes, such as `/\evil.example/` or `https:\\evil.example/`, were treated as internal or excluded and showed no warning.
* The Inline Indicator Scope description said modal and redirect warnings applied to external links only, but the second option also applied them to internal links that open in a new tab.

= Earlier versions =

For the changelog of earlier versions, please refer to the [releases page on GitHub](https://github.com/WebberZone/webberzone-link-warnings/releases).

== Upgrade Notice ==

= 1.7.0 =
Adds URL pattern rules, an option to skip download warnings for files on your own site, and an option to hide warnings from logged-in users. Same-tab links are no longer announced as opening a new window. No action needed.
