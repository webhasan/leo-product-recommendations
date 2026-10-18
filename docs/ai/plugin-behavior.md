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
3. **Global fallback.** If the product has no manual products and no dynamic selection,
   and Global is active, the Global rule is used.
4. **Nothing.** If none of the above applies, no recommendations.

Product picks and Global are never mixed. A product with manual picks shows only those
picks, even if there are fewer than the Global "Number of Products".
(**Owner intent**, matches code.)

### Global rule options (free feature)

- Categories: "Product Related Category" (`same_categories`, the added product's own
  categories) or Manual (chosen categories; empty = all categories).
- Tags (empty = all tags). Categories and tags combine with AND.
- Products Filtering (`global_filtering`): `rand`, `newest`, `oldest`, `lowprice`,
  `highprice`, `popularity` (best selling, `total_sales`), `rating` (`_wc_average_rating`).
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
