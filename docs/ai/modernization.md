# Modernization inventory

Inputs for the planned package upgrade. Checked against the npm registry on 2026-10-18.
Nothing here is decided yet; "Options" are candidates to evaluate.

## npm audit

`npm audit` reports ~38 issues, all inside `@wordpress/scripts` dev tooling (stylelint,
eslint, webpack-dev-server, cssnano, markdownlint). `npm audit --omit=dev` is clean: nothing
ships to sites. `npm audit fix` changes nothing; `npm audit fix --force` would downgrade
`@wordpress/scripts`, so never run it. `braces` (root of ~12 "high") has no patched
release. Re-check after each `@wordpress/scripts` update.

## Build tooling

| Item | Free | Pro | Latest | Notes |
|---|---|---|---|---|
| `@wordpress/scripts` | **36.1** (was 6.1) | **36.1** (was 6.2) | 36.1.0 | Done in Free 3.3.1 / Pro 3.2.1. Needs Node 22.22+ / 24.15+. |
| `sass` | **1.105** | **1.105** | 1.105 | Done. `darken()` replaced by a fixed color. |
| Lockfiles | v3 | v3 | | Regenerated. |
| `*.asset.php` | committed, used | committed, used | | `script_asset()` reads dependencies and version. |

## Product panel (React)

| Package | Used for | Installed | Latest | Status | Options |
|---|---|---|---|---|---|
| `react-reorder` | Drag to sort selected products | removed | | **Done:** `@dnd-kit/core` 6 + `@dnd-kit/sortable` 10 (mouse, touch, keyboard) | |
| `react-debounce-input` | Search box debounce | removed | | **Done:** local `components/DebounceInput.js` | |
| `react-select` (Pro) | Category/tag multi-select | **5.10.2** | 5.10.2 | **Done** (bundles emotion, panel is ~116 KB) | Later: `FormTokenField` to drop it |
| `classnames` | CSS classes | 2.2.6 | 2.5.1 | Fine | `clsx` or keep |
| `@wordpress/components` `TreeSelect` | Category filter | external (`wp.components`) | | Fine | Keep |
| `lodash` (`groupBy` in `tree.js`) | Category tree | removed | | **Done:** plain JS `groupBy` | |
| `wp.element.render` | Mounting | `createRoot` | | **Done** | |
| jQuery `$.ajax` | Panel requests | | | Works | `@wordpress/api-fetch` + REST routes |

React compatibility: the panel uses WordPress's React (18.3.1 on WordPress 7.1) and no
longer relies on APIs removed in React 19 (`findDOMNode`, legacy render, legacy
lifecycles). Done in Free 3.3.1 / Pro 3.2.1.

## Admin settings page (jQuery plugins, vendored)

| Library | Vendored version | Status | Options |
|---|---|---|---|
| Select2 | 4.0.3 (2016) | Old, known fixes since | WooCommerce's bundled `selectWoo` / `wc-enhanced-select`, or React controls |
| Spectrum color picker | 1.8.1 | Unmaintained | `wp-color-picker` (no alpha without an add-on), `spectrum-colorpicker2`, or `@wordpress/components` `ColorPicker` |
| Lity lightbox | 2.4.1 (2020, last release) | Only used for `help` links, and no field sets `help` | Remove |
| CodeMirror (`wp.codeEditor`) | WordPress core | Fine | Keep |
| TinyMCE (`wp.editor`) | WordPress core | Classic editor stack | Keep for now |

Bigger option: rebuild the settings page as a React app on `@wordpress/components`
with a REST endpoint and one shared schema, which would also remove the duplicated Free
and Pro PHP frameworks.

## Front end (jQuery)

| Item | Status | Options |
|---|---|---|
| Owl Carousel 2.3.4 (Pro) | Unmaintained since 2018, jQuery-based | Swiper 14, Embla 8, Splide 4, or CSS scroll-snap |
| jQuery modal (`$.fn.lprModal`) | Own small code, works | Native `<dialog>` element |
| `ajax-add-to-cart.js` + `class-ajax-add-to-cart.php` | Copy of WooCommerce core logic, intercepts every `form.cart` | WooCommerce Store API (`/wc/store/v1/cart/add-item`) or limit interception to supported product types |
| jQuery itself | Required by WooCommerce classic templates anyway | Keep while WooCommerce classic add-to-cart events are the trigger |
| localStorage popup counter | Works | Keep |

## Duplication to remove

| Duplicated thing | Free | Pro | Notes |
|---|---|---|---|
| Settings framework | `class-settings-page.php`, `settings-fields-type.php` | **Done (3.3.0/3.2.0):** removed from Pro | Shared via `lpr_settings_pages` |
| Settings schema | `default_settings_pages()` | Full page set returned through the filter | Next: Pro could add only its own fields instead of the whole schema |
| `get_setting()` and helpers | Cached field map | Delegates to Free (own copy kept for compatibility mode and activation defaults) | Remove Pro's copy once Free 3.2.0 support ends |
| Product panel | `panel.js` + components | Forked copies | One panel in Free with an extension point for Dynamic Selection |
| Templates | 2 files | 2 forked files | One template set, layout via data |

## Suggested order (proposal)

1. ~~Bump `@wordpress/scripts` and `sass`; production build; use `*.asset.php`.~~ Done (3.3.1 / 3.2.1).
2. ~~Replace `react-reorder`, move to `createRoot`, drop `react-debounce-input` and lodash.~~
   Done (3.3.1 / 3.2.1).
3. ~~Merge the Free and Pro settings framework into Free; cache the schema.~~ Done.
   Release rules: Free 3.3.0 ships before Pro 3.2.0; keep Pro `$require_free_version`
   at 3.2.0 (see `codebase-internals.md`, version-aware loading).
4. Replace Select2, Spectrum, Lity on the settings page (or rebuild it in React).
5. Replace Owl Carousel; review the add-to-cart interception.
6. Test every step with Pro active and inactive (`testing.md`).
