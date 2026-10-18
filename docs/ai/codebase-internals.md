# Codebase internals

Deep read of both plugins, October 2026 (Free 3.2.0, Pro 3.1.0). Read this before
refactoring or upgrading packages. File-level map: `architecture.md`.

The plugin has three layers, each with its own technology:

| Layer | Technology | Where |
|---|---|---|
| Admin settings page | Own PHP mini framework on the WordPress Settings API + jQuery plugins | `class-settings-page.php`, `settings-fields-type.php`, `settings.js` |
| Product edit panel | React (via `wp.element`), built with `@wordpress/scripts` 6 (2019) | `panel.js`, `components/`, `functions/`, `class-admin-ajax.php` |
| Front-end popup | jQuery + PHP templates, Owl Carousel (Pro) | `modal.js`, `ajax-add-to-cart.js`, `templates/`, `class-ajax-add-to-cart.php` |

## 1. Admin settings framework

### Schema

Everything is described by one PHP array returned from `settings_pages()`:

```
pages[]     id (option name), page_title, menu_title, slug, parent, icon, position, hidden
 sections[] id, tab_title, title, template (optional PHP file instead of fields)
  fields[]  id, title, type, default, description, doc, help, options, label
   chields[] (Free) / childs[] (Pro): sub-fields of wrapper types
```

- All settings are stored in **one option**, `lc_lpr_settings` (page `id`).
- Field `type` is the name of a **namespaced PHP function** in `settings-fields-type.php`
  (`LoeCoder\Plugin\ProductRecommendations\<type>()`), called as
  `$type($field, $base, $setting_id)`. Adding a field type = adding a function.
- Field types: `wrapper`, `wrapper_extend` (groups of child inputs: color_picker,
  reset_color, checkbox, number, radio, categories_select, tags_select, select),
  `radio`, `select`, `checkbox`, `text`, `info`, `css`, `pro_image` (Free only),
  `heading_selection`, `license` (Pro only).

### Runtime

- `Settings_Page::__construct($base)` hooks `admin_menu` (pages), `admin_init`
  (sections + fields, `register_setting`).
- Tabs are sections. **All tabs render inside one `<form>`**; inactive tabs are output in
  a hidden div, so saving any tab posts every field. This is why unchecked checkboxes on
  other tabs are not lost.
- `display_page()` also handles `?action=rest_color&color_ids=<wrapper>` (Reset Colors):
  deletes those sub-settings and redirects. No nonce.
- Values are read with `$base->get_setting($id)`: stored value, else field default
  (looked up by walking the schema). Checkbox missing from a saved array = off.
- `get_setting()`, `get_field_type()` and `get_default_setting()` read from the cached
  field map (3.3.0). Pro's `get_setting()` delegates to Free's. Before that, every call
  rebuilt the schema, and in Pro also read the plugin file header from disk (about 40
  times per front-end page from Pro's `settings_css()`). In Pro compatibility mode Pro
  still uses its own old `get_setting()`.
- Front-end CSS is generated from settings in `settings_css()` (`wp_head`).

### One shared framework (Free 3.3.0 + Pro 3.2.0)

Until Free 3.2.0 / Pro 3.1.0 the framework existed twice (Pro carried an older copy with
no sanitizing, little escaping and `childs` instead of `chields`). It now lives only in
Free:

- **Schema extension:** `settings_pages()` = `default_settings_pages()` (Free fields)
  passed through the `lpr_settings_pages` filter. Pro returns its full page set there
  (it is a superset of Free's), including the License page.
- **Normalization:** sub-fields are read from `children`, `chields` or `childs` and stored
  under `children` (`normalize_settings_field()`). Never rely on the old keys again.
- **Cache:** the merged schema and a flat field map (`get_field_map()`, with `_page` and
  `_parent`) are built once per request, after `plugins_loaded`.
- **Extra field types:** if `settings-fields-type.php` has no function for a type, the
  framework fires `lpr_settings_field_{type}` (`$field, $base, $setting_id`). Pro renders
  `license` this way.
- **Sanitizer** (`Settings_Page::sanitize_settings()`), built to accept everything already
  stored:
  - keeps stored keys the current page does not manage (Pro settings survive a save from
    the Free-only screen; before 3.3.0 they were erased),
  - colors: hex (3, 4, 6, 8 digits), `rgb()`, `rgba()`, `transparent`, color names,
  - numbers: empty means "use default" (key unset), otherwise `absint`,
  - text: `wp_kses` with span/b/strong/i/br (same list as `get_setting()`),
  - editor: `wp_kses_post`; css: `wp_strip_all_tags`; category/tag lists: `absint`,
  - radio/select/unknown keys: `sanitize_text_field` (arrays deep).
  WordPress runs it on every `update_option()` of the option, so it is idempotent.
- **Reset Colors** needs a nonce (`lpr_reset_colors`) and `manage_options`, and only
  removes sub-fields of an existing wrapper. Unknown group = no change.
- **Backup:** first admin load under 3.3.0 copies the option to `lc_lpr_settings_backup`
  (`{time, version, settings}`, not autoloaded). Deleted on uninstall.

### Version-aware loading

| Free | Pro | Who runs the settings page | Notes |
|---|---|---|---|
| 3.3.0+ | none | Free | Free fields + Pro teaser images |
| 3.3.0+ | 3.1.x or older | Old Pro (its own copy) | Free stays out: no `LPR_PRO_SHARED_SETTINGS` |
| 3.2.0 | 3.2.0+ | Nobody | Pro shows "update Free" and runs in **compatibility mode** (front end + product panel only); old Free hides its page because Pro is active, so no screen can overwrite Pro settings |
| 3.3.0+ | 3.2.0+ | Free (shared) | Pro adds fields by filter |

Constants: Free defines `LPR_SETTINGS_API_VERSION` (1); Pro defines
`LPR_PRO_SHARED_SETTINGS`. **Pro's `$require_free_version` must stay at 3.2.0**: raising
it turns compatibility mode off and breaks the popup on stores still on Free 3.2.0.

### jQuery side (`settings.js`, Free, used by both)

- Spectrum color picker (`showAlpha`, `preferredFormat: rgb`), CodeMirror CSS editor
  (`wp.codeEditor`), TinyMCE for the default heading (`wp.editor.initialize`),
  select2 for category/tag pickers, show/hide logic (grid vs slider, global on/off,
  same vs manual categories).
- Lity lightbox is loaded for `help` links, but no field currently sets `help`.

## 2. Product edit panel (React)

### How it mounts and saves

- PHP prints `<div id="pr-app" data-id="POST_ID">` inside the WooCommerce
  "Recommendations" product data tab (`option-select-products.php`).
- `panel.js` mounts with `wp.element.createRoot()` (React 18/19 API, since 3.3.1).
- **There is no save API.** The React app renders hidden inputs named
  `_lc_lpr_data[...]`, plus nonce `lc_pr_panel_nonce`. They are submitted with the normal
  product form and saved by PHP in `on_save_post()` (`save_post` hook) into post meta
  `_lc_lpr_data`.
- Data comes from admin-ajax (jQuery `$.ajax` wrapped in Promises):
  `lpr_initial_data` (saved meta + product titles/thumbs), `lpr_fetch_categories`,
  `wpr_fetch_tags` (Pro), `lpr_fetch_products` (20 per page, search, category filter,
  infinite scroll).

### Components

| Component | Notes |
|---|---|
| `panel.js` `SelectProduct` | One big component, all state in one `appData` object |
| `RecommendationHeading` | Heading vs Heading & Description (TinyMCE) |
| `WPEditor` | Textarea turned into TinyMCE 400ms after mount (or after `DOMContentLoaded` if still loading) |
| `RadioSelection` | Manual vs Dynamic |
| `ManualSelection` | Search (local `DebounceInput`), category `TreeSelect` (`@wordpress/components`), product list, sortable selected list (`@dnd-kit`, `SortableProduct`; mouse, touch, keyboard) |
| `DynamicSelection` (Pro) | `react-select` for categories/tags, orderby, on-sale, number |
| `Loading` | SVG spinner |

### Free vs Pro

- Pro's `panel.js` is a **fork** of Free's (Pro adds Dynamic Selection). Keep shared
  components (`ManualSelection`, `DebounceInput`, `WPEditor`, `tree.js`) identical in both.
- Free posts a hidden `_lc_lpr_data[type]=manual-selection`; Pro posts the radio value.
- Free shows the Pro Dynamic Selection as a static image (`assets/images/pro-feature.jpg`).

### Known weak spots

- `WPEditor` initializes TinyMCE on a 400ms timer, independent of when saved data arrives.
  If `lpr_initial_data` is slower, the editor may start empty. To verify on a slow server.
- No request cancellation: fast typing in search can show results out of order.
- Strings passed through variables to `__()` (`'Popup Heading'`, `'Select By'`) are not
  extractable for translation; `__('Documentation»', { textDomain })` passes an object
  as the domain.
- `class=` used instead of `className` and missing `key` props in `DynamicSelection`.
- Free's committed `panel.min.js`, `settings.min.js` and `ajax-add-to-cart.min.js` are
  **development builds** (unminified webpack 4 bootstrap, `react-is.development.js`),
  about 180 KB for the panel. Only `modal.min.js` is minified.
- Script dependencies are hard-coded in `wp_enqueue_script()`; the generated
  `*.asset.php` files are git-ignored and not used.

## 3. Front-end popup (jQuery)

### Flow

1. Any WooCommerce `added_to_cart` jQuery event (archive buttons, blocks, widgets, quick
   view) is caught in `modal.js`.
2. On single product pages, `ajax-add-to-cart.js` catches `form.cart` submits, sends the
   form as `FormData` (minus `add-to-cart` and file fields) to `lc_ajax_add_to_cart`, then
   triggers `added_to_cart` itself. It lets the form post normally when: the popup limit
   is reached, the clicked button has a name other than `add-to-cart` (Buy now), a file is
   selected, the form has class `lpr-no-ajax`, or the server answers `fallback`.
   `add-to-cart` is left out on purpose: WooCommerce's form handler runs on `wp_loaded`
   for any request with that field and would add the product a second time. The PHP
   puts it back into `$_REQUEST`/`$_POST` for validation code.
2b. Normal (page reload) adds: `remember_added_product()` on `woocommerce_add_to_cart`
   sets cookie `lpr_added_product` (skipped for AJAX, REST, admin and when there is no
   `add-to-cart` in the request). On the next page load `modal.js` reads it, deletes it,
   and calls `openRecommendationPopup()` unless the limit is reached or it's a cart or
   checkout page (`is_cart_or_checkout_page()`, which also detects Cart/Checkout blocks).
   A cookie, not page HTML, so page caching can't show it to other visitors.
3. `modal.js` checks the Popup Show limit (localStorage), closes known quick-view
   popups, requests `lpr_get_popup_data`, injects the HTML into `#lpr-modal-content`
   (empty shell printed in `wp_footer`), initializes Owl Carousel when the layout is
   slider (Pro settings via `lc_slider_setting`), runs `wc_variation_form()` on variation
   forms in the popup, and opens the modal (own tiny jQuery plugin `$.fn.lprModal`).
4. Add to cart inside the popup goes through the same path; a button inside
   `.recommended-products-wrapper` shows the green flash bar instead of a new popup.

### Server side

- **The popup HTML must stay on admin-ajax.** The template relies on WooCommerce's
  default loop hooks, and the theme lists (`lpr_fix_thumb`, `lpr_title_price`,
  `lpr_fix_price`) were tuned for admin-ajax, where themes don't load their front-end
  loop changes. Tested Oct 2026: rendering it via `?wc-ajax=` drops the title and price
  on Astra (Astra moves them off `woocommerce_shop_loop_item_title` /
  `woocommerce_after_shop_loop_item_title` on front-end requests).
- GTM4WP boots its front-end modules only when `is_admin()` is false, so admin-ajax popup
  products have no `.gtm4wp_single_productdata` / `data-gtm4wp_product_data` and GTM4WP
  pushes no `add_to_cart` for popup adds. Since 3.4.0 `addTrackingData()` in modal.js
  posts the popup product IDs to `?wc-ajax=lpr_tracking_data` after the popup opens and
  copies only `gtm4wp*` elements (into `form.cart`) and `data-gtm4wp*` attributes (onto
  `.add_to_cart_button`). Variable products only need the parent span; GTM4WP reads the
  variation from WooCommerce's `found_variation` event. Only on when GTM4WP is active
  (`lpr_popup_tracking_data`). Tested on Astra, Storefront, Kadence, OceanWP, Blocksy,
  Twenty Twenty-Three/Four/Five, Pro on and off, GTM4WP 2.0.x.
- `lpr_get_popup_data` builds `$template_data` (`get_template_data()`), runs a
  `WP_Query` with `post__in`, and includes `template-modal-content.php`, which loops
  `template-recommendations-products.php` per product.
- Product cards call standard WooCommerce loop hooks plus many **theme-specific
  branches** (Flatsome, Medibazar, OceanWP, Blocksy, Electro, Rey, Avada, Betheme...)
  and filter lists (`lpr_fix_thumb`, `lpr_title_price`, `lpr_fix_price`).
- In-popup add to cart uses `woocommerce_template_single_add_to_cart()` when
  "Variable / Group Products Add To Cart" is on (real product forms), else the loop button.
- `class-ajax-add-to-cart.php` is a **copy of WooCommerce core's form handler**
  (simple, variable, grouped). Since 3.4.0, types with their own
  `woocommerce_add_to_cart_handler_{type}` (bundles, subscriptions...) get the `fallback`
  reply before anything is added, so WooCommerce handles them with a normal post.

### Pro additions on the front end

- Pro has its own copies of both templates (grid `<ul>` vs slider `<div class=owl-carousel>`,
  visibility settings, button classes used by the color settings).
- Slider init code lives in **Free's** `modal.js`, reading Pro's `lc_slider_setting`.
- Pro's product template ends with `do_action('lpr_before_recommended_product')`
  instead of `lpr_after_recommended_product` (the "after" hook never fires with Pro).

### CSS

- `modal.scss` (813 lines) is compiled to a single-line `modal.css`. Breakpoints 1199,
  991, 767. Default grid is 4/3/2 columns, overridden by inline settings CSS.
- Generated CSS: Free `settings_css()` (Pro inactive, grid + custom CSS) or Pro
  `settings_css()` (colors, sizes, grid, custom CSS, values echoed unescaped).
- Grid widths are computed as `100 / items`. If an items value is saved as 0 or empty,
  PHP 8 throws `DivisionByZeroError` in `wp_head` on every front-end page.

## 4. Plugin boot and coupling

- Pro boot: dependency errors show a notice; if only the shared settings API is missing
  (Free 3.2.0), Pro still runs `hooks()` (compatibility mode, see section 1).
- Both plugins hook `plugins_loaded` at the default priority. Free fires
  `lpr_before_action` there; Pro attaches its listener in its own `plugins_loaded`
  callback. This works only because WordPress loads `leo-product-recommendations-pro/`
  before `leo-product-recommendations/` (alphabetical: `-` sorts before `/`).
  Renaming a folder or loading order changes would silently disable Pro.
- Shared namespace `LoeCoder\Plugin\ProductRecommendations` for both plugins; classes
  and field functions with the same names exist in both and are guarded only by which
  file gets required first.
- Pro updates: `site_transient_update_plugins` filter calls leocoder.com. Failed or
  unsuccessful responses are **not cached**, so sites without a valid license make a
  remote request each time the update transient is read in admin.
- License key stored with reversible AES using a key hard-coded in the source
  (`leo_crypt()`): obfuscation, not protection.
- Deactivation feedback (Free) posts site URL, active plugins, theme and server info to
  leocoder.com with `sslverify => false` when the admin submits the form.
- `uninstall.php` (Free) deletes `lc_lpr_settings` only; product meta `_lc_lpr_data`
  and Pro license options remain. Pro's uninstall does nothing.
