# Controlling links with CSS classes

WebberZone Link Warnings gives you two sets of CSS classes you can add directly to links (or their containers) in your content. One set suppresses the warning icon or modal on specific links. The other forces links to be treated as external even when they point to your own domain.

You do not need to write any code. You add these classes the same way you would add any CSS class in your page builder, block editor, or theme.

## Where do these classes work?

**These classes only work inside post and page content** — specifically, in the main body text and excerpts of posts and pages. The plugin processes those areas automatically.

They do **not** work in:

- Navigation menus
- Widget areas (sidebars, footers)
- Page headers or footers
- Archive pages (blog index, category pages, tag pages)
- Any area outside the main post or page content

If you need the plugin's warnings to apply outside of post content, that requires a code customisation. See the [Developer Reference](developer-reference.md).

## Exclusion classes — turn off warnings on specific links

Use these when the plugin is adding a warning icon or modal to a link that you do not want it to touch. Common examples: a "back to top" link, a logo link, or a navigation link placed inside content.

### Turn off warnings on a single link

Add the class `wzlw-no-icon` directly to the `<a>` tag.

```html
<a href="https://example.com" class="wzlw-no-icon">Example</a>
```

The icon and modal are suppressed. If the link opens in a new tab, a hidden screen-reader label is still added so screen reader users know the tab will open.

### Turn off warnings on a group of links

Add the class `wzlw-no-icon-wrapper` to any element that wraps the links. Every link inside that element will have its icon and modal suppressed.

```html
<div class="wzlw-no-icon-wrapper">
  <a href="https://partner-a.com">Partner A</a>
  <a href="https://partner-b.com">Partner B</a>
</div>
```

This is useful for a sponsor block, an icon grid, or any section where you want no warnings at all.

### How to add these classes in WordPress

**Block editor (Gutenberg):**

1. Select the link or the block that wraps your links.
2. Open the block settings panel on the right.
3. Under **Advanced**, find the **Additional CSS class(es)** field.
4. Type `wzlw-no-icon` (for a single link block) or `wzlw-no-icon-wrapper` (for a group).

**Classic editor:**

Switch to the **Text** tab and add `class="wzlw-no-icon"` to the `<a>` tag by hand.

**Page builders (Elementor, Divi, etc.):**

Look for a CSS Class or Custom Class field on the element or section. Enter `wzlw-no-icon-wrapper` on the section/column to suppress all links inside it.

## Force-external classes — make internal links behave like external ones

Use these when a link points to your own domain but should still show a warning. This is common for affiliate redirect links, gateway pages, or any URL on your domain that actually takes the visitor away from your site.

### Force a single link to be treated as external

Add the class `wzlw-force-external` directly to the `<a>` tag.

```html
<a href="/go/affiliate-product/" class="wzlw-force-external">Buy now</a>
```

The link will be processed exactly like an external link — it gets the warning icon, modal, or redirect, depending on your settings.

### Force all links in a section to be treated as external

Add the class `wzlw-force-external-wrapper` to any element that wraps the links.

```html
<div class="wzlw-force-external-wrapper">
  <a href="/go/product-a/">Product A</a>
  <a href="/go/product-b/">Product B</a>
</div>
```

Every link inside the wrapper is treated as external, regardless of its URL.

### How to add these classes in WordPress

The steps are identical to the exclusion classes above. Add `wzlw-force-external` to a single link element, or `wzlw-force-external-wrapper` to the section, column, or block that contains the links you want to force.

## Combining both sets of classes

You can use exclusion and force-external classes together. For example, you could force all links in an affiliate section to be treated as external, then exclude one specific link inside that section:

```html
<div class="wzlw-force-external-wrapper">
  <a href="/go/product-a/">Product A</a>
  <a href="/go/product-b/" class="wzlw-no-icon">Product B (no warning)</a>
</div>
```

Product A gets the warning. Product B is excluded from the warning even though it is inside the force-external wrapper.

## Changing the default class names

The default class names (`wzlw-no-icon`, `wzlw-no-icon-wrapper`, `wzlw-force-external`, `wzlw-force-external-wrapper`) can be changed under **Settings → Link Warnings → Advanced**. If you have changed them, use your custom class names wherever this guide shows the defaults.
