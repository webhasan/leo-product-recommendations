# Free vs Pro

## Feature split

| Area | Free | Pro |
|---|---|---|
| Popup after add to cart (archive, single, blocks, widgets, quick view) | Yes | Yes |
| AJAX add to cart inside popup, incl. variable and grouped | Yes | Yes |
| Default heading, `%title%`, `[item, items]` | Yes | Yes |
| Popup Show limit | Yes | Yes |
| Product-level Manual selection | Yes | Yes |
| Product-level **Dynamic** selection | Locked | Yes |
| Global settings, incl. dynamic rules and Skip Manual Selection | Yes | Yes |
| Grid layout and items per row | Yes | Yes |
| Slider layout and slider options | Locked | Yes |
| Popup size | Locked | Yes |
| Button visibility (Continue Shopping, Checkout, Close icon) | Locked | Yes |
| Popup colors and product colors | Locked | Yes |
| Custom CSS | Yes | Yes |
| REST API (`lpr/v1`) | No | Yes |
| License page, automatic updates from leocoder.com | No | Yes |

**Owner intent:** Global dynamic rules are free on purpose. Product-level dynamic
selection is the Pro feature.

## How locking works in Free

- Settings page: Pro fields are `pro_image` type fields. Each shows a static teaser image
  with a red "PRO FEATURE" badge and a "Get Pro Version »" link. They are images, not
  real fields, so they can drift from the real Pro fields (see `known-issues.md`).
- Product panel: the Dynamic Selection option shows a locked demo with sample data
  (Clothing, Hoodies, T-Shirt; Green, Orange, Red; Best Selling; 10).
- Front end: when Pro is inactive the layout is forced to grid
  (`wp_enqueue_scripts`), Pro colors, size and visibility are not applied, and Free prints
  its own grid CSS and custom CSS (`settings_css`).
- Values saved while Pro was active stay in `lc_lpr_settings` and in product meta. Free
  ignores Pro display settings. Note: product-level dynamic data saved under Pro is still
  honored by Free's query code (see `known-issues.md`).

## How Pro plugs in

- Pro needs Free active, minimum Free version in `$require_free_version` (3.2.0).
- Free fires `do_action('lpr_before_action')` in `on_plugins_loaded`. Pro attaches its
  `includes()` and `hooks()` there, so Pro loads only when Free has passed its own checks.
- Settings page: one framework in Free. Pro 3.2.0+ adds its pages through the
  `lpr_settings_pages` filter; Pro 3.1.x and older still run their own copy (Free steps
  aside). See `codebase-internals.md` for the version table.
- Free detects Pro with `is_pro_activated()` (`class_exists(Product_Recommendations_Pro)`)
  and then: skips its own panel
  script (Pro enqueues its panel), skips its own front-end CSS (Pro prints CSS), allows
  slider layout.
- Templates: Free's `get_templates_path()` looks in the theme, then Pro, then Free.
- Both plugins use the same text domain `leo-product-recommendations` and the same
  settings option `lc_lpr_settings`.
