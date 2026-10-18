# Modernization inventory

Inputs for the planned package upgrade. Checked against the npm registry on 2026-10-18.
Nothing here is decided yet; "Options" are candidates to evaluate.

## Build tooling

| Item | Free | Pro | Latest | Notes |
|---|---|---|---|---|
| `@wordpress/scripts` | 6.1.1 | 6.2.0 | 36.1.0 | 30 major versions behind. Webpack 4 era. Free build needs `--openssl-legacy-provider` on modern Node. Latest needs Node 22.22+ / 24.15+. |
| `sass` | ^1.26 | ^1.30 | 1.105 | Fine to bump. Old `@import` style will need `@use` eventually. |
| Lockfiles | v2 | v2 | | Regenerate after the bump. |
| `*.asset.php` | ignored | partly committed | | Use them for script dependencies and versions instead of hard-coded arrays. |

## Product panel (React)

| Package | Used for | Installed | Latest | Status | Options |
|---|---|---|---|---|---|
| `react-reorder` | Drag to sort selected products | 3.0.0-alpha.7 | same | Abandoned alpha, needs `create-react-class`, uses `findDOMNode` and legacy lifecycles | `@dnd-kit/sortable` (10.x), or `@wordpress/components` drag helpers |
| `react-debounce-input` | Search box debounce | 3.2.x | 3.3.0 (2022) | Peer dep React 15/16 only | `useDebounce` from `@wordpress/compose`, or a small custom hook |
| `react-select` (Pro) | Category/tag multi-select | 3.1.1 | 5.10.2 | Peer dep React 16 only, bundles emotion | `FormTokenField` / `ComboboxControl` from `@wordpress/components`, or react-select 5 |
| `classnames` | CSS classes | 2.2.6 | 2.5.1 | Fine | `clsx` or keep |
| `@wordpress/components` `TreeSelect` | Category filter | external (`wp.components`) | | Fine | Keep |
| `lodash` (`groupBy` in `tree.js`) | Category tree | WP global | | WordPress is moving away from lodash | Plain JS |
| `wp.element.render` | Mounting | | | Legacy React render API | `createRoot` from `@wordpress/element` |
| jQuery `$.ajax` | Panel requests | | | Works | `@wordpress/api-fetch` + REST routes |

React compatibility: `findDOMNode` (pulled in by `react-reorder`) and the legacy render
API are removed in React 19. The panel will break whenever WordPress ships React 19, so
`react-reorder` and `wp.element.render` are the first things to replace. Check which
React version the target WordPress versions bundle before planning.

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

1. Bump `@wordpress/scripts` and `sass`; get a clean production build; use `*.asset.php`.
2. Replace `react-reorder`, `react-debounce-input`, `react-select`; move to `createRoot`.
3. ~~Merge the Free and Pro settings framework into Free; cache the schema.~~ Done.
   Release rules: Free 3.3.0 ships before Pro 3.2.0; keep Pro `$require_free_version`
   at 3.2.0 (see `codebase-internals.md`, version-aware loading).
4. Replace Select2, Spectrum, Lity on the settings page (or rebuild it in React).
5. Replace Owl Carousel; review the add-to-cart interception.
6. Test every step with Pro active and inactive (`testing.md`).
