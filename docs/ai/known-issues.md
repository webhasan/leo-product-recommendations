# Known issues and open questions

Found during code review and local testing, October 2026 (Free 3.2.0, Pro 3.1.0).
Remove an item when it is fixed. Add new items as you find them.

## Behavior and bugs

1. ~~Price sorting uses `_regular_price`~~ Fixed in 3.4.0: `lowprice`/`highprice` order by
   `wc_product_meta_lookup` min/max price (`price_order_clauses()`), like WooCommerce's shop sort.
2. ~~Deleted products in manual picks~~ Fixed in 3.4.0: `manual_query_products()` skips ids
   where `wc_get_product()` returns false.
3. **Custom CSS escaped with `esc_html()`** in Free `settings_css()`. Selectors with `>`
   become `&gt;` and break. Pro prints custom CSS raw (works, but unescaped).
4. **Free honors product-level dynamic data saved under Pro.** `is_dynamic_selection()` is
   not gated by `is_pro_activated()`. Decide if this is intended.
5. **"View cart" link after adding from the popup** shows in Free but not with Pro. Trace
   which template or CSS removes it and decide the intended behavior.
6. **Pro product template fires the wrong hook.** It ends with
   `do_action('lpr_before_recommended_product')`; `lpr_after_recommended_product` never
   fires when Pro is active.
7. **Default "Heading & Description" loses its formatting.** `get_template_data()` runs
   it through `wp_kses()` with only span/b/strong/i/br and wraps it in `<h2>`.
8. **Switching heading type in settings blanks the other heading** on save
   (`heading_selection` renders the non-selected value as empty).
9. ~~Custom product types / repeated fields / file inputs in AJAX add to cart~~ Fixed in
    3.4.0 (fallback to a normal post plus popup after reload, FormData).
10. **Possible race in `WPEditor`** (panel): TinyMCE inits on a 400ms timer, independent of
    when saved data loads. To verify on a slow server.
11. **Pro update check does not cache failures.** Without a valid license, every read of
    the update transient in admin triggers a request to leocoder.com.
12. **Plugin load order dependency.** Pro works only because its folder name sorts
    before Free's. See `codebase-internals.md` section 4.
13. ~~Blocksy: two "Add to cart" buttons per popup card~~ Fixed in 3.4.0 with CSS
    (`.single-lpr:has(form.cart) .ct-woo-card-actions { display: none }`).

- Pro add-on saving in the popup and drawer needs Pro's analytics tracker, which reads the
  source product from the popup's dataLayer block. A store that turns either off with code
  (`lpr_enable_analytics` or `lpr_popup_datalayer` set to false) still sees "save 10%" but
  popup and drawer adds get full price. FBT and the checkout offer tag adds on the server
  and are not affected. There is no setting for either filter.
- Pro FBT box: the tier it shows counts only the ticked extras; the cart also counts bundle
  extras already in the cart, so the shopper can be charged a bigger saving than shown.

## Security and hardening

Security review (Oct 2026, Free 3.4.0 / Pro 3.2.0) fixed: public REST routes, `nopriv`
panel AJAX, popup/REST/tracking data for non-public posts, unescaped Pro CSS and
templates, `%title%` in the heading, custom CSS output, save handler without capability
or post type checks, deactivation feedback (`sslverify => false`, no capability check,
no disclosure), license messages via `.html()`, API key in GET URLs, uncached failed
update checks. Details in `docs/ai/security.md`.

Still open:
1. License key is "encrypted" with a key hard-coded in the source (`leo_crypt()`). It is
   obfuscation only. Changing it needs a migration (decrypt with the old key on read).
2. The license page prints the full API key into the input's `value` (admins only).
   Deactivation reads it from there; moving deactivation to the stored key would avoid it.
3. `lc_ajax_add_to_cart` has no nonce, like WooCommerce's own add to cart (CSRF can only
   add products to the visitor's own cart).

## Admin UI and copy

2. Locked Dynamic Selection demo uses sample categories and tags that do not exist in the store.
3. Typos: "Continue Shoping", "Gird Or Slider", "WPR configuration panel" in the Skip
   Manual Selection help text.
4. Label differs: "Variable Products Add To Cart" (Free) vs "Variable / Group Products" (Pro).
5. License page button reads "Active Lincese" / "Deactive Lincese".
7. Untranslatable strings in the panel (`'Popup Heading'`, `'Select By'` passed as
   variables); `__('Documentation»', { textDomain })` passes an object as the domain.

9. Analytics page wording still says "popup" (Popup views, Adds per popup, Added in the
   popup) although it now counts the drawer and More Places too, and there is no
   breakdown by place (the stats table has no place column).
10. Recommendation cards in More Places use the theme's product card. Themes that show the
    add to cart button only on hover hide it on touch screens. Astra is handled in
    `places.css`; other themes may need the same, or the plugin's own compact card.
11. Cart drawer with many items: the recommendations sit below the whole cart list, out of
    view until the shopper scrolls the drawer.
12. Popup cards: when titles wrap to two lines the add to cart buttons do not line up.
13. License page: heading says "Activate License" when it is already active, and the
    status reads "Activated License." (built JS, `license.js`).

## Build and housekeeping

1. `fetch_products` runs the same product query twice (`get_posts` + `WP_Query`).
2. `uninstall.php` leaves `_lc_lpr_data` product meta and Pro license options.
3. Pro's activation defaults add a meaningless `reset_colors` key (the reset link field has
   a `default`). Harmless.
4. Pro `settings_css()` still echoes setting values without escaping. Values are
   sanitized on save since 3.4.0, but values stored earlier are printed as they are.

## Public docs out of date (leocoder.com)

1. Template override path is `<theme>/lpr/templates/`, not `/wpr/templates/`.
2. REST routes are `lpr/v1/product-recommendations/{id}` and `lpr/v1/settings`
   (docs show `product-recommend` and `wpr/v1`).
3. Filters `wc_pr_show_in_product_archives`, `wc_pr_show_in_singe_product`,
   `wc_pr_show_in_gutenberg_product_block` **do not exist** in the code.
4. Popup Show limit, close icon, `%title%` and `[item, items]` are not documented.
5. WordPress.org lists "Dynamic selection" as a free key feature; only Global dynamic
   rules are free.

## Fixed (October 2026)

Release test pass for Free 3.7.0 (not released yet):

- Random sort: the popup, drawer and FBT showed a saving, but the add usually got full
  price, because the server drew a new random list to check the product. Free 3.7.0 adds
  `can_recommend()` (same rule, no limit, when the list is random); Pro uses it when present.
  The checkout offer had the same problem when a shopper ticked a product (could fail with
  "This product can't be added"); it now uses the same check.
- Block themes: `popup-add.js` now fires WooCommerce's `adding_to_cart` event, so Pro tags the
  add (saving and analytics) like it does with WooCommerce's own script.
- Pro FBT and checkout offer run `woocommerce_add_to_cart_validation` before adding.
- Pro uninstall cleans every site on multisite and clears the WP-Cron build step.
- Pro drawer: Add link without the script goes to the cart (was the drawer AJAX URL);
  variation prices shown the way the store displays prices (with tax when set).
- Free milestones: a product removed from the cart no longer counts as added from the popup.
- Free and Pro: dependency notices are translated when they print, not at plugins_loaded.
- Top Rated sort includes products with no reviews.


Shared settings framework (Free 3.4.0 + Pro 3.2.0, see `codebase-internals.md`):

- Grid `100 / items` division by zero guarded in Free and Pro `settings_css()`; empty
  numbers fall back to defaults.
- Reset Colors: nonce + capability, unknown group no longer wipes all settings, Free no
  longer calls a missing `set_settings()`.
- Pro settings now saved through Free's sanitizer and rendered with escaping.
- Saving on the Free-only settings screen no longer erases Pro settings.
- Settings markup: double `</div>` and unclosed tag `<option>` fixed.

- Grid showed one item fewer per row than set: themes such as Astra add `gap: 20px` to
  `.woocommerce ul.products`, on top of the plugin's margins. `modal.css` now sets
  `gap: 0 !important` on the popup list.
- Free popup lost its cart and check icons (the cart badge then overlapped the Checkout
  button): the template printed the SVG icons through `wp_kses_post()`, which strips
  `<svg>`. Now `wp_kses()` with `Product_Recommendations::svg_allowed_html()`.

- Build tools updated to `@wordpress/scripts` 36 (Free 3.4.0 / Pro 3.2.0); Free's
  committed bundles were development builds, now production.

- Product panel ready for React 19: `react-reorder` replaced by `@dnd-kit`, mounted with
  `createRoot`; Pro panel infinite scroll uses functional state updates.

Earlier:

- Pro manual picker label "Select Products Pro" changed to "Select Products".
- Pro `lpr_license` AJAX: admin only (`manage_options`), no logged-out access, server URL,
  API name, plugin data and instance taken from the server, action limited to
  status / activate / deactivate. Only product ID and API key come from the form.
- Dynamic and Global queries always exclude hidden and out-of-stock products.

## Unknowns to verify

- Exact behavior of grouped products inside the popup (quantity per child).
- Whether quick view plugins other than the ones in `modal.js` close correctly.
