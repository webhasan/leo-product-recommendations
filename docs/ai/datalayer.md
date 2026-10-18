# dataLayer events (since 3.4.0)

Free feature, on by default, pushed only when `window.dataLayer` already exists (the plugin
never creates it). Filter `lpr_popup_datalayer` (bool) turns it off. Owner decision
(Oct 2026): event names `lpr_popup_*`, mirroring GA4 names; the standard `add_to_cart` of
GTM4WP and other plugins is left unchanged.

| Event | When |
|---|---|
| `lpr_popup_view_item_list` | Each time the popup opens (grid and slider, slider clones ignored) |
| `lpr_popup_select_item` | Click on a product link (image/title) in a popup card, not on buttons |
| `lpr_popup_add_to_cart` | `added_to_cart` fired for a button inside `#lpr-modal` |

Each push is preceded by `{ecommerce: null}`. Payload:

```js
{
  event: 'lpr_popup_add_to_cart',
  ecommerce: {
    currency: 'BDT', value: 150,            // value/currency only on add_to_cart
    item_list_id: 'lpr_popup',
    item_list_name: 'Product Recommendations Popup',
    items: [{ item_id: '242', item_name: 'Slim Fit Blue Jeans', price: 150, quantity: 1,
              index: 0, item_category: 'Women', item_category2: '...', item_brand: '...',
              item_variant: 'blue',          // variable products
              item_list_id: 'lpr_popup', item_list_name: 'Product Recommendations Popup' }]
  },
  lpr: { source_product_id: '247', source_product_name: 'Basic Gray Jeans',
         recommendation_type: 'manual' }    // manual | dynamic | global
}
```

## How it works

- `lpr_get_popup_data()` appends `<script type="application/json" class="lpr-datalayer-data">`
  after the popup template (`print_datalayer_data()`): currency, list id/name, `id_type`,
  `source`, and `items` keyed by product ID (`get_datalayer_item()`; grouped products also
  carry `children`). Works for Free and Pro templates (both use `.single-lpr` cards).
- `modal.js` reads it (`readDatalayerData()`), maps cards to items with
  `getCardProductId()`, and builds the events. Variable adds take the variation from the
  form's `data-product_variations` (variation ID or SKU, `display_price`, attributes).
  Grouped adds push one item per child with a quantity. Quantity comes from the form or
  the button's `data-quantity`.
- `index` is the 0-based card position (GA4 example convention).
- `price` is `wc_get_price_to_display()` (respects the store's tax display setting).

## Filters

- `lpr_popup_datalayer` (true): turn the events off.
- `lpr_datalayer_item` (`$item, $product`): change any item field.
- `lpr_datalayer_item_id_type` ('id'): `'sku'` uses the SKU as `item_id` (variations too).
- `lpr_datalayer_item_list_name` ('Product Recommendations Popup').

## Not covered

- Adds from the popup that fall back to a normal form post (bundles etc.) reload the page,
  so no `lpr_popup_add_to_cart` is pushed for them.
- `select_item` is pushed on click; whether GTM tags finish before the page navigates
  depends on the container (use GTM's "wait for tags" or a beacon transport).
