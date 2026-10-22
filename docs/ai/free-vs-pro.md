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
| Custom CSS (field removed in 3.6.0, saved CSS still printed) | Yes | Yes |
| Cart drawer | Locked (example in More Places) | Yes |
| More Places: cart page, product page FBT, checkout offer, thank-you page | Locked (Pro card + live example) | Yes |
| Smart picks (store-wide rule, product panel, fill empty spots) | Locked option (accent border, PRO badge) that scrolls to a showcase card (`smart_teaser`) with pairs found in the store's own last 300 orders (`smart_teaser_data()`, cached a day in the `lpr_smart_teaser` transient; visible, in-stock products only; an example when there are none) | Yes |
| Free shipping bar with products that close the gap | Sixth item in the More Places showcase, drawn with the store's products | Yes |
| Discounts (bundle saving, add-on saving, checkout offer discount) | Discounts tab with a showcase (`discount_showcase` field type, drawn with the store's own products) and locked `pro_preview` cards; no Save button on that tab | Yes |
| Recommendation analytics | Locked (teaser page) | Yes |
| REST API (`lpr/v1`) | No | Yes |
| License page, automatic updates from leocoder.com | No | Yes |

**Owner intent:** Global dynamic rules are free on purpose. Product-level dynamic
selection is the Pro feature. Free is the popup only; every other place (cart drawer,
cart page, product page, checkout, thank-you page) is Pro (owner, October 2026).

## How locking works in Free

- Settings page: locked Pro settings are `pro_preview` fields (Layout Type, Popup Size,
  Buttons, Popup Colors). They draw the same controls Pro uses (chips, switches, swatches),
  disabled and without a `name`, with a small PRO badge and a "Get it with Pro" link. When
  a Pro control changes, update its `controls` list in Free so the two stay alike.
  More Places in Free is a showcase of every Pro place drawn with the store's products.
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
