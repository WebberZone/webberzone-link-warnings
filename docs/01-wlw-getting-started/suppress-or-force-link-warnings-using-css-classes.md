---
slug: suppress-or-force-link-warnings-using-css-classes
title: "Suppress or force link warnings using CSS classes"
products: [link-warnings]
sections: ["01-wlw-getting-started"]
tags: [link-warnings]
status: publish
order: 0
toc: true
---

[toc]

[WebberZone Link Warnings](https://webberzone.com/plugins/webberzone-link-warnings/) gives you two sets of CSS classes you can add directly to links (or their containers) in your content. One set suppresses the warning icon on specific links. The other set treats links as external even when they point to your own domain.

You do not need to write any code. You add these classes the same way you would add any CSS class in your page builder, block editor, or theme.

## Suppress visual indicators on specific links

Use these when you want to hide a link’s visual indicator while keeping any modal or redirect warning. Common examples include a “back to top” link, a logo link, or a navigation link placed within content.

### Suppress the indicator on a single link

Add the class `wzlw-no-icon` directly to the `<a>` tag.

```html
<a href="https://example.com" class="wzlw-no-icon">Example</a>
```

The visual indicator (icon and/or text) is suppressed. The modal or redirect warning still applies if your warning method includes one. If the link opens in a new tab, a hidden screen-reader label is still added so screen reader users know the tab will open.

### Suppress indicators on a group of links

Add the class `wzlw-no-icon-wrapper` to any element that wraps the links. Every link inside that element will have its visual indicator (icon and/or text) suppressed. The modal or redirect warning still applies if your warning method includes one — the same as the single-link class above.

```html
<div class="wzlw-no-icon-wrapper">
  <a href="https://partner-a.com">Partner A</a>
  <a href="https://partner-b.com">Partner B</a>
</div>
```

This is useful for a sponsor block, an icon grid, or any section where you want no visual indicators.

### How to add these classes in WordPress

**Block editor (Gutenberg):**

1. Select the block that contains your links.
2. Open the block settings panel on the right.
3. Under **Advanced**, find the **Additional CSS class(es)** field.
4. Enter `wzlw-no-icon-wrapper` to suppress indicators on links inside the block.

The **Additional CSS class(es)** field normally applies to the block container, not its `<a>` tag. Use the wrapper class there. To apply `wzlw-no-icon` to one anchor, edit that link’s HTML.

**Classic editor:**

Switch to the **Text** tab and add `class="wzlw-no-icon"` to the `<a>` tag by hand.

**Page builders (Elementor, Divi, etc.):**

Look for a CSS Class or Custom Class field on the element or section. Enter `wzlw-no-icon-wrapper` on the section/column to suppress all links inside it.

## Force-external classes: Make internal links behave like external ones

Use these when a link points to your own domain, but should still show a warning. This is common for affiliate redirect links, gateway pages, or any URL on your domain that actually takes the visitor away from your site.

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

Add `wzlw-force-external` to an anchor’s HTML, or add `wzlw-force-external-wrapper` to the section, column, or block containing the links. Use the wrapper class in the block editor’s **Additional CSS class(es)** field.

## Affiliate classes: Mark links for automatic rel/target attributes

Use these when you want the plugin to add `rel="nofollow"`, `rel="sponsored"`, `rel="ugc"`, or new-tab attributes to specific links automatically, without touching every external link on the site. This is intended for affiliate or sponsored links. You still need to tick the attributes you want under **Settings → Link Warnings → Advanced → Link Attributes** — the classes below only mark *which* links count as affiliate links.

### Mark a single link as an affiliate link

Add the class `wzlw-affiliate` directly to the `<a>` tag.

```html
<a href="https://merchant.example.com/product" class="wzlw-affiliate">Buy now</a>
```

### Mark all links in a section as affiliate links

Add the class `wzlw-affiliate-wrapper` to any element that wraps the links.

```html
<div class="wzlw-affiliate-wrapper">
  <a href="https://merchant.example.com/a">Product A</a>
  <a href="https://merchant.example.com/b">Product B</a>
</div>
```

An affiliate-marked link is always treated as external for warning purposes too — even if it points to a path on your own domain, such as a cloaked redirect link like `/go/product/`, and even if the domain is on your exclusion list.

### How to add these classes in WordPress

Add `wzlw-affiliate` to an anchor’s HTML, or add `wzlw-affiliate-wrapper` to the section, column, or block containing the links. Use the wrapper class in the block editor’s **Additional CSS class(es)** field.

## Combining both sets of classes

You can use exclusion and force-external classes together. For example, you could force all links in an affiliate section to be treated as external, then suppress the indicator on one link inside that section:

```html
<div class="wzlw-force-external-wrapper">
  <a href="/go/product-a/">Product A</a>
  <a href="/go/product-b/" class="wzlw-no-icon">Product B (no indicator)</a>
</div>
```

Product A gets the icon and any modal or redirect warning. Product B also gets the modal or redirect warning, but its icon is suppressed by the `wzlw-no-icon` class even though it is inside the force-external wrapper.

## Changing the default class names

The default class names (`wzlw-no-icon`, `wzlw-no-icon-wrapper`, `wzlw-force-external`, `wzlw-force-external-wrapper`, `wzlw-affiliate`, `wzlw-affiliate-wrapper`) can be changed under **Settings → Link Warnings → Advanced**. If you have changed them, use your custom class names wherever this guide shows the defaults.

Each setting also accepts a comma-separated list of class names. This lets you honor multiple class conventions at once — for example, if you have an existing theme class you want to reuse alongside the plugin default:

```text
wzlw-no-icon, my-theme-no-icon
```

Any link carrying **any** of the listed classes will be treated as a match. The same applies to the wrapper and force-external settings.

## Adding class names to navigation menus

For classic menus, open **Appearance → Menus → Screen Options** and enable **CSS Classes**. Menu-item classes normally apply to the `<li>` container, so use `wzlw-no-icon-wrapper`, `wzlw-force-external-wrapper`, or `wzlw-affiliate-wrapper` for its descendant links.
