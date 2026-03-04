# Styling WebberZone Link Warnings

WebberZone Link Warnings uses CSS custom properties (variables) for all colours, spacing, and visual tokens. You can override these in your theme stylesheet without modifying plugin files.

This guide covers the CSS class reference, custom property reference, and common customisation recipes — including replacing the default external link icon.

## CSS classes added to links

The plugin adds the following classes to processed `<a>` tags:

| Class | Applied when |
| --- | --- |
| `wz-bel-processed` | Always added to every link the plugin processes. |
| `wz-bel-external` | Added when the link is classified as external. |

These classes are useful for writing targeted CSS rules.

## Inline indicator markup

When an inline warning method is active (`inline`, `inline_modal`, or `inline_redirect`), the plugin appends one or more `<span>` elements inside the link, just before the closing `</a>` tag.

### Icon indicator

```html
<span class="wz-bel-icon" aria-hidden="true">↗</span>
```

### Text indicator

```html
<span class="wz-bel-text" aria-hidden="true">(opens in new window)</span>
```

### Screen reader text

Always added to processed links, regardless of the visual indicator setting:

```html
<span class="screen-reader-text">Opens in a new window</span>
```

## CSS custom properties

The plugin defines all visual tokens as CSS custom properties on `:root`. Override them in your theme to change colours and spacing globally.

### Frontend (indicators and modal)

Defined in `includes/assets/css/frontend.css`:

```css
:root {
    --wz-bel-color-text: #1a1a1a;
    --wz-bel-color-text-muted: #4a4a4a;
    --wz-bel-color-indicator: #595959;
    --wz-bel-color-surface: #fff;
    --wz-bel-color-on-primary: #fff;
    --wz-bel-color-surface-muted: #f5f5f5;
    --wz-bel-color-border: #c3c4c7;
    --wz-bel-color-border-strong: #8c8f94;
    --wz-bel-color-button-muted: #f0f0f0;
    --wz-bel-color-button-muted-hover: #d5d5d5;
    --wz-bel-color-link: #2271b1;
    --wz-bel-color-link-hover: #135e96;
    --wz-bel-color-focus: #2271b1;
    --wz-bel-modal-overlay-bg: rgba(0, 0, 0, 0.7);
    --wz-bel-shadow-modal: 0 4px 20px rgba(0, 0, 0, 0.3);
    --wz-bel-radius-sm: 4px;
    --wz-bel-radius-md: 8px;
    --wz-bel-transition: all 0.2s ease;
}
```

### Redirect screen

Defined in `includes/assets/css/redirect.css`:

```css
:root {
    --wz-bel-color-text: #1a1a1a;
    --wz-bel-color-text-muted: #4a4a4a;
    --wz-bel-color-text-subtle: #5a6268;
    --wz-bel-color-surface: #fff;
    --wz-bel-color-surface-muted: #f8f9fa;
    --wz-bel-color-link: #2271b1;
    --wz-bel-color-link-hover: #135e96;
    --wz-bel-color-on-primary: #fff;
    --wz-bel-color-back-hover: #f0f6fc;
    --wz-bel-radius-lg: 12px;
    --wz-bel-radius-md: 8px;
    --wz-bel-shadow-card: 0 10px 40px rgba(0, 0, 0, 0.1);
    --wz-bel-shadow-primary-hover: 0 4px 12px rgba(34, 113, 177, 0.3);
    --wz-bel-transition: all 0.2s ease;
    --wz-bel-redirect-bg: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}
```

## Overriding custom properties

Add overrides in your theme's stylesheet (or via the Customiser's Additional CSS). Your rules load after the plugin stylesheet, so they take precedence without `!important`.

```css
:root {
    --wz-bel-color-link: #0073aa;
    --wz-bel-color-link-hover: #005177;
    --wz-bel-color-indicator: #333;
    --wz-bel-radius-md: 12px;
}
```

This changes the modal button colour, indicator text colour, and border radius across all plugin elements.

## Replacing the default icon

The default icon is the ↗ Unicode character rendered inside a `<span class="wz-bel-icon">`. There are several ways to replace it.

### Hide the character and use a background image

```css
.wz-bel-icon {
    font-size: 0;
    width: 16px;
    height: 16px;
    background: url('/path/to/your-icon.svg') no-repeat center / contain;
    vertical-align: middle;
}
```

This hides the Unicode character by setting `font-size: 0` and displays your SVG as a background image instead.

### Hide the character and use a pseudo-element

```css
.wz-bel-icon {
    font-size: 0;
    position: relative;
    display: inline-block;
    width: 1em;
    height: 1em;
}

.wz-bel-icon::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('/path/to/your-icon.svg') no-repeat center / contain;
    font-size: 14px; /* Reset to desired size. */
}
```

### Use a different Unicode character

If you only need a different symbol, override the character with CSS:

```css
.wz-bel-icon {
    font-size: 0;
}

.wz-bel-icon::after {
    content: '\1F517'; /* Link emoji, or any Unicode code point. */
    font-size: 0.875em;
}
```

### Use a web font icon (e.g. Dashicons or Font Awesome)

If your theme already loads an icon font, you can reference a glyph:

```css
.wz-bel-icon {
    font-size: 0;
}

.wz-bel-icon::after {
    font-family: 'dashicons';
    content: '\f504'; /* Dashicons external link glyph. */
    font-size: 14px;
    vertical-align: middle;
}
```

Replace the `font-family` and `content` values with the appropriate values for your icon font.

## Styling the modal dialog

The modal container uses the class `wz-bel-modal-container`. Key child classes:

| Class | Element |
| --- | --- |
| `wz-bel-modal-overlay` | Semi-transparent backdrop. |
| `wz-bel-modal-container` | The dialog box itself. |
| `wz-bel-modal-close-btn` | The close (×) button. |
| `wz-bel-modal-title` | The `<h2>` heading. |
| `wz-bel-modal-message` | The body text. |
| `wz-bel-modal-url` | The destination URL display. |
| `wz-bel-modal-actions` | The button row. |
| `wz-bel-modal-cancel` | The cancel button. |
| `wz-bel-modal-continue` | The continue button. |

Example — wider modal with a darker overlay:

```css
.wz-bel-modal-container {
    width: 640px;
}

:root {
    --wz-bel-modal-overlay-bg: rgba(0, 0, 0, 0.85);
}
```

## Styling the redirect screen

The redirect page uses a centred card layout. Key classes:

| Class | Element |
| --- | --- |
| `wz-bel-redirect-container` | Full-page wrapper with gradient background. |
| `wz-bel-redirect-content` | The card. |
| `wz-bel-redirect-icon` | The SVG icon area. |
| `wz-bel-redirect-title` | The `<h1>` heading. |
| `wz-bel-redirect-message` | The message paragraph. |
| `wz-bel-redirect-url-container` | The URL display block. |
| `wz-bel-redirect-actions` | The button row. |
| `wz-bel-redirect-continue` | The "Continue to site" button. |
| `wz-bel-redirect-back` | The "Go back" button. |
| `wz-bel-redirect-countdown` | The countdown text. |
| `wz-bel-countdown-number` | The countdown number. |

Example — change the redirect page background:

```css
:root {
    --wz-bel-redirect-bg: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    --wz-bel-color-surface: #1e1e2f;
    --wz-bel-color-text: #e0e0e0;
    --wz-bel-color-text-muted: #b0b0b0;
}
```

## Reduced motion support

The plugin respects `prefers-reduced-motion: reduce`. When this media query matches, the modal slide-up animation and redirect fade-in animation are disabled, and button transitions are removed. No additional configuration is needed.

## RTL support

The plugin ships with RTL-specific stylesheets (`frontend-rtl.css`, `redirect-rtl.css`). These are loaded automatically when `is_rtl()` returns `true`. If you are adding custom CSS, test with both LTR and RTL layouts.

## Enqueue order

The plugin stylesheet is enqueued with the handle `wz-bel-frontend`. If you need to ensure your overrides load after the plugin, declare a dependency:

```php
wp_enqueue_style(
    'my-theme-bel-overrides',
    get_stylesheet_directory_uri() . '/css/bel-overrides.css',
    array( 'wz-bel-frontend' ),
    '1.0.0'
);
```

This guarantees your stylesheet loads after the plugin's frontend CSS.
