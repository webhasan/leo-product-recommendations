# Known issues and open questions

Found during code review and local testing, October 2026 (Free 3.2.0, Pro 3.1.0).
Remove an item when it is fixed. Add new items as you find them.

## Behavior and bugs

1. **Price sorting uses `_regular_price`.** Ignores sale prices; variable parents usually
   have no `_regular_price`. `_price` is likely the intended key.
2. **Deleted products in manual picks.** `manual_query_products()` calls methods on
   `wc_get_product($id)` without checking for `false`, which can fatal if a picked product
   was deleted.
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

1. Free's locked color teaser (image) does not match Pro's real fields.
2. Locked Dynamic Selection demo uses sample categories and tags that do not exist in the store.
3. Typos: "Continue Shoping", "Gird Or Slider", "WPR configuration panel" in the Skip
   Manual Selection help text.
4. Label differs: "Variable Products Add To Cart" (Free) vs "Variable / Group Products" (Pro).
5. License page button reads "Active Lincese" / "Deactive Lincese".
7. Untranslatable strings in the panel (`'Popup Heading'`, `'Select By'` passed as
   variables); `__('Documentation»', { textDomain })` passes an object as the domain.

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
