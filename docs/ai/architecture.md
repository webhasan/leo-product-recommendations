# Architecture and code map (Free)

## Files

| Path | Purpose |
|---|---|
| `leo-product-recommendations.php` | Plugin header, HPOS declaration, boot `Product_Recommendations::init()` |
| `includes/class-product-recommendations.php` | Core class: hooks, queries, popup data, settings schema, defaults |
| `includes/class-admin-ajax.php` | AJAX for the product panel (initial data, products, categories, tags) |
| `includes/class-ajax-add-to-cart.php` | Server side of single product and popup AJAX add to cart |
| `includes/class-settings-page.php`, `settings-fields-type.php` | Settings page renderer and field types |
| `includes/option-select-products.php` | Container `#pr-app` for the React product panel |
| `includes/tutorials.php` | Tutorials tab |
| `templates/modal-modal.php` | Empty modal wrapper printed in `wp_footer` |
| `templates/template-modal-content.php` | Popup head, buttons, heading, product loop |
| `templates/template-recommendations-products.php` | One product card |
| `assets/js/panel.js` + `components/`, `functions/` | React product panel (built to `panel.min.js`) |
| `assets/js/modal.js` | Opens popup on `added_to_cart`, popup limit, slider init |
| `assets/js/ajax-add-to-cart.js` | Intercepts `form.cart` submit on single product pages |
| `assets/js/settings.js` | Settings page UI (color picker, select2, CSS editor) |
| `assets/css/*.scss` | Sources for `modal.css`, `panel.css`, `settings.css` |
| `feedbacks/deactivation-feedback/` | Deactivation feedback form |

## Stored data

| Key | Type | Content |
|---|---|---|
| `lc_lpr_settings` | option | All settings (Free and Pro), one array |
| `lc_lpr_settings_backup` | option (not autoloaded) | One-time copy taken before 3.3.0 first saves: `{time, version, settings}` |
| `_lc_lpr_data` | product post meta | `heading_type`, `heading`, `heading_article`, `type` (`manual-selection` / `dynamic-selection`), `products`, `categories`, `tags`, `orderby`, `sale`, `number` |
| `lpr-popup-count` | browser localStorage | `{count, time}` for the Popup Show limit |
| `lpr_added_product` | cookie (5 min, COOKIEPATH, not HttpOnly) | Product ID added with a normal form post; `modal.js` opens the popup on the next page load and deletes it (since 3.4.0) |

Saved in `on_save_post()`, nonce `lc-panel-security` (field `lc_pr_panel_nonce`).

## AJAX actions

| Action | Who | Purpose |
|---|---|---|
| `lpr_get_popup_data` | `?wc-ajax=lpr_get_popup_data` (POST, used by modal.js since 3.4.0) plus admin-ajax priv + nopriv (fallback), nonce `lc-ajax-modal` | Returns popup HTML for `product_id`. Rendered as a front-end request on wc-ajax so front-end-only plugins (GTM4WP, pixel plugins) add their product data |
| `lc_ajax_add_to_cart` | front end (priv + nopriv) | Adds simple, variable and grouped products, returns fragments and cart hash. For types with their own `woocommerce_add_to_cart_handler_{type}` it adds nothing and returns `{success:false, data:{fallback:true}}`, and the JS posts the form normally |
| `lpr_initial_data`, `lpr_fetch_products`, `lpr_fetch_categories`, `wpr_fetch_tags` | product panel, nonce `lc-panel-security` | Panel data |

## JS events

- WooCommerce: listens to `added_to_cart`; triggers `adding_to_cart` and `added_to_cart`
  from the single product AJAX submit.
- Plugin: `before_open_lpr_modal` and `after_close_lpr_modal` on `body`.
- Localized objects: `lc_ajax_modal` (url, nonce, `popup_view_times`, `layout_type`,
  `added_cookie` {name, path, domain}, `popup_on_cart`, `is_cart_page`, `popup_url`),
  `lc_ajax`, `lc_pr_panel_data`, `lc_slider_setting` (Pro).

## PHP hooks

Settings framework: filter `lpr_settings_pages` (`$pages, $base`), action
`lpr_settings_field_{type}` (`$field, $base, $setting_id`). Constants
`LPR_SETTINGS_API_VERSION` (Free) and `LPR_PRO_SHARED_SETTINGS` (Pro).

Actions: `lpr_before_action` (Free/Pro bridge), `lpr_before_modal_content`,
`lpr_start_modal_head`, `lpr_end_modal_head`, `lpr_before_products_loop`,
`lpr_after_products_loop`, `lpr_after_modal_content`, `lpr_before_recommended_product`,
`lpr_after_recommended_product`.

Filters: `lpr_show_buttons`, `lpr_show_continue_shopping`, `lpr_go_checkout`,
`lpr_cart_count`, `lpr_show_close_icon`, `lpr_checked_icon`, `lpr_cart_icon`,
`lpr_fix_thumb`, `lpr_title_price`, `lpr_fix_price` (theme compatibility lists).

Add to cart (3.4.0): `lpr_ajax_add_to_cart_supported` (`$supported, $product, $handler`),
`lpr_popup_after_reload` (`true, $added_id, $product_id`), `lpr_popup_after_reload_on_cart`
(`false`). Form class `lpr-no-ajax` opts a `form.cart` out of the AJAX submit.

## Template override

Copy a file from `templates/` to `wp-content/themes/<theme>/lpr/templates/<file>.php`.
Lookup order: theme, Pro plugin, Free plugin.

## Build

Needs Node 22.22+ or 24.15+ (required by `@wordpress/scripts` 36).

| Command | What it does |
|---|---|
| `npm install` | Once, after cloning or when `package.json` changes |
| `npm start` | Watch mode: Sass recompiles `assets/css/*.scss`, webpack rebuilds `assets/js/*.min.js` (development build, with source maps). Ctrl+C stops both |
| `npm run build` | Production: compressed CSS without source maps, then minified JS and `*.min.asset.php` |

- **Always run `npm run build` before committing.** `npm start` writes development builds
  into the same `.min.js` files.
- Built files are committed and shipped: `assets/js/*.min.js`, `assets/js/*.min.asset.php`,
  `assets/css/*.css`. Source maps (`*.map`) are git-ignored.
- `webpack.config.js` keeps the output in `assets/js` (next to sources and the vendored
  Select2, Spectrum, Lity) and sets `clean: false`. Never remove it: the default config
  empties the output folder.
- Scripts are enqueued with `script_asset()`, which reads dependencies and a content-hash
  version from `*.min.asset.php`. The panel needs `react-jsx-runtime` (WordPress 6.6+).
- CSS files still use the plugin version for cache busting.

## Repos

- Free: `git@github.com:webhasan/leo-product-recommendations.git`
- Pro: `git@github.com:webhasan/leo-product-recommendations-pro.git`
- WordPress.org SVN for Free releases.
