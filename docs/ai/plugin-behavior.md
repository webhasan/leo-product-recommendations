# Plugin behavior

How the plugin behaves for the store owner and the shopper. Verified against the code
(Free 3.2.0, Pro 3.1.0) and on the local test store, October 2026.
Statements marked **Owner intent** come from the plugin author.

## 1. Choosing which products to recommend

Entry point: `Product_Recommendations::get_recommended_products_id($product_id)`.

Order of decision:

1. **Global forced.** If Global is active (`active_global_settings`) and
   **Skip Manual Selection** (`disable_global_override`) is checked, every product uses the
   Global rule. Product-level picks are ignored. Use case: campaigns such as Black Friday.
2. **Product-level picks.** Otherwise, if the product has its own data in the
   Recommendations tab, it is used:
   - Manual selection: the hand-picked products, in the saved order.
   - Dynamic selection (Pro UI): a query by categories, tags, sort, on-sale and count.
3. **WooCommerce linked products (3.5.0+).** If the product has no manual products and no
   dynamic selection, and `global_linked_upsells` and/or `global_linked_cross_sells` is on,
   its Upsells then Cross-sells are used (published, not hidden, in stock, not itself,
   capped at the Global "Number of Products"). Type `linked` in the dataLayer/analytics.
   Code: `uses_linked_products()`, `get_linked_product_ids()`, filter `lpr_linked_product_ids`.
   Skipped when Skip Manual Selection forces Global. The product's Recommendations tab shows
   a hint when this applies.
4. **Global fallback.** If the product has no manual products, no dynamic selection and no
   usable linked products, and Global is active, the Global rule is used.
5. **Nothing.** If none of the above applies, no recommendations.

**Owner intent (linked products default):** on for new installs (activation writes the
default `1`), off for stores that already had settings (the keys are missing, and a missing
checkbox reads as off; activation code writes `''` for them so reactivating doesn't turn it on).

Product picks and Global are never mixed. A product with manual picks shows only those
picks, even if there are fewer than the Global "Number of Products".
(**Owner intent**, matches code.) Exception, Pro 3.4.0+: "Fill empty spots" (see Smart
recommendations below) tops up short hand-picked or linked lists with smart picks.

Every result passes through the filter `lpr_recommended_products_id ($ids, $product_id, $from)`,
where `$from` is `linked`, `manual`, `dynamic`, `smart`, `global-manual`, `global-rule` or empty.

### Global rule options (free feature)

- Categories: "Product Related Category" (`same_categories`, the added product's own
  categories) or Manual (chosen categories; empty = all categories).
- Tags (empty = all tags). Categories and tags combine with AND.
- Products Filtering (`global_filtering`): `rand`, `newest`, `oldest`, `lowprice`,
  `highprice` (by the shown price from `wc_product_meta_lookup`, sale and variable prices
  included), `popularity` (best selling, `total_sales`), `rating` (`_wc_average_rating`).
- On-Sale Only, Number of Products (default 12).

### Exclusions

- The added product itself is never recommended (dynamic and Global queries).
- Products already in the cart are removed (`lpr_get_popup_data`). **Owner intent:** it
  makes no sense to recommend something already in the cart.
- Hidden, non-published and out-of-stock products are excluded, for manual picks (checked
  per product) and for dynamic and Global queries (`product_visibility` NOT IN, always
  applied, with or without category or tag filters; fixed October 2026).

### Number of Products and the cart exclusion

The count is the query limit (`posts_per_page`). Cart items are removed afterwards, so the
popup can show fewer than the configured number (example: 12 configured, 9 shown).
**Owner decision:** accepted as is, not a bug. Do not change this behavior.
Manual selection has no count limit.

## 2. When the popup appears

- The popup opens on WooCommerce's JS event `added_to_cart`. So it works with any add to
  cart that fires that event: archive AJAX buttons, Gutenberg product blocks, the
  `[products]` shortcode, Elementor/Divi/WPBakery widgets, and quick view plugins.
  (**Owner intent:** it should work for product widgets and quick view.)
- Single product page: `ajax-add-to-cart.js` intercepts `form.cart` submit and adds to
  cart via the `lc_ajax_add_to_cart` AJAX action, then triggers `added_to_cart`. This is
  why the plugin exists in that form: WooCommerce itself reloads the page there.
- Products the AJAX call can't add (bundles, subscriptions, bookings, file uploads) and
  Buy now buttons post normally. After the reload the popup opens once for that product
  (cookie `lpr_added_product`), not on cart or checkout pages (since 3.4.0). The same
  happens for `?add-to-cart=ID` links.
- The popup asks the server for content (`lpr_get_popup_data`). If there are no
  recommendations after exclusions, the server returns nothing and **no popup is shown**
  (**Owner intent**). The product is still added to the cart.
- Adding a product from inside the popup does not open a new popup. It shows a green
  "X has been added to cart." flash bar instead.
- On activation the plugin forces WooCommerce settings "Enable AJAX add to cart buttons on
  archives" = yes and "Redirect to the cart page after successful addition" = no.
  (**Owner decision, Oct 2026:** keep both as they are. Don't add a "popup on the cart
  page" mode for stores that turn the redirect back on; with redirect on, archive adds go
  straight to the cart and the after-reload popup is skipped there.)

### Popup Show limit (`popup_view_times`)

Options: Every Time (`always`), 1, 2, 3, 4, 5 times.

- Stored in the browser's **localStorage** key `lpr-popup-count` as `{count, time}`.
- Counted **store-wide per browser**, not per product and not per user.
- Rolling window of **2 hours**: the count resets 2 hours after the last popup.
- When the limit is reached, the single product form is no longer intercepted, so that
  page falls back to WooCommerce's normal (non-AJAX) submit with a page reload.

## 3. Popup content

- Top bar: thumbnail of the added product, check icon, "X has been added to your cart.",
  "View cart" link, Continue Shopping and Checkout buttons, cart icon with live item count
  (updated through WooCommerce cart fragments).
- Heading:
  - Product level: plain heading, or Heading & Description (rich editor, shortcodes run).
  - Fallback / Global: the Default Heading from General settings.
  - When Global is used, the product's own heading is ignored and the default is used.
  - Variables: `%title%` = added product title. `[single, plural]` picks the word by the
    number of recommendations, e.g. `[item, items]`.
- Product list: Grid (free) or Slider (Pro, Owl Carousel).
- Variable and grouped products: if "Variable / Group Products Add To Cart" is on,
  variation dropdowns (or the grouped product form) appear inside the popup. The Add to
  cart button stays disabled until a variation is chosen. Grouped products follow normal
  WooCommerce grouped behavior (**Owner intent**).
- After adding from the popup: Free shows a "View cart" link under the product; with Pro
  active only a check mark appears (seen in testing, cause not yet traced).

## 4. Settings reference

All settings live in the option `lc_lpr_settings` (one array). Field ids:

- General: `heading_type`, `default_heading`, `default_heading_description`,
  `popup_view_times`, `variable_add_to_cart`, `grid_lg_items` (4), `grid_md_items` (3),
  `grid_sm_items` (2), `grid_column_gap` (20).
  Pro only: `layout_type`, slider (`is_autoplay`, `autoplay_speed`, `smart_speed`,
  `is_loop`, `slide_by`, `slider_lg_items`, `slider_md_items`, `slider_sm_items`,
  `slider_column_gap`), popup size (`popup_lg_size`, `popup_md_size`, `popup_sm_size`),
  visibility (`show_continue_shopping`, `show_go_check_out`, `show_close_icon`).
- Style: Pro color fields (see `free-vs-pro.md`), `custom_style` (free).
- Global: `active_global_settings` (default on), `global_categories`,
  `global_custom_categories`, `global_tags`, `global_filtering`, `global_on_sale`,
  `global_products_number`, `disable_global_override`.

Breakpoints used by the grid and slider: tablet at 991px and below, mobile at 767px and
below (browser width, media queries).

## More Places (Free 3.6.0 / Pro 3.4.0)

Recommendations outside the popup, a Pro feature (the cart drawer counts as one too). In Free
the "More Places" tab is a Pro card plus a live example of every place, with nothing to save.
In Pro the tab (`lpr-places-settings`) has one
card per place (fields share a `group`; the first field is an on/off `switch` and the
other rows hide while it is off). The tab has its own live preview per place.

| Place | Plugin | Hook | List id |
|---|---|---|---|
| Cart page "Complete your order" | Pro (code in Free `includes/class-places.php`, shows only when Pro is active) | `woocommerce_after_cart_table`, `render_block_woocommerce/cart` | `lpr_cart` |
| Product page "Frequently bought together" | Pro (`places/includes/class-fbt.php`) | `woocommerce_after_add_to_cart_form` | `lpr_fbt` |
| Checkout "Last chance to add" | Pro (`places/includes/class-checkout-offer.php`) | `woocommerce_review_order_before_payment`, `render_block_woocommerce/checkout` | `lpr_checkout` |
| Thank-you page | Pro (`places/includes/class-thankyou.php`) | `woocommerce_thankyou` (priority 30) | `lpr_thankyou` |

- Products come from `Places::recommendations_for()`: the usual sources of each product
  (manual picks, rule, linked products, store-wide rule, smart picks), minus the products
  themselves and the cart. For several products the lists are merged by reciprocal rank
  fusion (score += 1 / (2 + rank) per list): a product recommended for three cart items
  beats one recommended for one, and each list's first pick still ranks high. Ties keep the
  order of the source products. Filter `lpr_place_recommendations`.
- Cart page and thank-you page: Grid or Slider (`*_layout`, filter `lpr_place_layout`).
  The slider is native scroll-snap with arrow buttons in `assets/js/places.js`, no library.
- Frequently bought together and the checkout offer offer simple products only. The checkout
  offer is off by default and limited to a share of the cart total
  (`checkout_offer_share`, default 30%), with a best-seller fallback from the cart's categories.
- Analytics: places push the same `lpr_popup_*` dataLayer events with their list id, and
  items added from FBT and the checkout offer are tagged in the cart item (`lpr_analytics`).
- New installs: cart page, FBT and thank-you are on. Existing stores: off until switched on
  (a checkbox key that is missing counts as off).

## Store-wide rule: hand-picked products (3.6.0)

What to Recommend (section id `lpr-global-settings`, was Default Recommendations) > Store-wide Rule > "Choose Products By":
- `global_rule_type` = `rule` (default): categories, tags, sort and number as before.
- `global_rule_type` = `manual`: `global_products` (ordered product ids, picked with a
  searchable select, AJAX `lpr_search_products`) is the list for every product that falls
  back to the store-wide rule, in the picked order, minus the product itself, hidden or
  out-of-stock products and the cart. Same in Free and Pro; Campaign Mode works with it too.

## Smart recommendations (Free 3.6.0 hooks / Pro 3.4.0 engine)

**Owner intent:** Pro only; the default store-wide rule for new Pro installs; "Fill empty
spots" on by default. Simple to set up, nothing to configure for it to work.

Where it shows up:
- Store-wide Rule > Choose Products By: `global_rule_type` = `smart` ("Smart picks, learned
  from your orders"). In Free the option is shown locked with a PRO badge (radio `locked`
  key); a saved `smart` without Pro behaves like `rule`.
- Product Recommendations tab (Pro panel): "Smart" (`type` = `smart-selection`). Counts as
  the product's own setting (beats linked products and the store-wide rule, like Dynamic).
- `smart_fill` ("3. Fill Empty Spots", Pro): lists from hand-picked products, linked products
  or the hand-picked store-wide rule that are shorter than one popup row
  (`grid_lg_items` / `slider_lg_items`, filter `lpr_smart_fill_target`) are topped up with
  smart picks, own picks first. Lists from category rules are never topped up.
- Status card (`smart_status`) with "Rebuild now", and `smart_months` (3, 6 or 12, default 6).
- Smart Options row (`smart_options`, shown with Smart picks): `smart_personal` (default on),
  `smart_skip_bought` (default off), `smart_cheaper` (default off). Existing stores get
  `smart_personal` on once (`lpr_smart_personal_default`).
- Product panel: under "Smart", the picks shown now with the reason for each ("Bought
  together in N orders", "Similar product from the same category", "One of your best
  sellers", plus a note when shoppers often or rarely add it). AJAX `lpr_smart_preview`
  (panel nonce, `edit_post`).

How picks are made (Pro `smart/`):
1. Learned pairs: products bought in the same paid order within `smart_months`
   (orders with more than 20 different products are skipped). Variations count as their
   parent. Score = orders together / sqrt(orders of the recommended product + 10)
   (filter `lpr_smart_smoothing`), so best sellers rank well without taking over.
2. Similar products to fill up: best sellers from the same categories, those within half to
   double the price first, then best sellers from the whole store.
3. Only published, visible, in-stock products; never the product itself.
4. Each product keeps its best 50 learned pairs.
5. What converts: each pair's score is multiplied by a boost (0.5 to 2) from the analytics
   counts of the same period: (adds + 30 x store rate) / (views + 30) / store rate. Needs
   200 views and 5 adds store-wide first. Filters `lpr_smart_learn_from_views`,
   `lpr_smart_learn_min_views`.
6. Lower-priced first (`smart_cheaper`): picks that cost no more than the product come first.

Personal picks (`smart_personal`), only where pages are never cached: AJAX (popup, drawer),
cart, checkout, and logged-in visitors (filter `lpr_smart_personal_context`). Products in the
`lpr_viewed` cookie (up to 15 product IDs, newest first, set by a small inline script on
product pages so it works with page caches) and products that go with what a logged-in
customer bought (user meta `_lpr_bought`, up to 30, kept up to date by paid orders) move to
the front. `smart_skip_bought` leaves out what a logged-in customer bought.

Origin: `recommendation_origin($source, $product)` returns `smart` (Smart product or rule,
or a filled spot), `own` (anything else) or '' (not recommended now). Analytics stores it
per view, click, add and order line (column `origin`, item meta `_lpr_origin`) and the
dashboard compares "Smart picks vs your own picks".

Best selling / top rated lists (dynamic selection, the category rule and the smart fallback)
break ties by newest, then id, so the same products show on every request.

Free hooks: `lpr_smart_available` (bool), `lpr_smart_product_ids ($ids, $product_id, $limit)`.
Without Pro, `smart_product_ids()` falls back to same-category best sellers.

## Modern popup (Free 3.6.0, `includes/class-popup-modern.php`, `templates/modern-popup.php`)

- Setting `popup_design`: `modern` (default for new installs) or `classic`. Stores with saved settings and no key get `classic` (`Popup_Modern::design()`, and `add_default_settings()` in Free and Pro on reactivation). Filter `lpr_popup_design`.
- `lpr_get_popup_data` includes `templates/modern-popup.php` (theme override `yourtheme/lpr/templates/modern-popup.php`) instead of the classic template. It fires the same actions: `lpr_before_modal_content`, `lpr_before_products_loop` / `lpr_after_products_loop` (Pro add-on saving note and prices), `lpr_after_modal_content`, plus `lpr_popup_modern_summary` (Pro free shipping bar, where = popup, bar only).
- Own cards (`li.lpr-pm-card.single-lpr[data-lpr-product]`): simple products get a WooCommerce AJAX Add button; variable products a variation select when "variable add to cart" is on and no attribute is "Any", else a link to the product. Filter `lpr_popup_modern_card_html`.
- Heading: "Goes well with %title%" (new default); a store still on the old stock heading gets the new wording; own headings and Heading & Description show as set.
- Accent: Pro `checkout_bg`, else #1d2327 (filter `lpr_popup_accent_color`). CSS `assets/css/popup-modern.css`, scoped to `#lpr-modal.lpr-modal--modern`; phones (600px and less) get a bottom sheet.
- `modal.js`: modern branch (no Owl slider), arrows with scroll snap, Esc and a Tab loop, focus on the dialog and back on close, "Added" state plus a short notice, `admin-ajax lpr_popup_summary` refreshes subtotal and bar. dataLayer and analytics work as before (cards carry `data-lpr-product`).
- Both designs: block product grids (Product Collection, Product Button) open the popup too: `modal.js` remembers the clicked `[data-product_id]` and listens for `wc-blocks_added_to_cart` (skipped when a jQuery `added_to_cart` came for the same add). Focus loop, Esc, `prefers-reduced-motion`, 44px touch targets on coarse pointers, a stale-response guard (`openToken`) and a single set of namespaced close handlers. `Popup_Modern::readable()` picks the text colour on the accent (white, #1d2327 or black, whichever reaches 4.5:1) and an "ink" shade for accent text on white; the drawer uses the same.
- Tests: `ui/popup.js`.

## Free shipping bar (Pro 3.4.0, `places/includes/class-free-shipping.php`)

- Settings in More Places (group `freeship`): `freeship_bar` (default on for new installs), `freeship_source` (`auto` = WooCommerce Free shipping method of the shopper's zone with "requires" min_amount or either, lowest amount wins; `custom` + `freeship_amount`), `freeship_products` + `freeship_number` (1 to 4, default 3), `freeship_drawer`, `freeship_cart`.
- Counted like `WC_Shipping_Free_Shipping`: displayed subtotal, minus discounts (and their tax when prices show with tax) unless the method ignores discounts. No bar for empty carts, carts that need no shipping, or without a rule. Filters `lpr_free_shipping_rule`, `lpr_free_shipping_products`.
- Products (simple, in stock, visible): recommended for the cart and priced from the gap to 2x, in recommendation order; then best sellers in that range (`wc_product_meta_lookup`); then recommended up to 3x, cheapest first; then the closest cheaper ones.
- Output: `section.lpr-place[data-lpr-place=shipping]`, cards carry `data-lpr-source`, so the analytics tracker tags adds as place `shipping` (no add-on saving). Cart page: classic `woocommerce_before_cart`, block via `render_block_woocommerce/cart`; `free-shipping.js` refreshes from `wc-ajax=lpr_freeship` after cart changes (classic events, the `wc/store/cart` store). Drawer: the drawer response carries `freeship`, and its own list leaves out the bar's products.
- Tests: `tests/09-freeship.php`, `ui/freeship.js`.

## Discounts (Pro 3.4.0, module `discounts/`)

Three optional percentage discounts on recommended products, each off until the owner turns it on (missing checkbox = off):

| Setting | Code | Discounted lines | Units discounted |
|---|---|---|---|
| `discount_bundle` + `discount_bundle_pct` (10) | `lpr-bundle`, "Bundle saving" | cart item data `lpr_deal` kind bundle (added from FBT) | min(line qty, main product qty) |
| `discount_addon` + `discount_addon_pct` (10) | `lpr-addon`, "Add-on saving" | cart item data `lpr_deal` kind addon (added right there from the popup or drawer) | min(line qty, main product qty) |
| `discount_offer` + `discount_offer_pct` (15) | `lpr-offer`, "Checkout offer saving" | cart item flag `lpr_offer` (sets `lpr_deal` kind offer) | 1 |

- Type (`discount_{kind}_type`): `percent` (`_pct`, 1 to 90) or `fixed` (`_amount`, decimal in the store's entered prices, Pro field type `deal_amount`; a cheaper product becomes free, never below 0). Bundle and add-on saving can turn on `_tiers` ("Save more with more"): `_tier2` and `_tier3` (Pro field type `deal_tiers`, stored as text) apply to every unit once the cart has 2, or 3 or more units of that discount (`Discounts::units()`). Defaults: percent base +5 / +10, fixed x1.5 / x2. Tiers never go below the previous value; percentages are capped at 90. Filter `lpr_discount_value($value, $which, $type, $count)`.
- Shown prices use `display_price_after()` (fixed amounts go through `wc_get_price_to_display`, so the tag shows the real saving with tax when prices show with tax). FBT prints `data-deal-prices` (prices for 1, 2, 3+) and the script picks one by the number of ticked extras. Popup and drawer show the price for one more unit than the cart has (`note()` says "Add one more and save 15% on each").
- Server-side marker: `Discounts::mark_deal()` (`woocommerce_add_cart_item_data`, priority 30) sets `lpr_deal` = {kind, source} only when the add was made right there (`via` direct, not a click remembered in the cookie), the product is in `get_recommended_products_id()` of the source's parent (filter `lpr_discount_is_recommended`), the source is in the cart at full price, and product != source. The request tag is read even with the analytics module off. Checkout offers get it only after `Checkout_Offer::ajax_toggle()` checks the product is one of `offers()` right now; the source comes from the offer, never the request.
- Main product: the source must be in the cart on a full-price line (no `lpr_deal`, no `lpr_offer`), so discounted products can't keep each other discounted. Checkout offer: the product it was offered for (at any price, it may be a bundle extra; when the flag is 1, any other line) must be in the cart, never another offer line.
- Order lines save `_lpr_deal` (kind, qty, type, value) at checkout, so `WC_Order::recalculate_coupons()` in the admin keeps the same saving.
- Codes typed by shoppers (classic form or Store API apply-coupon) get "doesn't exist" (`Discounts::typed()`).
- Each discount is a code-only WooCommerce coupon (`woocommerce_get_shop_coupon_data`) of type `lpr_deal`, registered as a cart coupon type so WooCommerce never shows "not applicable" errors. `woocommerce_coupon_get_apply_quantity` sets the units, `woocommerce_coupon_get_discount_amount` the per-unit amount. WooCommerce handles tax, rounding, order lines and reports.
- Applied and removed in `woocommerce_before_calculate_totals` (priority 30) by writing `applied_coupons` directly: no notices.
- Rules: `discount_on_sale` (off: sale items skipped), `discount_alone` (off: works with other coupons). "Individual use only" coupons always replace them. A shopper removing a discount (classic [Remove], wc-ajax `remove_coupon`, Store API remove) keeps it off for the session (`lpr_deals_declined`); WooCommerce's own removals don't count.
- Display: FBT shows crossed-out prices and a saving line while the main product is ticked; the checkout offer, popup (`lpr_before_products_loop` / `lpr_after_products_loop`, filter on `woocommerce_get_price_html`) and drawer show the old price crossed out, the new price and "10% off" (variable products: price range plus the tag). Nothing is shown when the discount would not apply right now (`Discounts::show_percent()`). Cart and checkout show the names, never the codes (block labels via `registerCheckoutFilters`); cart lines say "Bundle saving: 10% off"; the mini cart lists savings under its subtotal.
- Works with the analytics module off (`Discounts::add_tag()` reads the request tag itself).
- Tests: `tests/08-discounts.php`, `ui/discounts.js`.

## Note at the top of What to Recommend (3.6.0)

Field type `recommendation_flow` (Free and Pro): "These settings are for all your products.
To change them for one product, edit that product and open its Recommendations tab, in the Product data box." "Open a product" opens the most recently edited product with
`&lpr_tab=1`, which opens its Recommendations tab. That tab starts with "Picks set here are for
this product only. Products with nothing set use the What to Recommend settings." (link back).
