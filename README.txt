=== Leo Product Recommendations: Upsell & Cross-sell Popup for WooCommerce ===
Contributors: webhasan, masum007
Tags: product recommendations, upsell, cross-sell, related products, add to cart popup
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 3.5.0
WC requires at least: 7.0
WC tested up to: 11.2.1
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Sell more per order. Show upsells and cross-sells in a popup right after add to cart, and let shoppers add them in one click.

== Description ==
[youtube https://youtu.be/c20SsY1zsf8]

A shopper who has just added something to the cart is the easiest person to sell to. They have decided to buy, the card is out, and one good suggestion is often enough: a case for the phone, a belt for the jeans, batteries for the toy.

Leo Product Recommendations shows that suggestion at exactly the right moment. As soon as a product goes into the cart, a popup confirms it and shows the products you want to recommend. The shopper adds one with a single click, without leaving the page, and carries on shopping or goes to checkout.

No extra page, no redirect, no copy and paste of shortcodes. Install it, choose what to recommend, and the popup works on your shop, category and product pages.

= Why store owners use it =

* **Bigger orders without more traffic.** You already paid to get the visitor. The popup turns one item into two or three.
* **Suggestions that make sense.** You decide what goes with what, product by product, or set one rule for the whole store.
* **Nothing to set up if you use Upsells and Cross-sells.** The popup can show the ones you already added in WooCommerce.
* **Shoppers stay in the flow.** They add recommended products, including variable products with options, right inside the popup.
* **You can measure it.** GA4-ready dataLayer events show how much the recommendations sell.

= How it works =

1. A shopper adds a product to the cart, from the shop page, a category page or the product page.
2. A popup confirms the add and shows your recommended products.
3. They add a recommendation with one click or carry on shopping. Products already in the cart are never suggested again.

= Three ways to choose what to recommend =

**1. Your WooCommerce Upsells and Cross-sells.** If you already set them under Product data > Linked Products, the popup uses them. Upsells come first, then Cross-sells. You can turn each one on or off.

**2. Hand-picked products.** On any product, open the Recommendations tab and pick what to suggest, then drag to set the order. Best for accessories and items you know sell together.

**3. A store-wide rule.** One rule covers every product you haven't set up yourself, for example "4 best sellers from the same category" or "newest products tagged summer". Filter by categories, tags and on-sale products, and sort by best selling, top rated, newest, oldest, price or random.

Running a sale? Turn on Campaign Mode and every product shows your campaign rule until you turn it off.

Hidden and out of stock products are skipped automatically.

= Track the revenue it brings in =

If your site uses Google Tag Manager (or anything else that sets up a dataLayer), the popup pushes GA4 ecommerce events: `lpr_popup_view_item_list`, `lpr_popup_select_item` and `lpr_popup_add_to_cart`. Each one carries the product data and the product that opened the popup, so you can report exactly what the recommendations sell. Your normal `add_to_cart` tracking keeps working, and GTM4WP picks up products added from the popup.

Want the numbers without setting up GA4? The Pro version has a built-in analytics dashboard.

= Works with your store =

* Simple, variable and grouped products, with options chosen right in the popup
* Shop, category and single product pages, WooCommerce blocks and shortcodes
* Product bundles, subscriptions and other products with their own add to cart handling (the page reloads normally and the popup opens after it)
* Popular themes such as Astra, Storefront, Kadence, OceanWP, Blocksy and the default Twenty themes
* Page builders such as Elementor
* HPOS and the block cart and checkout
* Translation ready

The popup content loads only after a product is added to the cart, so it doesn't slow down your pages.

= Free features =

* Recommendation popup after add to cart
* Uses your WooCommerce Upsells and Cross-sells
* Hand-picked recommendations per product
* Store-wide rule-based recommendations, with Campaign Mode
* One-click add to cart inside the popup, variable products included
* Grid layout with items per row for desktop, tablet and mobile
* Popup heading with the added product's name, or your own heading and description
* Limit how often each visitor sees the popup
* GA4 / Google Tag Manager dataLayer events
* Custom CSS
* Template overrides from your theme, plus action and filter hooks for developers

= Pro features =

[Leo Product Recommendations Pro](https://leocoder.com/leo-product-recommendations-for-woocommerce/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=upgrade&utm_content=readme) is for stores that want to see the results and fine-tune the popup:

* **Analytics dashboard.** Revenue, orders and adds to cart that came from the popup, a simple funnel, the product pairs that sell best together, and average order value with vs without a recommendation. Data stays in your own database.
* **Rules per product.** Give any product its own rule instead of the store-wide one, for example "on-sale products from the Accessories category".
* **Slider layout** with its own items per row, autoplay and navigation settings
* **Popup size** for desktop, tablet and mobile
* **Your brand colors** for the popup, buttons and products
* **Show or hide** the Continue Shopping, Checkout and close buttons

[See Pro features and pricing](https://leocoder.com/leo-product-recommendations-for-woocommerce/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=upgrade&utm_content=readme-cta)

= Demos =

See the popup on a demo store: [smartphones](https://www.leocoder.com/demo/lc-product-recommendations/product-category/electronics/mobile/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=demo&utm_content=readme), [electronics](https://www.leocoder.com/demo/lc-product-recommendations/product-category/electronics/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=demo&utm_content=readme), [laptops](https://www.leocoder.com/demo/lc-product-recommendations/product-category/electronics/laptop/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=demo&utm_content=readme), [shoes](https://www.leocoder.com/demo/lc-product-recommendations/product-category/clothing/shoes/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=demo&utm_content=readme), [food](https://www.leocoder.com/demo/lc-product-recommendations/product-category/food/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=demo&utm_content=readme), [clothing](https://www.leocoder.com/demo/lc-product-recommendations/product-category/clothing/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=demo&utm_content=readme), [jewellery](https://www.leocoder.com/demo/lc-product-recommendations/product-category/jewellery/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=demo&utm_content=readme), [leather](https://www.leocoder.com/demo/lc-product-recommendations/product-category/leather/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=demo&utm_content=readme), [software](https://www.leocoder.com/demo/lc-product-recommendations/product-category/software/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=demo&utm_content=readme), [toys](https://www.leocoder.com/demo/lc-product-recommendations/product-category/toys/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=demo&utm_content=readme), [fashion](https://www.leocoder.com/demo/lc-product-recommendations/product-category/fashion/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=demo&utm_content=readme) and [sports](https://www.leocoder.com/demo/lc-product-recommendations/product-category/sports/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=demo&utm_content=readme).

= Setup video =

[youtube https://youtu.be/M5i8FDHge2s]

[Read the documentation](https://leocoder.com/docs/woocommerce-product-recommendations/add-recommended-products/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=docs&utm_content=readme)

[youtube https://youtu.be/6ojRz6bK9Xo]

[youtube https://youtu.be/3t--SH1MjDw]

== Privacy ==

The plugin does not collect any data by itself. If you choose to send feedback when you deactivate it, only the reason you pick, any comment you write and the plugin version are sent to LeoCoder. No site URL or other site details are sent. You can skip the feedback form.

The dataLayer events are pushed only inside the visitor's browser, to a dataLayer your site already has. The plugin does not send them anywhere.

== Installation ==

= Requirements =

* WordPress 6.6 or later
* WooCommerce 7.0 or later
* PHP 7.4 or later

= Install from your dashboard =

1. Go to **Plugins > Add New** and search for **Leo Product Recommendations**.
2. Click **Install Now**, then **Activate**.
3. Open **LPR Settings > Settings** to choose your default recommendations, or edit a product and open its **Recommendations** tab to pick products.

= Install manually =

1. Download the plugin zip.
2. Go to **Plugins > Add New > Upload Plugin**, choose the zip and click **Install Now**.
3. Click **Activate Plugin**.

On activation the plugin turns on WooCommerce's "Enable AJAX add to cart buttons on archives" setting and turns off "Redirect to the cart page after successful addition", so the popup can open on shop and category pages.

== Frequently Asked Questions ==

= Is the free version really free? =

Yes. The popup, hand-picked products, Upsells and Cross-sells, the store-wide rule and the GA4 events are all free, with no limit on products or popups. Pro adds analytics, rules per product and more design options.

= Will it slow down my store? =

No. The plugin adds two small scripts and one stylesheet. The recommended products are fetched with AJAX only after a shopper adds something to the cart, so your pages don't load any extra product data.

= Does it work on the shop page and the product page? =

Yes. The popup opens after add to cart on shop and category pages, on single product pages, and in WooCommerce product blocks and shortcodes.

= Does it work with variable and grouped products? =

Yes, both for the product being added and for the products shown in the popup. Customers choose options inside the popup.

= What if a product needs a page reload to add to cart? =

Product types with their own add to cart handling, such as bundles, subscriptions or products with file uploads, are added the normal WooCommerce way. The popup then opens after the page loads.

= Can I track sales from the recommendations in GA4? =

Yes. When your site has a dataLayer (for example from Google Tag Manager), the popup pushes `lpr_popup_view_item_list`, `lpr_popup_select_item` and `lpr_popup_add_to_cart` in GA4 ecommerce format. Create GA4 event tags for them in GTM. Products added from the popup also send GTM4WP's normal `add_to_cart`.

= Does it use the Upsells and Cross-sells I already set in WooCommerce? =

Yes. Products with nothing set in their own Recommendations tab show their Upsells first, then their Cross-sells. Products without either use your store-wide rule. You can turn Upsells and Cross-sells on or off separately in LPR Settings > Default Recommendations.

= Can I stop the popup from showing too often? =

Yes. In the settings you can show it every time or limit it to a number of times per visitor.

= Does it work with Elementor and other page builders? =

Yes. It works with WooCommerce products shown by Elementor and other builders that use the standard WooCommerce add to cart buttons and product forms.

= How do I translate it? =

The plugin is translation ready. Use a plugin such as Loco Translate. [Translation guide](https://leocoder.com/docs/woocommerce-product-recommendations/translation/?utm_source=wordpress.org&utm_medium=plugin-page&utm_campaign=docs&utm_content=readme)

= Can I change the popup templates? =

Yes. Copy a file from the plugin's `templates` folder to `your-theme/lpr/templates/` and edit it there. The templates also have action and filter hooks. You need some PHP knowledge for this.

== Screenshots ==

1. Recommendation popup after add to cart
2. AJAX add to cart inside the popup
3. Popup heading with the added product's name
4. Picking recommended products on the product edit screen
5. Settings page
6. Popup layouts

== Changelog ==
= 3.5.0 =
* New: Use WooCommerce's own Upsells and Cross-sells (Product data > Linked Products) as recommendations. Products with nothing set in their Recommendations tab show their linked products first, and the store-wide rule covers the rest. On by default for new installs. Existing stores: turn it on in LPR Settings > Default Recommendations.
* New: The product's Recommendations tab says when the popup will use its linked products.
* New: LPR Settings > Analytics shows a preview of the Pro analytics dashboard. The free version does not track anything.
* Improvement: Clearer settings. The Global tab is now Default Recommendations, with numbered steps (linked products, then the store-wide rule). Skip Manual Selection is now called Campaign Mode. Your saved settings don't change.
* Improvement: Plugin renamed to Leo Product Recommendations: Upsell & Cross-sell Popup for WooCommerce. Settings and recommendations are not affected.
* Improvement: Documentation and support links in the plugin now go straight to leocoder.com.
* Improvement: The first item under LPR Settings is now called Settings, the same as with Pro.
* Improvement: The "Popup Show" setting description can now be translated.
* Fix: Headings with more than one [single, plural] pattern now show the right words, and spaces after the comma are trimmed.
* Developer: Filter `lpr_linked_product_ids`. Recommendation type `linked` in the dataLayer events.

= 3.4.0 =
* New: The popup also opens after a normal (page reload) add to cart, for example for product bundles, subscriptions, bookings, products with file uploads and add to cart links.
* New: GA4-ready dataLayer events for the popup: `lpr_popup_view_item_list`, `lpr_popup_select_item` and `lpr_popup_add_to_cart`, with product data, so you can measure the revenue the popup brings in. Pushed only when the site already has a dataLayer (for example Google Tag Manager). Your existing add_to_cart tracking is not changed.
* New: One settings framework shared with the Pro version. Settings are kept as they are.
* New: One-time backup of the plugin settings before the first save with this version.
* Improvement: Modern build tools (@wordpress/scripts 36). Admin scripts are smaller and load with automatic cache busting.
* Improvement: Product panel ready for React 19 (new drag and drop, keyboard sorting with space and arrow keys).
* Improvement: Settings are loaded once per request (faster pages).
* Fix: Product types with their own add to cart handler (bundles, composites, subscriptions...) showed "Something went wrong" on the product page. They now add to cart the normal WooCommerce way.
* Fix: Product options sent as checkbox groups or multi-selects kept only the last choice.
* Fix: "Buy now" buttons in the product form now work instead of opening the popup.
* Fix: GTM4WP did not record add to cart for products added from the popup. The popup now loads GTM4WP's product data for its products.
* Fix: Cart and check icons were missing in the popup, so the cart count badge overlapped the Checkout button.
* Fix: The popup grid showed one product fewer per row than set with themes that add a gap to product lists (for example Astra).
* Fix: Hidden and out-of-stock products are always excluded from dynamic recommendations.
* Fix: A PHP error in the popup when a hand-picked recommended product had been deleted.
* Fix: "Low Price" and "High Price" sorting in dynamic recommendations left out most variable products and ignored sale prices. They now sort by the price shoppers see, like the shop's own price sorting.
* Fix: Saving settings without Pro active no longer removes Pro settings.
* Fix: An empty or zero "items per row" value no longer causes a PHP error.
* Fix: No JavaScript error when a theme fires added_to_cart without a button (Flatsome mini cart).
* Fix (Blocksy): two add to cart requests on the product page when Blocksy's AJAX add to cart is on, the popup opening again on the next page, and two add to cart buttons on each popup product.
* Fix (Woodmart): with Woodmart's AJAX add to cart on, the product page sent two add to cart requests. The plugin now leaves those forms to Woodmart.
* Fix: The deactivation feedback form showed no radio buttons or text fields since 3.1.0, so feedback could not be sent.
* Security: The product panel requests now need a logged-in user who can edit products. They were also open to logged-out visitors (protected only by a nonce).
* Security: The popup only loads for published, visible products, so it can no longer show the title or image of a draft, private or non-product post.
* Security: Recommendations are saved only for products, only by users who can edit them, and not on autosaves or revisions.
* Security: The product title in the popup heading is escaped. Custom CSS is printed without tags (so it can't leave the style element) and selectors like `a > b` now work.
* Security: "Reset colors" now requires a valid nonce and only resets the color settings.
* Security: Deactivation feedback is sent with TLS certificate checks on and only by users who can manage plugins.
* Privacy: Deactivation feedback now sends only the reason, your comment and the plugin version. The site URL, theme, plugin list and server details are no longer sent.
* Developer: Filters `lpr_popup_datalayer`, `lpr_datalayer_item`, `lpr_datalayer_item_id_type` (`id` or `sku`) and `lpr_datalayer_item_list_name`.
* Developer: Filters `lpr_ajax_add_to_cart_supported`, `lpr_popup_after_reload`, `lpr_popup_after_reload_on_cart`, and the `lpr-no-ajax` form class to opt a form out of AJAX add to cart.
* Developer: Filter `lpr_theme_ajax_add_to_cart_selector` for themes that add the product form to the cart with their own AJAX call.
* Developer: Filter `lpr_popup_tracking_data` (on by default when GTM4WP is active).
* Compatibility: Tested with WordPress 7.1 and WooCommerce 11.2. Declares support for High-Performance Order Storage and the Cart & Checkout blocks.
* Requires WordPress 6.6 or later.

= 3.2.0 =
* Updated compatibility metadata for WordPress 7.1 and WooCommerce 11.1.1.

= 3.0.0 =
* Fix plugin compatibility issue

= 2.8.0 =
* Compatible with HPOS

= 2.7.0 =
* Bug Fix

= 2.6.4 =
* Bug Fix: Fix for Be theme

= 2.6.3 =
* Bug Fix: Fix for Blocksy theme

= 2.6.2 =
* Bug Fix: Fix when product data null

= 2.6.1 =
* Bug Fix: Fix when product data null

= 2.6.0 =
* Bug Fix: Bug for for pro version for popup limit.

= 2.5.0 =
* Feature Added: Limit to show recommendation popup (Every time, 1, 2, 3, 5 times)

= 2.4.0 =
* Feature Added: Modeal close icon

= 2.3.1 =
* Bug fix

= 2.3.0 =
* Exclude private, hidden, out-of-stock products from recommendation
* Template fix for Rey theme

= 2.2.0 =
* Fixing for OceanWP

= 2.0.2 =
* Bug fix

= 2.0.1 =
* Bug fix

= 2.0 =
* Removed preloader from the popup
* Moved selection panel to WooCommerce data table as Recommendations tab
* Improved performance 
* Bug Fixed

= 1.9.0 =
* Bug fix

= 1.9.0 =
* Update recommendation modal & added new features.

= 1.8.9 =
* Bug fixed for product_page shortcode

= 1.8.8 =
* Feature improvement & bug fixed

= 1.8.6 =
* Bug fix for WooCommerce Custom Product Addons

= 1.8.4 =
* Fixed theme compatibility

= 1.8.3 =
* Fixed modal appearing issue for variable product

= 1.8.2 =
* Fixed theme compatibility

= 1.8.0 =
* Added animation is added to cart and recommendations heading

= 1.7.0 =
* Improved plugin documentation

= 1.6.1 =
* Fix for Safari theme quick view 

= 1.6.0 =
* Optimized coding & performance 
* Added deactivation feedback 

= 1.5.3 =
* Improved performance.
* Improved theme compatibility.

= 1.5.1 =
* Bug Fixed

= 1.5 =
* Added Add To Cart flash notification.
* Improved theme compatibility.

= 1.4 =
* Add option to ajax add to cart for Variable & Group recommend products. Customers don't need to go to a single product page to add to a cart variable/group.

= 1.3.2 =
* Bug Fix

= 1.3.1 =
* Add editor field for default heading

= 1.2 =
* Update compatibility

= 1.1 =
* Update Features

= 1.0 =
* Initial release

== Upgrade Notice =