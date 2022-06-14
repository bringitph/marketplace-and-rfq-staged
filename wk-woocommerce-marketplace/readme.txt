=== Marketplace for Woocommerce ===
Contributors: Webkul
Version: 5.1.2
Tags: b2c marketplace, marketplace, marketplace for woocommerce, marketplace plugin, multi-vendor marketplace, seller, woocommerce marketplace

WordPress:
	* Requires at least: 4.4.0
	* Tested up to: 5.9.x

WooCommerce:
	* Requires at least: 4.0.0
  	* Tested up to: 5.9.x

PHP:
	* Requires at least: 7.0.0
  	* Tested up to: 8.0.0

License: license.txt file included with plugin
License URI: https://store.webkul.com/license.html

This plugin converts the WooCommerce store into multi-vendor store. Using this plugin, the seller can manage the inventory, shipment, seller profile page, seller collection page and much more.

== Installation ==

1. Upload the `wk-woocommerce-marketplace` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin using the 'Marketplace' menu

== Frequently Asked Questions ==

1. Can multiple sellers sell the same product?
Ans: You can check our WooCommerce Marketplace Price Comparison module which will allow multiple sellers to sell the same product.

2. Can the seller set the shipping method by its own?
Ans: In this module, the admin can set the shipping for the seller, but if you want the customer should define its shipping, then you can check the Marketplace Table Rate Shipping module.

3. Will the module works with the WooCommerce themes?
Ans: Our marketplace will work with every theme based on the default functionality of WooCommerce.

4. Can the customers buy the products of the different seller at the same time?
Ans: Yes, the buyer can purchase products from different sellers by merely adding the products to cart and then proceeding to checkout.

5. What types of commissions can be set by the Store owner?
Ans: The store owner can set the commission in two types:
	• Global commission – The commission will be applied to all sellers.
	• Seller’s Commission – The commission will be applied to the selective seller only.

6. How can I convert WooCommerce website into multi-seller website?
Ans: You can purchase the WooCommerce Multi Vendor Marketplace module.

For any Query please generate a ticket at https://webkul.com/ticket/

== 5.1.2 (2021-11-15) ==
Added: Compatibility with custom registration plugin for field validations.
Added: Settings to enable translation capability to seller with WPML globally and individually.
Added: Dynamic weight and dimensions units for seller products.
Added: Option to show 'Shop Name' in seller list widget.
Added: Loader on clicking favorite seller heart icon on single product page.
Added: Compatibility with eMarket theme to show notices properly on shipping zone operations.
Added: Filter hook for adding custom filed on add product form on seller front end.
Added: Zip code validation using WC function on the seller profile page.
Added: Recipients option in seller emails similar to new WooCommerce email structure.
Fixed: Email contents and setting keys to allow email translation for WPML sites.
Fixed: Order history and order views page data.
Fixed: Approve and disapprove process stuck on multilingual site.
Fixed: Issues in updating shipping classes from seller frontend.
Fixed: Showing seller name on seller profile page instead of seller shop name.
Fixed: Seller shipping layout and functionality for multiple shipping classes.
Fixed: Variations are not removing from seller product edit page.
Fixed: XSS vulnerabilities issues in registration forms.
Fixed: White screen issues on adding products and shipping on some php version.
Fixed: Issues in deleting shop follower and favorite sellers.
Fixed: Shipping classes appearance on seller and admin product edit pages on front end and backend both.
Fixed: Shipping calculation for seller shipping through classes.
Fixed: Assign product action not working for translated site.
Fixed: SKU validation issue.
Fixed: Image upload issues from seller front end.
Fixed: Icons are not showing on seller dashboard endpoints.
Fixed: Custom footer messed up style on order history on seller backend dashboard.
Fixed: Social URL and logo images update issues from seller backend dashboard.

== 5.1.0 (2021-10-20) ==
Added: Minimum order amount feature for admin and seller.
Added: Seller shop link on order items.
Added: Become a seller feature for customer on the store.
Added: Product quantity limit feature for admin and seller's product.
Added: Order approval feature for seller's product.
Added: Dynamic SKU feature for seller's product.
Added: Sold by title on products on cart and checkout page.
Added: Compatibility with some popular themes for seller dashboard layout.
Improved: Seller profile UI to focus more on payment information.
Improved: Feedback statuses pending, approved, disapproved.
Fixed: Shipping calculations and their appearance issues.
Fixed: Redundancy in review system UI.
Fixed: HTML content are not storing properly in database form product desc and short description fields.
Fixed: Currency position according woocommerce settings on Seller dashboard grids.

== 5.0.2 (2021-07-12) ==
Added: Google analytics feature to track customer activity on the site.
Added: Google analytics to track purchases, product sales, seller product's activities.
Added: Anonymize IP feature in Google analytics tracking.
Added: Option to select custom seller page from Marketplace general settings.
Added: Fallback function to create DB tables if it was failed on activation.
Fixed: Layout issues in extension menu and re-opened it.
Improved: Coding standards based on php and wp coding standards.

== 5.0.1 (2021-05-27) ==
Enhancement - Added filter and action hooks for better compatibility to other addons.
Removed - Unnecessary regular and sale price fields from add and edit product form for variable and grouped products.
Added - Success notice on seller registration.
Fixed - Fixed Seller can access some admin sections in admin dashboard.
Fixed - Fixed Seller add product page warning with php 7.4+.
Fixed - Database tables were not creating thus sellers are not listing.
Fixed - coding standard using WordPress and WooCommerce ruleset.
Fixed - Setting getting empty after updating from older versions.
Added - Validation on -ive and empty commission value and added percentage symbol.
Fixed - Handled issue with empty row in seller listing when a seller is deleted.
Fixed - SKU is deleting automatically when updating product.
Fixed - Query sorting order, latest seller will be appeared at top.
Fixed - Shipping popup styling and missing title on backend for MP Flat Rate shipping.
Fixed - Issue with sending mail by seller to their shop-followers.
Fixed - Issue with adding and updating Variable and grouped products.

== 5.0.0 ( 2020-12-20 ) ==
Enhancement - Changed coding structure as per Webkul default code structure.
Enhancement - Added feature that seller can configured shipping class in Marketplace Flat Rate Shipping
Enhancement - Unique value should save when Endpoint save.
Enhancement - Added pagination where all seller review display on seller profile view page.
Fixed - Seller order status issue when order placed from two different seller.
Fixed - Seller can publish product from seperate dashboard even he is now allow for the same.
Fixed - product stock status issue on seller product list page at seller end.

== 4.9.3 ==
Enhancement - Added Marketplace Local Pickup and Marketplace Freeshipping Method.
Enhancement - Compatibility With Marketplace Tax Manager.
Enhancement - Tax Refund by seller.
Fixed - Deletion in Product variation.

== 4.9.2 ==
Enhancement - Added an thumbnail for the product image if uploaded by seller.
Enhancement - Added new hooks.
Enhancement - Allowed Multiple Shipping Option.
Enhancement - Added Option to show Admin Shipping zones or Seller Shipping Zones at Cart Page
Fixed - Issue in displaying total seller amount with respective refunded amount at seller order page at admin end.
Fixed - Rewrite rules array issue.

== 4.9.1 ==
Enhancement - Allow user to change endpoints and title of user Dashboard.
Removed - The dependency on datatable.
Fixed - admin mail issue while seller register

== 4.9.0 ==
Enhancement - Added compatibility for Multisite.
Enhancement - Added Refund management at seller end.
Enhancement - Added email templates for seller for following order status: on-hold, processing, completed, refunded, failed and cancelled.
Enhancement - Added some translations.
Enhancement - Added compatibility with WC Marketplace Split Order plugin.
Fixed - Transaction query.

== 4.8.4 ==
Fixed - Virtual product issue on seller side.
Fixed - Seller mail for new order.
Fixed - Shipping page issue.
Fixed - Seller approve issue on seller-list page.
Added - Tweak css for feedback popup.
Enhancement - Updated the commission part.
Enhancement - Added email templates
Enhancement - Added pagination in products list page in backend to load products faster.
Enhancement - Added compatibility with Webkul Marketplace Event Manager plugin.
Enhancement - Added compatibility with Webkul Marketplace Multi-currency plugin.

== 4.8.3 ==
Enhancement - Fixed error on seller creation on admin side.
Enhancement - Updated transaction function.
Enhancement - Fixed seller register mail issue.
Enhancement - Fixed the product type issue admin side.
Enhancement - Fixed the product meta data issue on seller end.
Tweak - Added additional information on seller order mail.
Tweak - Added compatibility with Webkul Marketplace Reward plugin.
Tweak - Added compatibility with Webkul Marketplace USPS shipping.
Tweak - Added hook for adding order meta data on seller side.

== 4.8.2 ==
Fixed - The translation issue.
Fixed - Product listing issue.
Fixed - Notification issue.
Fixed - Issues regarding seller slug.
Updated - Seller page css.
Updated - Commission management.


== 4.8.1 ==
Updated - Seller stat for showing only seller data.
Fixed - Shipping issue.

== 4.8.0 ==
Added - Feature of seperate seller dashboard for seller.
Added - Translation support where missing.
Added - Additional fields provided for seller country and state in profile page.
Added - Seller query management.
Added - Additional hooks and filters.
Added - Mail template for admin answer regarding query.
Fixed - Responsive issues.
Fixed - Shipping class issue.
Fixed - The shipping and discount issue.
Fixed - The countries SVG layout issue.
Fixed - Mail preview issue.
Updated - The commission management.

== 4.7.4 ==
Added - Marketplace extension tab at admin end for showing addons and other Webkul WordPress and WooCommerce plugins.
Added - Seller can now delete the particular shipping method while editing shipping zone.
Added - Check for the WC_Admin_Report at dashboard.
Fixed - The Emogrifier issue while sending mail to seller while ordering.
Fixed - Error in adding shipping zone at seller end due to updated version of WooCommerce 3.4.
Fixed - jquery error while seller creating product.
Fixed - Warnings and added link to login page in favourite seller product page.

== 4.7.3 ==
Added - Nonce to prevent CSRF vulnerability in product delete at seller end.
Updated - Grouped product addition flow as per WooCommerce 3.x.x.
Updated - Icons in the pages, used Webkul Rango fonts in place of Font Awesome.
Updated - Price display in product list at seller end.

== 4.7.2 ==
Added - Filter to search by seller in marketplace product page at admin end.
Added - Product page link in products in order details page seller end.
Added - Variation details in order view for variable product.

== 4.7.1 ==
Introduced -  Category tree at seller end.
Restricted -  Seller from accessing admin end.
Updated - New order mail action for seller.
Updated - Seller delete page layout.
Fixed - Generated password for new user in email notification.

== 4.7.0 ==
Introduced - Transactions.
Added - Mass assign products to sellers added.
Added  - Upsells/Crosssells feature for seller.
Added - Seller reviews approval from admin end.
Added - Backend set seller category and product type by admin.

== 4.6.0 ==
Added - A buyer can give review to seller in a more interactive way.
Added - Marketplace Seller Menu with the WooCommerce default menu to make it more accessible.
Added - Log in "Ask To Admin" feature at seller-end.
Added - Visibility of seller rating at product page.
Added - Seller Profile asset visibility configuration at admin-end.
Added - Multiple selection of product gallery images at seller-end.
Added - Manual commission pay feature.
Added - Inbuilt Marketplace Flat Rate Shipping.
Fixed - Login with Facebook while reviewing seller fixed.
Fixed - All XSS vulnerabilities.
Updated - Email template feature, and preview added.
Updated - Marketplace Seller Dashboard with new and interactive charts.
Updated - Front-end design (layout) [Major Update].

== 4.5.0 ==
Added - Seller preview and collection page restrictions.
Added - Shipping access restrictions for seller.
Added - Product update notice at seller end.
Added - Multiple file upload for downloadable product at seller end.
Fixed - Variable product issue.
Fixed - Default Dashboard view at seller end.
Fixed - XSS vulnerability at seller's profile edit page.
Fixed - Seller shop follower page design issue.
Updated - Rewrite rules.

== 4.4.0 ==
Added - Multi-language feature.
Added - .pot file.
Added - Code standardization.
Introduced - E-mail templates for various actions like seller registration, ask to admin, etc.
Introduced - Notification center at admin as well as seller end.

== 4.3.2 ==
Introduced - Separate Seller Registration feature which can be managed by admin.

== 4.3.1 ==
Added -  Feature to convert customer into seller by changing role and vice-versa.
Updated - Module as per new version of WooCommerce i.e., 3.0

== 4.3.0 ==
Introduced - New Invoice management feature in plugin for seller and admin end.
Introduced - New shop follow feature / Favourite Seller in marketplace plugin.
Fixed - My account pages calling bugs.

== 4.2.3 ==
Updated - Hook in Seller Panel list files for adding more tabs by plugins.

== 4.2.2 ==
Introduced - Seller shipping management.
Fixed - Bug in deletion of zone to seller meta table and page reloaded once zone cost is defined.

== 4.2.1 ==
Fixed - Bugs related to visibility of seller product.

== 2.3.0 ==
Added - Product by feature on product page.
Fixed - Edit product page vulnerability issue.
Updated - Setting page admin hidden value for seller page title.
Updated - Rewrite rule for shop address.
Updated - Phone number validation.

== 2.2.0 ==
Fixed - Bugs related with admin end.
Fixed - Facebook login issue.

== 2.1.0 ==
Fixed - Bugs with DEBUG MODE true

== 2.0.0 ==
Updated - Version with many bugs fixed and new controls to seller.

== 1.0.0 ==
Initial release
