=== Leo Product Recommendations: Upsell & Cross-sell Popup for WooCommerce ===
Contributors: webhasan, masum007
Tags: product recommendations, upsell, cross-sell, add to cart popup, related products
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 3.6.0
WC requires at least: 7.0
WC tested up to: 11.2.1
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Show upsells and cross-sells in a popup right after add to cart. Shoppers add them in one click, so more orders end up with a second item.

== Description ==
[youtube https://youtu.be/c20SsY1zsf8]

The best time to suggest a second product is right after a shopper adds the first one. They have already decided to buy. A phone case, a belt, a refill pack: one good suggestion at that moment is often enough.

Leo Product Recommendations shows that suggestion. When a product goes into the cart, a popup confirms it and shows the products you want to recommend. The shopper adds one with a single click and keeps shopping or goes to checkout. No new page, no redirect.

Install it, choose what to recommend, and it works on your shop, category and product pages.

= What shoppers see =

The popup shows what was just added, the cart subtotal, and a row of products that go with it. Each product has its own Add button, and products with options (size, color) can be chosen right there. Checkout and View cart are one tap away. On phones it slides up from the bottom of the screen.

It draws its own product cards, so it looks clean in any theme. If you prefer the older layout, you can switch back to the classic design in the settings.

= Three ways to choose what to recommend =

**1. The Upsells and Cross-sells you already have.** If you set them in WooCommerce (Product data > Linked Products), the popup uses them. Nothing else to set up.

**2. Products you pick yourself.** Open a product's Recommendations tab, pick the products to suggest and drag them into order. Best for accessories and things you know sell together.

**3. One rule for the whole store.** For example "4 best sellers from the same category" or "newest products tagged summer". Filter by categories, tags and sale items, and sort by best selling, rating, date, price or random. Running a sale? Campaign Mode shows your campaign products on every product until you turn it off.

Products that are hidden, out of stock or already in the cart are never suggested.

= Track the sales it brings in =

If your site uses Google Tag Manager, the popup sends GA4 ecommerce events to the dataLayer: `lpr_popup_view_item_list`, `lpr_popup_select_item` and `lpr_popup_add_to_cart`. Each event carries the product data and the product that opened the popup, so you can report what the recommendations sell. Your normal `add_to_cart` tracking keeps working.

Prefer a ready report? Pro has a built-in analytics dashboard.

= Works with your store =

* Simple, variable and grouped products
* Shop, category and product pages, WooCommerce blocks and shortcodes, block themes
* Bundles, subscriptions and other product types with their own add to cart (the popup opens after the page reloads)
* Popular themes such as Astra, Storefront, Kadence, OceanWP, Blocksy and the Twenty themes
* Elementor and other page builders
* HPOS and the block cart and checkout
* Keyboard and screen reader friendly, right-to-left languages, translation ready

The recommended products load only after something is added to the cart, so your pages don't get heavier.

= Free features =

* Popup after add to cart, modern or classic design
* Uses your WooCommerce Upsells and Cross-sells
* Recommendations you pick for each product
* A store-wide rule, with Campaign Mode
* One-click add to cart in the popup, products with options included
* Products per row for desktop, tablet and mobile
* Heading with the added product's name, or your own heading and text
* Limit how often each visitor sees the popup
* GA4 and Google Tag Manager dataLayer events
* Template overrides and hooks for developers

= Pro features =

[Leo Product Recommendations Pro](https://leocoder.com/leo-product-recommendations-for-woocommerce/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=upgrade&utm_content=readme) shows recommendations in more places, picks them from your own sales, and shows you the money they bring in:

* **Smart picks.** Learns which products your customers buy together from your own orders. New products and new stores get similar products until there is enough data.
* **More places.** "Frequently bought together" on the product page, a slide-in cart drawer, "Complete your order" on the cart page, a last-chance offer at checkout and suggestions on the thank-you page.
* **Discounts.** A small saving for products bought together, for add-ons from the popup or cart drawer, and for the checkout offer. Percentage or fixed amount, with a bigger saving for 2 or 3+ products. Taken off in the cart automatically, no coupon code needed.
* **Free shipping bar.** "Add $12.00 more to get free shipping" with products that cost about the missing amount, added in one tap.
* **Analytics dashboard.** Revenue, orders and adds to cart from recommendations, the best product pairs, and average order value with and without a recommended product. Data stays in your own database.
* **Rules per product,** slider layout, popup size, your own colors and button options.

[See Pro features and pricing](https://leocoder.com/leo-product-recommendations-for-woocommerce/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=upgrade&utm_content=readme-cta)

= Demo and docs =

* [See it on a demo store](https://www.leocoder.com/demo/lc-product-recommendations/product-category/clothing/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=demo&utm_content=readme)
* [Documentation](https://leocoder.com/docs/woocommerce-product-recommendations/add-recommended-products/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=docs&utm_content=readme)

[youtube https://youtu.be/M5i8FDHge2s]

== Privacy ==

The plugin does not collect any data by itself. If you choose to send feedback when you deactivate it, only the reason you pick, any comment you write and the plugin version are sent to LeoCoder. No site URL or other site details are sent. You can skip the feedback form.

The dataLayer events stay in the visitor's browser, in a dataLayer your site already has. The plugin does not send them anywhere.

== Installation ==

1. Go to **Plugins > Add New**, search for **Leo Product Recommendations**, click **Install Now**, then **Activate**.
2. Open **LPR Settings > Settings**. The **What to Recommend** tab is where you set what the popup shows.
3. To pick products for one product, edit it and open its **Recommendations** tab.

Requires WordPress 6.6+, WooCommerce 7.0+ and PHP 7.4+.

On activation the plugin turns on WooCommerce's "Enable AJAX add to cart buttons on archives" and turns off "Redirect to the cart page after successful addition", so the popup can open on shop and category pages.

== Frequently Asked Questions ==

= Is the free version really free? =

Yes. The popup, your own picks, Upsells and Cross-sells, the store-wide rule and the GA4 events are free, with no limit on products or popups.

= Will it slow down my store? =

No. It adds two small scripts and one stylesheet. Recommended products are loaded only after a shopper adds something to the cart.

= Does it work with block themes like Twenty Twenty-Five? =

Yes. The popup opens after add to cart from block product grids, the shop page and the product page.

= Does it work with variable and grouped products? =

Yes, both for the product being added and for the products in the popup. Shoppers choose options inside the popup.

= Does it use the Upsells and Cross-sells I already set? =

Yes. Products with nothing in their own Recommendations tab show their Upsells first, then their Cross-sells. You can turn each one off in LPR Settings > What to Recommend.

= I updated and still see the old popup. Why? =

Stores that used the plugin before 3.6.0 keep the classic design so nothing changes without you knowing. To use the new design, go to LPR Settings > Popup > Popup Design and choose Modern.

= Can I stop the popup from showing too often? =

Yes. Show it every time, or limit it to a number of times per visitor.

= Can I track sales from the recommendations in GA4? =

Yes. With Google Tag Manager, create GA4 event tags for `lpr_popup_view_item_list`, `lpr_popup_select_item` and `lpr_popup_add_to_cart`. They use the GA4 ecommerce format.

= How do I translate it? =

Use a plugin such as Loco Translate. [Translation guide](https://leocoder.com/docs/woocommerce-product-recommendations/translation/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=docs&utm_content=readme)

= Can I change the popup templates? =

Yes. Copy a file from the plugin's `templates` folder to `your-theme/lpr/templates/` and edit it there. This needs some PHP knowledge.

== Screenshots ==

1. The popup after add to cart, with one-click add for each recommended product
2. On phones the popup slides up from the bottom
3. Choose recommendations for one product in its Recommendations tab
4. What to Recommend: Upsells and Cross-sells, and one rule for the whole store
5. Popup settings with a live preview
6. Pro: "Frequently bought together" on the product page, with a bundle saving
7. Pro: cart drawer with a free shipping bar
8. Pro: analytics dashboard

== Upgrade Notice ==

= 3.6.0 =
New modern popup design. Existing stores keep the classic design until you switch in LPR Settings > Popup > Popup Design.

== Changelog ==

= 3.6.0 =
* New: Modern popup design. After add to cart it shows what was added and the cart subtotal, a row of products that go with it (with arrows when there are more), and clear Checkout and View cart buttons. It draws its own product cards, so it looks the same in every theme. On phones it slides up from the bottom with a swipeable row. Products added from it say "Added" and the subtotal updates without closing it. Esc closes it and keyboard focus stays inside. New installs use it; existing stores keep the classic design until they switch in Settings > Popup > Popup Design. Theme template: yourtheme/lpr/templates/modern-popup.php.
* Improvement: The default popup heading is now "Goes well with %title%".
* New: More Places shows the Pro free shipping bar, drawn with your products.
* New: Discounts tab shows the Pro discounts (bundle saving, add-on saving and checkout offer discount, as a percentage or fixed amount, with a bigger saving for more products) with your own products.
* Improvement: The Default Recommendations tab is now called What to Recommend and comes first, with the store-wide rule at the top.
* Improvement: The What to Recommend tab says these settings are for all products, with a link to set one product differently in its Recommendations tab. That tab links back.
* New: Store-wide rule shows "Smart picks" (Pro): recommendations learned from your own orders, with a preview of products your customers already buy together.
* Developer: `lpr_recommended_products_id`, `lpr_smart_available` and `lpr_smart_product_ids` filters.
* Improvement: Clearer settings tabs, most useful first: What to Recommend, Popup, More Places, Discounts and Get Started.
* Change: The Custom CSS field is removed. CSS you already saved there keeps working on the store. Add new CSS in Appearance > Customize > Additional CSS.
* New: Settings page redesign with a live preview of the popup next to the settings, a products-per-row stepper, and More options for the rest.
* New: Get Started tab with the setup steps, the setup video and help links.
* Improvement: Settings keep numbers inside their allowed range and ignore unknown choices when saving.
* Developer: New `lpr_before_popup` front-end event (cancelable) and `lpr_ajax_add_to_cart_always` filter, so other places can show the recommendations. Used by the Pro cart drawer.
* Fix: Adding a product from inside the popup now always shows the "added to cart" notice, also after the popup limit is reached.
* Fix: The popup now also opens after adding a product from block product grids (Product Collection, Product Button) in block themes like Twenty Twenty-Five.
* Improvement: Better keyboard and screen reader use. Both popup designs keep focus inside while open, Esc closes them, the slider arrows are real buttons, and focus outlines are visible. In the product's Recommendations tab, products can be added and removed with the keyboard.
* Improvement: Respects the "reduce motion" setting on phones and computers, and touch targets are bigger on touch screens.
* Improvement: Buttons in the popup keep readable text with any Button Color you pick.
* Fix: A quick double click on Add to cart no longer adds the product twice.
* Fix: The product panel script no longer loads on admin pages where it isn't used.
* Improvement: Clearer, shorter wording for settings titles and descriptions.

Older versions are listed in changelog.txt.
