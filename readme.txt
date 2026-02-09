=== Better External Links ===
Contributors: webberzone, ajay
Tags: accessibility, external links, wcag, new window, a11y
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enhance accessibility by warning users when links open in new windows or navigate to external sites.

== Description ==

Better External Links helps improve your website's accessibility by providing visual and screen reader indicators for external links and links that open in new windows. This plugin supports WCAG 2.1 compliance by ensuring users are properly informed when they're about to leave your site or when a link will open in a new window.

= Key Features =

* **Multiple Warning Methods**: Choose from inline indicators, modal dialogs, or redirect screens
* **Flexible Scope**: Target external links only, all target="_blank" links, or both
* **Customizable Indicators**: Configure visual icons, text, or screen reader-only warnings
* **Modal Dialog**: Show a confirmation dialog before users navigate to external sites
* **Redirect Screen**: Display an intermediate page with a countdown before external navigation
* **Domain Exclusions**: Whitelist trusted domains to treat them as internal links
* **Post Type Control**: Enable warnings on specific post types only
* **WCAG Compliant**: Follows Web Content Accessibility Guidelines for proper link handling
* **Fully Customizable**: Modify all text, messages, and visual elements to match your brand

= Use Cases =

* **Accessibility Compliance**: Meet WCAG 2.1 guidelines for warning users about new windows
* **User Experience**: Inform visitors when they're leaving your site
* **Educational Sites**: Help users understand when content is external
* **Corporate Websites**: Warn users before navigating to third-party resources
* **Membership Sites**: Alert members when leaving your protected content area

= How It Works =

Better External Links uses WordPress's native `WP_HTML_Tag_Processor` class to efficiently parse and modify links in your content. It adds appropriate ARIA attributes, visual indicators, and optional JavaScript-based warnings based on your configuration.

The plugin processes content during the `the_content` and `the_excerpt` filters, ensuring compatibility with most themes and page builders.

= Configuration Options =

**Warning Methods:**
* Inline indicators only (visual and/or screen reader text)
* Modal dialog (JavaScript-based confirmation)
* Redirect screen (intermediate page with countdown)
* Combined approach (inline + modal for external links)

**Link Scope:**
* External links only
* All target="_blank" links
* Both external and target="_blank" links

**Visual Indicators:**
* Icon only (↗)
* Text only (customizable)
* Icon + text
* None (screen reader only)

**Advanced Settings:**
* Custom modal messages and button text
* Custom redirect page content
* Domain exclusion list
* Post type selection

= Privacy & Data =

Better External Links does not collect, store, or transmit any user data. All processing happens on your server, and no external services are contacted. The plugin is fully GDPR compliant.

= Compatibility =

* Works with Classic Editor and Block Editor (Gutenberg)
* Compatible with popular page builders
* Multisite compatible
* Translation ready

== Installation ==

= From WordPress Dashboard =

1. Navigate to Plugins > Add New
2. Search for "Better External Links"
3. Click "Install Now" and then "Activate"
4. Go to Settings > Link Warnings to configure

= Manual Installation =

1. Upload the `better-external-links` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Link Warnings to configure

= After Activation =

1. Visit Settings > Link Warnings
2. Choose your preferred warning method
3. Configure visual indicators and messages
4. Select which post types should show warnings
5. Save your settings

== Frequently Asked Questions ==

= Does this plugin affect SEO? =

No. Better External Links only modifies how links are displayed to users. It doesn't change the href attribute or add nofollow tags. Search engines see your links exactly as you created them.

= Will this slow down my site? =

No. The plugin uses WordPress's efficient `WP_HTML_Tag_Processor` class and only processes content when it's displayed. The performance impact is negligible.

= Can I exclude specific domains? =

Yes! In the Advanced Settings, you can add a list of domains that should be treated as internal links. This is useful for sister sites or trusted partners.

= Does it work with page builders? =

Yes! Better External Links works with Elementor, Beaver Builder, Divi, and other popular page builders. It processes the final rendered content regardless of how it was created.

= Can I customize the appearance? =

Yes! All visual elements use CSS classes prefixed with `wz-bel-`, making it easy to add custom styles. You can also customize all text strings through the settings page.

= Is it accessible? =

Absolutely! The plugin was built with accessibility as the primary goal. It includes proper ARIA attributes, keyboard navigation support, focus management, and screen reader text.

= Does it work with multilingual sites? =

Yes! Better External Links is translation-ready and compatible with WPML, Polylang, and other translation plugins. All strings can be translated through standard WordPress methods.

= What happens if I deactivate the plugin? =

Your links return to their original state. The plugin doesn't modify your content in the database—it only changes how links are displayed.

= Can I use this on WooCommerce product pages? =

Yes! Simply enable the "product" post type in the plugin settings, and warnings will appear on product descriptions and content.

== Screenshots ==

1. Plugin settings page - General options
2. Inline indicator with icon
3. Modal dialog warning
4. Redirect screen with countdown
5. Settings for modal customization
6. Advanced settings and domain exclusions

== Changelog ==

= 1.0.0 - 2025-01-01 =
* Initial release
* Multiple warning methods (inline, modal, redirect)
* Customizable visual and screen reader indicators
* Domain exclusion support
* Post type selection
* Full WCAG 2.1 compliance
* Translation ready

== Upgrade Notice ==

= 1.0.0 =
Initial release of Better External Links.

== Support ==

For support, feature requests, or bug reports, please visit:

* Plugin Support: https://wordpress.org/support/plugin/better-external-links/
* Documentation: https://webberzone.com/support/better-external-links/
* GitHub: https://github.com/WebberZone/better-external-links

== Credits ==

Better External Links is developed and maintained by [WebberZone](https://webberzone.com/).

== Translations ==

Better External Links is translation-ready. If you'd like to contribute a translation, please visit our [GitHub repository](https://github.com/WebberZone/better-external-links).