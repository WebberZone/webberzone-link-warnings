# Better External Links

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/better-external-links.svg?style=flat-square)](https://wordpress.org/plugins/better-external-links/)
[![License](https://img.shields.io/badge/license-GPL_v2%2B-orange.svg?style=flat-square)](https://opensource.org/licenses/GPL-2.0)
[![WordPress Tested](https://img.shields.io/wordpress/v/better-external-links.svg?style=flat-square)](https://wordpress.org/plugins/better-external-links/)
[![Required PHP](https://img.shields.io/wordpress/plugin/required-php/better-external-links?style=flat-square)](https://wordpress.org/plugins/better-external-links/)
[![Active installs](https://img.shields.io/wordpress/plugin/installs/better-external-links?style=flat-square)](https://wordpress.org/plugins/better-external-links/)

__Requires:__ 6.6

__Tested up to:__ 6.9

__Requires PHP:__ 7.4

__License:__ [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.html)

__Plugin page:__ [Better External Links](https://webberzone.com/plugins/better-external-links/) | [WordPress.org listing](https://wordpress.org/plugins/better-external-links/)

Add accessible warnings for external links and `target="_blank"` links in WordPress — using icons, modals, or redirect screens.

## Description

Better External Links helps you warn users when links open in a new window or take them to external websites. It adds accessible indicators, confirmation dialogs, or redirect screens — helping you align with accessibility best practices without rewriting your content.

Better External Links uses WordPress's native `WP_HTML_Tag_Processor` class to parse and modify external and `target="_blank"` links in your content efficiently. It adds appropriate ARIA attributes, visual indicators, and optional JavaScript-based warnings based on your configuration. The plugin processes content during the `the_content` and `the_excerpt` filters, making it compatible with most themes and page builders. Your stored content remains untouched — the plugin only alters rendered output and does not interfere with REST API responses or admin editing screens.

### Why warn users about external links?

- `target="_blank"` can disorient screen reader users
- Sudden context changes impact usability
- Accessibility audits often recommend user warnings
- Agencies and site owners often need documented user warnings during accessibility reviews

## Key features

- __Multiple Warning Methods__: Choose from inline indicators, modal dialogs, or redirect screens — or combine them
- __Flexible Scope__: Target external links only, or external links plus all `target="_blank"` links
- __Customizable Indicators__: Configure visual icons, text, or screen reader-only warnings
- __Modal Dialog__: Show a confirmation dialog before users navigate to external sites with keyboard navigation and focus management
- __Redirect Screen__: Display an intermediate page with a configurable countdown before external navigation
- __Domain Exclusions__: Allow trusted domains to treat them as internal links
- __Post Type Control__: Enable warnings on specific post types only
- __Built to support accessibility best practices for external link behaviour in WordPress__: Adds screen reader text, ARIA attributes, and keyboard-friendly modal confirmations
- __Setup Wizard__: Get started quickly with a guided setup wizard on first activation
- __Template Override__: Override the redirect screen template in your theme for full design control
- __RTL Support__: Full right-to-left language support for all frontend and admin styles
- __Multisite Compatible__: Network activate and configure per-site settings
- __Privacy Focused__: Does not collect personal data or send link data to third-party services
- __Performance Optimized__: Uses WordPress's native `WP_HTML_Tag_Processor` class to process links at display time
- __Developer-Friendly__: Filters and actions allow developers to customize behavior, exclude domains, and output

> This plugin assists with user awareness of external navigation. It does not automatically make your website fully accessible or legally compliant.

## Requirements

- WordPress 6.0+
- PHP 7.4+

## Installation

### WordPress install (the easy way)

1. Navigate to __Plugins > Add New__ in your WordPress dashboard
2. Search for "Better External Links"
3. Click __Install Now__ and then __Activate__

### Manual install

1. Download the latest release from the [releases page](https://github.com/WebberZone/better-external-links/releases)
2. Upload the `better-external-links` folder to `/wp-content/plugins/`
3. Activate the plugin through the __Plugins__ menu in WordPress
4. Go to __Settings > Better External Links__ to configure

## Configuration

After activation, the setup wizard guides you through the initial configuration. You can also configure the plugin at __Settings > Better External Links__:

1. __Choose Warning Method__
   - Inline indicators (visual and screen reader text)
   - Modal dialog (JavaScript confirmation)
   - Redirect screen (intermediate page)
   - Inline indicators + modal dialog
   - Inline indicators + redirect screen

2. __Set Link Scope__
   - External links only
   - External links and all `target="_blank"` links

3. __Customize Indicators__
   - Visual indicator type (icon, text, both, none)
   - Custom text for indicators
   - Screen reader text

4. __Configure Modal/Redirect__ (if applicable)
   - Modal title and message
   - Button text
   - Redirect page content and countdown duration

5. __Advanced Settings__
   - Excluded domains
   - Enabled post types

## Screenshots

![Better External Links Settings](https://raw.githubusercontent.com/WebberZone/better-external-links/master/wporg-assets/screenshot-1.png)
*Better External Links Settings Page*

For more screenshots, visit the [WordPress plugin page](https://wordpress.org/plugins/better-external-links/screenshots/).

## Other plugins by WebberZone

Better External Links is one of the many plugins developed by WebberZone. Check out our other plugins:

- [Contextual Related Posts](https://wordpress.org/plugins/contextual-related-posts/) - Display related posts on your WordPress blog and feed
- [Top 10](https://wordpress.org/plugins/top-10/) - Track daily and total visits to your blog posts and display the popular and trending posts
- [WebberZone Snippetz](https://wordpress.org/plugins/add-to-all/) - The ultimate snippet manager for WordPress to create and manage custom HTML, CSS or JS code snippets
- [Knowledge Base](https://wordpress.org/plugins/knowledgebase/) - Create a knowledge base or FAQ section on your WordPress site
- [Better Search](https://wordpress.org/plugins/better-search/) - Enhance the default WordPress search with contextual results sorted by relevance
- [Auto-Close](https://wordpress.org/plugins/autoclose/) - Automatically close comments, pingbacks and trackbacks and manage revisions
- [Popular Authors](https://wordpress.org/plugins/popular-authors/) - Display popular authors in your WordPress widget
- [Followed Posts](https://wordpress.org/plugins/where-did-they-go-from-here/) - Show a list of related posts based on what your users have read

## Contribute

Better External Links is also available on [GitHub](https://github.com/WebberZone/better-external-links).
So, if you've got a cool feature you'd like to implement in the plugin or a bug you've fixed, consider forking the project and sending me a pull request.

Bug reports are [welcomed on GitHub](https://github.com/WebberZone/better-external-links/issues). Please note that GitHub is *not* a support forum, and issues that aren't suitably qualified as bugs will be closed.

## Support

- __Documentation__: [webberzone.com/support/better-external-links/](https://webberzone.com/support/better-external-links/)
- __WordPress Support Forum__: [wordpress.org/support/plugin/better-external-links/](https://wordpress.org/support/plugin/better-external-links/)
- __GitHub Issues__: [github.com/WebberZone/better-external-links/issues](https://github.com/WebberZone/better-external-links/issues)

## How can I report security bugs?

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/wordpress/plugin/better-external-links/vdp)

## Translations

Better External Links is available for [translation directly on WordPress.org](https://translate.wordpress.org/projects/wp-plugins/better-external-links). Check out the official [Translator Handbook](https://make.wordpress.org/polyglots/handbook/plugin-theme-authors-guide/) to contribute.

## License

This plugin is licensed under the GPL-2.0+ license.

## Credits

Better External Links is developed and maintained by [WebberZone](https://webberzone.com/).

## About this repository

This GitHub repository always holds the latest development version of the plugin. If you're looking for an official WordPress release, you can find this on the [WordPress.org repository](https://wordpress.org/plugins/better-external-links/). In addition to stable releases, latest beta versions are made available under [releases](https://github.com/WebberZone/better-external-links/releases).
