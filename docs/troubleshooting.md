# Troubleshooting

This guide covers common issues, diagnostics, and frequently asked questions for Better External Links.

## Links are not being processed

**Check the post type.** The plugin only processes content on singular views of enabled post types. Archive pages, search results, and custom loops are not affected. Verify that the current post type is listed under **Settings > Better External Links > General > Enabled Post Types**.

**Check the link scope.** If the scope is set to "External links only", internal links with `target="_blank"` are not processed. Switch to "External links and all target='_blank' links" if you need those covered.

**Check excluded domains.** If a domain appears in the excluded domains list (under the Advanced tab), links to that domain are treated as internal and skipped.

**Check for filter conflicts.** Another plugin or theme may be removing or overriding the `the_content` filter at a priority that interferes. Better External Links runs at priority 999. If another filter runs later and strips data attributes or classes, the plugin's output may be lost.

## The modal does not appear

**Verify the warning method.** The modal only renders when the warning method is set to "Modal dialog" or "Inline indicators + Modal dialog". Check **Settings > Better External Links > General > Warning Method**.

**Check for JavaScript errors.** Open the browser console (F12 > Console) and look for errors. A JavaScript error from another plugin or theme can prevent the modal script from initialising.

**Check that the script is enqueued.** View the page source and search for `wz-bel-modal`. If the script tag is missing, another plugin may be dequeuing it, or the warning method may not be set correctly.

**Check the modal HTML.** The modal markup is rendered in `wp_footer`. If your theme does not call `wp_footer()`, the modal HTML will not be present in the page.

## The redirect screen shows a blank page or redirects to the home page

**Flush rewrite rules.** Navigate to **Settings > Permalinks** and click **Save Changes** without making any changes. This flushes the rewrite rules and re-registers the `external-redirect/` endpoint.

**Check the warning method.** The redirect screen only activates when the warning method includes a redirect component ("Redirect screen" or "Inline indicators + Redirect screen").

**Check the URL parameter.** The redirect page expects a `url` query parameter containing a valid external URL. If the URL is malformed, empty, or points to the same site, the plugin redirects to the home page as a security measure.

## The countdown does not update

The countdown JavaScript targets the `.wz-bel-countdown-number` class. If you are using a custom redirect template and have removed or renamed this class, the visual countdown will not update. The automatic redirect still fires after the configured duration.

If the countdown is set to `0` in settings, no countdown element is rendered and no timer runs.

## Visual indicators are not showing

**Check the warning method.** Inline indicators only appear when the warning method includes an inline component: "Inline indicators only", "Inline indicators + Modal dialog", or "Inline indicators + Redirect screen".

**Check the visual indicator setting.** If set to "None (screen reader only)", no visible indicator is rendered. Only the hidden screen reader text span is added.

**Check for CSS conflicts.** Another stylesheet may be hiding the indicator elements. Inspect the link in your browser's developer tools and check whether `.wz-bel-icon` or `.wz-bel-text` elements are present but hidden by CSS rules.

## Styles look wrong or are overridden by the theme

The plugin uses CSS custom properties for all visual tokens. Theme stylesheets that set aggressive global styles on buttons, links, or modal elements can override the plugin's appearance.

The plugin includes scoped selectors with minimal `!important` declarations for modal and redirect button states to maintain accessible contrast. If your theme still overrides these, add your own overrides with higher specificity. See the [Styling guide](styling.md) for the full class and custom property reference.

## The plugin does not work with my page builder

Better External Links processes content through the standard `the_content` and `the_excerpt` WordPress filters. Most page builders pass their output through these filters, so the plugin should work without additional configuration.

If your page builder renders content outside these filters (e.g. via custom shortcodes or AJAX calls that bypass the main query), the plugin will not process that content. There is no workaround for this within the plugin itself — the page builder would need to apply `the_content` filter to its output.

## External links inside widgets or custom templates are not processed

The plugin only processes content passed through `the_content` and `the_excerpt` filters on singular post type views. Widget content, sidebar output, header/footer templates, and custom template parts are not processed.

To process arbitrary HTML, you can apply the content filter manually:

```php
$processed = apply_filters( 'the_content', $my_html );
```

Be aware that this runs all content filters, not just Better External Links.

## The plugin affects REST API responses

The plugin does not process REST API responses. It hooks into `the_content` and `the_excerpt` at priority 999, but the `is_singular()` check in `is_post_type_enabled()` returns `false` in REST API contexts, so no processing occurs.

## Performance concerns

The plugin uses WordPress's native `WP_HTML_Tag_Processor` class for HTML parsing, which is significantly more efficient than regex-based alternatives. Processing happens at display time and does not modify stored content or run database queries beyond reading the settings option (which is autoloaded).

For most sites, the processing overhead is negligible. If you have pages with an unusually large number of links (hundreds), the processing time scales linearly with the number of `<a>` tags.

## Multisite considerations

The plugin can be network-activated. Settings are stored per-site in the `wz_bel_settings` option, so each site in the network can have its own configuration. There is no network-wide settings page.

On network activation, the activator runs on each site in the network to set default settings and flush rewrite rules.

## GDPR and privacy

The plugin does not collect personal data, set cookies, or send information to external services. It processes content entirely on the server side (for HTML modifications) and client side (for modal and redirect behaviour). No user data leaves the site.

## Uninstallation

The `uninstall.php` file runs when the plugin is deleted from the WordPress dashboard. It removes the `wz_bel_settings` option, wizard-related options, and transients from the database. On multisite, it runs cleanup on every site in the network. Deactivation alone does not remove settings — they are preserved in case you reactivate the plugin later.

## Getting help

- **Documentation:** [webberzone.com/support/better-external-links/](https://webberzone.com/support/better-external-links/)
- **WordPress support forum:** [wordpress.org/support/plugin/better-external-links/](https://wordpress.org/support/plugin/better-external-links/)
- **GitHub issues:** [github.com/WebberZone/better-external-links/issues](https://github.com/WebberZone/better-external-links/issues)

GitHub is for bug reports only. For general support questions, use the WordPress.org forum.
