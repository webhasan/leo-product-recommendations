# Security model (since Free 3.4.0 / Pro 3.2.0)

Rules every change must keep. Reviewed Oct 2026.

## Entry points

| Entry point | Who | Checks |
|---|---|---|
| `lpr_initial_data`, `lpr_fetch_products`, `lpr_fetch_categories`, `wpr_fetch_tags` (admin-ajax) | logged in only (no `nopriv`) | nonce `lc-panel-security` + `edit_products` (+ `edit_post` for `post_id`), `Admin_Ajax::verify_request()` |
| `save_post` -> `on_save_post()` | product editors | product post type, not autosave/revision, `edit_post`, nonce `lc-panel-security`, every field sanitized |
| `lpr_get_popup_data` (admin-ajax, nopriv) | public | nonce `lc-ajax-modal`, `is_public_product()` (published, not hidden, not password protected; or user can edit) |
| `wc-ajax=lpr_tracking_data` | public | nonce `lc-ajax-modal`, max 30 IDs, `is_public_product()` per ID |
| `lc_ajax_add_to_cart` (admin-ajax, nopriv) | public | no nonce (same as WooCommerce core add to cart) |
| Settings pages / `options.php` | `manage_options` | core options nonce, `sanitize_settings()` |
| Reset Colors (`?action=rest_color`) | `manage_options` | nonce `lpr_reset_colors` |
| Pro `lpr_license` (admin-ajax, POST) | `manage_options` | nonce `license-nonce`, action whitelist, server-side URL/instance |
| Pro REST `lpr/v1/settings` | `manage_options` | |
| Pro REST `lpr/v1/product-recommendations/{id}` | public for published products, otherwise `edit_post` | product list filtered the same way |
| Deactivation feedback (admin-ajax) | `activate_plugins` | nonce `valid-feedback-submit`, TLS verified. Sends only plugin, version, reason, comment (owner decision Oct 2026: no site URL, theme, plugins or environment) |

## Output

- Everything printed is escaped at output (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`,
  SVG icons via `wp_kses` + `Product_Recommendations::svg_allowed_html()`).
- CSS: colors through `sanitize_color()` (Free) / `Product_Recommendations_Pro::css_color()`
  (Pro, also works with an old Free that doesn't sanitize), numbers through `absint`/`(int)`,
  custom CSS through `wp_strip_all_tags()` (not `esc_html`, which breaks `>` selectors).
- `%title%` in the heading is `esc_html`'d.
- JS: server text goes in with `.text()`. HTML from other plugins (tracking data) is parsed
  with `DOMParser` (inert) and only `gtm4wp*` elements / `data-*` attributes are copied.

## Outbound requests

- leocoder.com license/update API via `wp_safe_remote_get` (TLS verified). Failed update
  checks are cached 1 hour.
- Deactivation feedback via `wp_remote_post` (TLS verified), only when the admin submits it: plugin name, version, reason key, comment. Nothing else.
