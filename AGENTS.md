# AGENTS.md: Leo Product Recommendations for WooCommerce (Free)

Context file for AI coding agents (Claude, Codex, Cursor, Copilot, Gemini and others).
Read this first, then open the file in `docs/ai/` that matches your task.

## What this plugin does

After a shopper adds a product to the cart, an AJAX popup shows recommended products.
The shopper can add those products (simple, variable, grouped) from inside the popup
without leaving the page, then continue shopping or go to checkout.

- Free plugin: this repo. Published on WordPress.org as `leo-product-recommendations`.
- Pro plugin: separate add-on in `../leo-product-recommendations-pro` (private repo).
  Pro never runs alone. It hooks into this plugin, so most behavior lives here.
- Author: Md Hasanuzzaman (LeoCoder). Docs: https://leocoder.com/docs/woocommerce-product-recommendations/

## Golden rules

1. **The code is the source of truth.** If this file or `docs/ai/` disagrees with the code,
   trust the code, then fix the doc in the same change.
2. **Keep Free working without Pro.** Every change must work with Pro active and inactive.
   Test both states.
3. **Do not break the Free/Pro contract**: the `lpr_before_action` hook, the shared settings
   option `lc_lpr_settings` (never rename or drop a field id without a migration), the
   `lpr_settings_pages` filter, the `lpr_settings_field_{type}` action, the constants
   `LPR_SETTINGS_API_VERSION` / `LPR_PRO_SHARED_SETTINGS`, the product meta
   `_lc_lpr_data`, template paths, and the `is_pro_activated()` checks. Pro depends on all of them. See `docs/ai/architecture.md`.
4. **WordPress.org rules apply.** Sanitize input, escape output, verify nonces, check
   capabilities, use the `leo-product-recommendations` text domain, no remote calls or
   upsell tricks that break plugin directory guidelines.
5. **Back-compat.** Existing stores have saved settings and product meta. Never rename an
   option key, meta key, hook, CSS class or template file without a migration.
6. **No em dashes in user-facing copy** (readme, admin text, docs).

## Where to look

| Task | Read |
|---|---|
| How recommendations are chosen, popup behavior | `docs/ai/plugin-behavior.md` |
| What is Free vs Pro, how Pro features are locked | `docs/ai/free-vs-pro.md` |
| Code map, hooks, AJAX actions, data storage, build | `docs/ai/architecture.md` |
| dataLayer events (GA4) | `docs/ai/datalayer.md` |
| Local environment and end-to-end testing | `docs/ai/testing.md` |
| Known bugs, doc mismatches, open questions | `docs/ai/known-issues.md` |
| How the code works inside (settings framework, React panel, jQuery popup, boot) | `docs/ai/codebase-internals.md` |
| Package versions and upgrade plan | `docs/ai/modernization.md` |

## Quick facts

- Main file `leo-product-recommendations.php`, core class
  `includes/class-product-recommendations.php` (`Product_Recommendations`, singleton,
  namespace `LoeCoder\Plugin\ProductRecommendations`; the "Loe" typo is real, keep it).
- Requires WP 6.6+, WC 7.0+, PHP 7.4+. HPOS compatible.
- Settings page: `wp-admin/admin.php?page=lpr-settings` (tabs: General, Style, Global, Tutorials).
- Product panel: "Recommendations" tab in WooCommerce Product data (React app, `assets/js/panel.js`).
- Front end: `assets/js/modal.js` (popup) and `assets/js/ajax-add-to-cart.js` (single product AJAX submit).
- Build (Node 22.22+): `npm install` once, `npm start` to watch, `npm run build` for production.
  Always run `npm run build` before committing (`npm start` writes development builds).

## Keeping these docs up to date (required)

When you add or change a feature, setting, hook, AJAX action, template, stored data, or the
Free/Pro split:

1. Update the matching file in `docs/ai/` in the **same change**.
2. If a fix resolves an item in `docs/ai/known-issues.md`, remove or update that item.
3. Update this file only when the rules, structure or quick facts change.
4. Before a release, confirm `docs/ai/` matches the code (part of the release checklist below).

## Release checklist

- Bump version in the plugin header, `README.txt` (Stable tag) and changelog.
- `npm run build`, then test with Pro active and inactive (`docs/ai/testing.md`).
- Release Free before Pro when Pro depends on new Free code. Keep Pro's
  `$require_free_version` at 3.2.0 (it controls compatibility mode, not the settings API).
- Test the version combinations in `docs/ai/codebase-internals.md` (version-aware loading).
- Review `docs/ai/` against the code.
- Exclude `AGENTS.md`, `CLAUDE.md`, `docs/`, `node_modules/`, `package*.json`,
  `webpack.config.js` and `.scss` sources from the WordPress.org SVN release.
