# Local environment and testing

## Local environment

- Site: http://localhost/woocommerce (admin: http://localhost/woocommerce/wp-admin)
- Path on disk (macOS): `/Users/webhasan/Sites/woocommerce/wp-content/plugins/`
- Stack seen in October 2026: WordPress 7.1.x, WooCommerce 11.1.x, theme Astra with
  Elementor. Also active: CartFlows, GTM4WP (no container ID yet), Variation Swatches.
- Currency: BDT (৳). Categories: Accessories, Men, Women, Uncategorized.
- `/shop` is an Elementor page without a product grid. Use a category archive instead,
  e.g. http://localhost/woocommerce/product-category/men/

### Useful test products

| ID | Product | Type |
|---|---|---|
| 324 | Dark Gray Jeans | simple, Men |
| 307 | Black Hoodie | simple, Men |
| 362 | DNK Yellow Shoes | simple, on sale |
| 375 | DNK Black Shoes | variable |
| 211 | Gray Pattern Tshirt | variable |
| 169 | Boho Bangle Bracelet | variable, Accessories |

## Testing rules

Owner's own rules: _to be added by Hasan._

Default safety rules until then:

- Note the current settings before changing them, and restore them after the test.
- Do not save product edits unless the test needs it; restore them after.
- Test cart items are fine. Empty the cart at the start of a test that depends on it,
  because cart items are excluded from recommendations.
- Never change WooCommerce core settings, payments, or other plugins without asking.
- Clear `localStorage` key `lpr-popup-count` (or use a private window) when testing the
  Popup Show limit.

## End-to-end checklist

Run each with **Pro active** and **Pro inactive**.

1. Archive page: AJAX add to cart opens the popup.
2. Single product page: form submit is AJAX, popup opens, no page reload.
3. Popup: heading, `%title%` and `[item, items]` render correctly.
4. Add a simple product from the popup: flash bar, cart count updates, no second popup.
5. Add a variable product from the popup: button disabled until a variation is chosen,
   price updates, item added.
6. Add a grouped product from the popup.
7. Products already in the cart do not appear in the next popup.
8. Manual picks on a product override Global when Skip Manual Selection is off.
9. Skip Manual Selection on: Global is used for every product.
10. Global off and no product picks: no popup, product still added.
11. Popup Show limit (e.g. 2 times): popup stops after 2; single product page reloads normally.
12. Responsive: desktop, tablet (991px and below), mobile (767px and below).
13. Pro only: slider, popup size, button visibility, colors.
14. Browser console has no errors; network calls to `admin-ajax.php` return 200.
15. Product with its own add to cart handler (bundle-type): page posts normally, product
    added once, popup opens after the reload and not again on the next page view.
16. Form with a selected file: posts normally, popup after reload. Checkbox group: all
    checked values reach the server.
17. A named non add-to-cart submit button (Buy now) is not intercepted.
18. `?add-to-cart=ID` link: popup after load. Cart/checkout pages never show the
    after-reload popup.
19. With GTM4WP active: adding from the popup pushes `add_to_cart` (simple and
    variable, variable also `view_item` on option choice). Check `dataLayer`.
20. Any change to how the popup HTML is loaded: compare the popup markup on several
    themes (Astra, Storefront, Kadence, OceanWP, Blocksy, a block theme). Titles and
    prices must still show on every card.
21. Blocksy with its AJAX add to cart on (default): product page add sends no
    `lc_ajax_add_to_cart`, adds one item, opens the popup once, and the next page shows
    no popup. One add to cart button per popup card.
22. Woodmart / Flatsome need a licensed copy to test for real. Without one, simulate
    Woodmart by setting `window.woodmart_settings = {ajax_add_to_cart: '1'}` on a product
    page: the plugin must not call `lc_ajax_add_to_cart`.
23. dataLayer: with GTM4WP (or any `window.dataLayer`), opening the popup pushes
    `lpr_popup_view_item_list`, clicking a product link `lpr_popup_select_item`, adding
    from the popup `lpr_popup_add_to_cart` (after GTM4WP's own `add_to_cart`), each after
    `{ecommerce: null}`. Variable adds carry the variation ID, variant and price. With
    `window.dataLayer` undefined nothing is pushed and nothing is created.
24. Dynamic selection with "Low Price" / "High Price": the popup order matches the shop's
    "Sort by price" for the same category, including variable and on-sale products.
25. More Places, cart page (classic and block cart): recommendations show, products in the
    cart are left out, adding one does not open the popup, the block cart updates and the
    card fades out. Grid and Slider; on a phone the add button is visible.
26. Pro: Frequently bought together on a simple and a variable product (button waits for
    options, price follows the variation), "Add all" adds every ticked item once.
27. Pro: checkout offer (classic and block checkout) ticks and unticks, totals update,
    and only products under the price limit are offered.
28. Pro: thank-you page shows recommendations for the ordered products.
29. More Places tab: preview follows the card being edited; a place that is off shows only
    its switch.
30. Pro Smart picks: status card shows the order count; "Rebuild now" updates it; firing
    several rebuilds at once gives the same counts as one (build lock).
31. Pro Smart picks: a product bought together with another ranks it first in the popup,
    drawer and FBT; products already in the cart are skipped; a product with no pairs gets
    similar products.
32. Pro Fill empty spots: a product with fewer hand-picked products than one row is topped
    up; a full list is left alone.
33. Pro product panel: "Smart" option shows its note, the picks shown now with reasons, and
    saves `smart-selection`.
34. Pro Smart Options: personalize (a viewed product moves to the front of the popup),
    skip bought (logged-in customer), lower-priced first.
35. Pro Analytics: "Smart picks vs your own picks" card fills after a popup view and an add.
Tip: a temporary helper plugin that maps a product to a custom handler type
(`woocommerce_add_to_cart_handler` filter) and prints test fields is the quickest way to
cover 15 to 16. Remove it afterwards.

## Tools

- AI agents with browser access can run this checklist through Chrome on the local site.
- For tracking work, watch `window.dataLayer` and the `added_to_cart` event.
