# Better External Links

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/better-external-links.svg)](https://wordpress.org/plugins/better-external-links/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/better-external-links.svg)](https://wordpress.org/plugins/better-external-links/)
[![WordPress Plugin Rating](https://img.shields.io/wordpress/plugin/r/better-external-links.svg)](https://wordpress.org/plugins/better-external-links/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Enhance accessibility by warning users when links open in new windows or navigate to external sites.

## Description

Better External Links helps improve your website's accessibility by providing visual and screen reader indicators for external links and links that open in new windows. This plugin supports WCAG 2.1 compliance by ensuring users are properly informed when they're about to leave your site or when a link will open in a new window.

## Features

- **Multiple Warning Methods**: Choose from inline indicators, modal dialogs, or redirect screens
- **Flexible Scope**: Target external links only, all target="_blank" links, or both
- **Customizable Indicators**: Configure visual icons, text, or screen reader-only warnings
- **Modal Dialog**: Show a confirmation dialog before users navigate to external sites
- **Redirect Screen**: Display an intermediate page with a countdown before external navigation
- **Domain Exclusions**: Whitelist trusted domains to treat them as internal links
- **Post Type Control**: Enable warnings on specific post types only
- **WCAG Compliant**: Follows Web Content Accessibility Guidelines for proper link handling
- **Fully Customizable**: Modify all text, messages, and visual elements
- **Privacy Focused**: No data collection, no external services
- **Performance Optimized**: Uses WordPress's native `WP_HTML_Tag_Processor` class

## Installation

### From WordPress.org

1. Navigate to **Plugins > Add New** in your WordPress dashboard
2. Search for "Better External Links"
3. Click **Install Now** and then **Activate**
4. Go to **Settings > Link Warnings** to configure

### Manual Installation

1. Download the latest release from the [releases page](https://github.com/WebberZone/better-external-links/releases)
2. Upload the `better-external-links` folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress
4. Go to **Settings > Link Warnings** to configure

### Via Composer

```bash
composer require webberzone/better-external-links
```

## Configuration

After activation, configure the plugin at **Settings > Link Warnings**:

1. **Choose Warning Method**
   - Inline indicators (visual and screen reader text)
   - Modal dialog (JavaScript confirmation)
   - Redirect screen (intermediate page)
   - Combined approach

2. **Set Link Scope**
   - External links only
   - All `target="_blank"` links
   - Both

3. **Customize Indicators**
   - Visual indicator type (icon, text, both, none)
   - Custom text for indicators
   - Screen reader text

4. **Configure Modal/Redirect** (if applicable)
   - Modal title and message
   - Button text
   - Redirect page content

5. **Advanced Settings**
   - Excluded domains
   - Enabled post types

## How It Works

Better External Links processes content during WordPress's `the_content` and `the_excerpt` filters using the native `WP_HTML_Tag_Processor` class for efficient HTML parsing.

### Processing Flow

1. Content is filtered when displayed
2. Links are parsed using `WP_HTML_Tag_Processor`
3. External links and/or `target="_blank"` links are identified
4. Appropriate attributes and indicators are added based on settings
5. Modified content is returned

### Technical Details

- **Namespace**: `WebberZone\Better_External_Links`
- **Constants Prefix**: `WZ_BEL_*`
- **CSS/JS Prefix**: `wz-bel-*`
- **Option Name**: `wz_bel_settings`
- **Minimum PHP**: 7.4
- **Minimum WordPress**: 6.0

## Development

### File Structure

```
better-external-links/
├── better-external-links.php    # Main plugin file (bootstrap)
├── includes/
│   ├── class-main.php           # Main loader class
│   ├── class-settings.php       # Settings management
│   ├── class-content-processor.php  # Content parsing and modification
│   ├── class-frontend-handler.php   # Modal functionality
│   └── class-redirect-handler.php   # Redirect screen handling
├── assets/
│   ├── css/
│   │   ├── admin.css            # Admin styles
│   │   ├── frontend.css         # Frontend styles (indicators + modal)
│   │   └── redirect.css         # Redirect page styles
│   └── js/
│       ├── modal.js             # Modal dialog functionality
│       └── redirect.js          # Redirect countdown timer
├── languages/                    # Translation files
├── README.md                     # This file
└── readme.txt                    # WordPress.org readme
```

### Building from Source

```bash
# Clone the repository
git clone https://github.com/WebberZone/better-external-links.git
cd better-external-links

# No build process required - plugin is ready to use
```

### Coding Standards

This plugin follows [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/):

```bash
# Check PHP code standards
composer run-script phpcs

# Fix PHP code standards
composer run-script phpcbf

# Run PHPStan analysis
composer run-script phpstan
```

## Hooks and Filters

### Actions

```php
// Before plugin initialization
do_action( 'wz_bel_before_init' );

// After plugin initialization
do_action( 'wz_bel_after_init' );
```

### Filters

```php
// Modify settings before processing
apply_filters( 'wz_bel_settings', $settings );

// Modify processed content
apply_filters( 'wz_bel_processed_content', $content, $original_content );

// Modify indicator HTML
apply_filters( 'wz_bel_indicator_html', $html, $link_url, $is_external );

// Modify excluded domains
apply_filters( 'wz_bel_excluded_domains', $domains );
```

## Customization

### Custom CSS

Add custom styles using the `.wz-bel-*` classes:

```css
/* Customize icon color */
.wz-bel-icon {
    color: #ff0000;
}

/* Style modal */
.wz-bel-modal-container {
    border-radius: 12px;
}

/* Custom redirect page */
.wz-bel-redirect-content {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

### JavaScript Events

```javascript
// Listen for modal events
document.addEventListener('wz_bel_modal_opened', function(e) {
    console.log('Modal opened for:', e.detail.url);
});

document.addEventListener('wz_bel_modal_closed', function(e) {
    console.log('Modal closed');
});
```

## Compatibility

- ✅ WordPress 6.0+
- ✅ PHP 7.4+
- ✅ Classic Editor
- ✅ Block Editor (Gutenberg)
- ✅ Popular page builders (Elementor, Beaver Builder, Divi)
- ✅ Multisite
- ✅ Translation plugins (WPML, Polylang)
- ✅ WooCommerce

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please ensure your code:

- Follows WordPress Coding Standards
- Includes PHPDoc blocks for all functions
- Passes PHPStan level 8 analysis
- Is properly sanitized and escaped

## Testing

```bash
# Run PHP unit tests
composer test

# Run integration tests
composer test:integration

# Run all tests with coverage
composer test:coverage
```

## Support

- **Documentation**: [webberzone.com/support/better-external-links/](https://webberzone.com/support/better-external-links/)
- **WordPress Support Forum**: [wordpress.org/support/plugin/better-external-links/](https://wordpress.org/support/plugin/better-external-links/)
- **GitHub Issues**: [github.com/WebberZone/better-external-links/issues](https://github.com/WebberZone/better-external-links/issues)

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for detailed version history.

## License

This plugin is licensed under the GPL-2.0+ license. See [LICENSE](LICENSE) for details.

## Credits

**Developed by**: [WebberZone](https://webberzone.com/)  
**Author**: Ajay D'Souza

### Contributors

- [Ajay D'Souza](https://github.com/ajaydsouza) - Lead Developer

## Acknowledgments

- WordPress core team for `WP_HTML_Tag_Processor`
- WCAG guidelines contributors
- All plugin contributors and translators

---

**Star this repository** if you find it useful! ⭐
