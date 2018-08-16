=== Webshipr - automated shipping ===
Contributors: Webshipr
link: http://www.webshipr.dk
Tags: shipping, valgfrit afhentningssted, automated shipping, post danmark, postdk, bluewater,blue water,woocommerce, automated shipping, dropshipping, webshipr, webshipper, GLS, Bring, DHL, DHL Express, DAO, DHL Parcel, UPS
Stable tag: 2.5.3
Requires at least: 3.7
Tested up to: 4.9.2

Automated shipping for woo-commerce.

== Description ==

Webshipr automates the shipping flow in your WooCommerce webshop. In one click your shipments are sent directly to the shipper, a label is generated, and
your tracking informations are available directly from the woo-commerce backend.
It's free to try for 30 days. Sign up on http://www.webshipr.com.

= Key Features =
* Automate shipping
* Integrate Post Danmark
* Integrate Post Danmark valgfrit afhentningssted
* Integrate Blue Water shipping
* Integrate with Warehouse partners
* Integrate with GLS Shipping
* Integrate with GLS Pakkeshop
* Integrate with Swipbox
* Integrate with DHL Express
* Integrate with DHL Parcel
* Integrate with DAO
* Integrate with CoolRunner
* Integrate with UPS

-------------------------

== Installation ==
* Go to Plugins > “Add New”.
* Download the Webshipr plugin from Wordpress repository and Click "Install Now" to install the Plugin. A popup
   window will ask you to confirm your wish to install the Plugin.
= Note: = If this is the first time you've installed a WordPress Plugin, you may need to enter the FTP login credential information. If
          you've installed a Plugin before, it will still have the login information. This information is available through your web server host.

* Click “Proceed” to continue the installation. The resulting installation screen will list the installation as successful or note any problems during the install.
* If successful, click "Activate Plugin" to activate it, or “Return to Plugin Installer” for further actions.
* Go to Settings => Webshipr options
* Insert your API key from your Webshipr account.
* You are ready to go!


== Brief Version History ==
* 1.1.3: Fixed issues for Woo Commerce 2.1.2 in regards to "Valgfrit afhentningssted"
* 1.1.4: Can now work with warehouse partners. And improvements for "Valgfrit afhentningssted"
* 1.1.5: GLS Pakkeshop implemented
* 1.1.6: Fixed issues with GLS pakke shop and "Valgfrit afhentningssted" in checkout flow.
* 1.1.7: Fixed compatibility to Woocommerce 2.0
* 1.1.8: Fixed javascript compliance issue for some themes.
* 1.1.9: Improved rate VAT calculations and replaced confirmation address with with dynamic address.
* 1.2.1: Shipping delivery address replaced with pickup place for GLS Pakkeshop and Postdanmark valgfriafhentning.
* 1.2.2: Javascript in frontend has been updated
* 1.2.3: Confirmation mail contains now chosen address for pakkeshop.
* 1.2.4: Button applied in checkout to rescan for pickup destinations. Also a bugfix for update of metadata in checkout applied.
* 1.2.5: Checkout changed to allow customers to override pickup place address.
* 1.2.7: Choose in backend, if you want additional search field above pickup shops
* 1.2.9: Supports for different UOM in weight ( kg / g )
* 1.3.1: Free above is now inclusive VAT. Please take into account in your webshipr settings.
* 1.3.2: Classes applied to GUI to make it easier to customize
* 2.0.0: New plugin - PUPs are picked in lightbox with map. Entirely new design.
* 2.0.1: Bugfix: Search PUPs again, when rate has changed
* 2.0.4: Internationalization and support for swipbox applied.
* 2.0.5: I18n Bugfix
* 2.0.6: Autoprocess problem resolved
* 2.0.7: Autoprocess hook moved in order to ensure persistent functionality.
* 2.0.9: Provide free shipping for coupon codes with free shipping.
* 2.1.0: Fixed combatibility with Woocommerce 2.3. Please test carefully before updating production shop.
* 2.1.1: Weights transferred to webshipr per line, and not in total.
* 2.1.2: Weight for Variations calculated. Extra classes applied to checkout for better customization.
* 2.1.3: Minor updates to satisfy strict php criterias.
* 2.1.4: Improved responsiveness for iphone 4
* 2.1.5: Applied stock location for warehouse services
* 2.1.6: Fixed deprecation warnings in debug mode.
* 2.1.7: Transfers comment to webshipr
* 2.1.8: Removed dreprecation warning in get_ajax_url
* 2.1.9: Fixed 'Session expired problem'
* 2.2.0: Inserted error messages and ability to update orders from woocommerce backend.
* 2.2.1: Warehouse locations should now be inserted in SKU field.
* 2.2.3: Inserted contact name on Delivery address for Drop point shipments
* 2.2.4: Autoprocess settings has changed. It is now possible to autoprocess by different statuses
* 2.2.6: Drop Point selection does not reset when other validation issues have appeared.
* 2.2.7: Financial info transfered to webshipr in details in order to make duitable shipments more automated.
* 2.2.8: Fixed minor bugs
* 2.2.9: Dont autoprocess virtual products
* 2.3.0: Support for WPML multi currency
* 2.3.1: Bugfix for free oders ( division by zero )
* 2.3.4: Support for droppoint across countries
* 2.3.5: Transfer variations to webshipr description
* 2.3.8: Support for WooCommerce 2.6.0
* 2.4.0: Google needs a separate Google Maps API token for Drop Point searches. Settings for such token was applied in this version.
* 2.4.1: Resolves problem with sending orders where variations are removed.
* 2.4.2: Enabled possibility to add tarif number and country origin code.
* 2.4.3: Changed visible order number to match between webshipr and WooCommerce
* 2.4.4: Scripts minified and other optimizations applied.
* 2.4.5: Changed privileges so users with manage_woocommerce can process orders.
* 2.5.0: Added support for WCSubscription
* Added option to options page
* Added support for pickup points in Klarna checkout
* Fixed issues around coupons
* 2.5.1: Added support for WC Pre-Orders
* Fixed bug with recent woocommerce update
* 2.5.2: Fixed bug with recent woocommerce update
* 2.5.3: Fixed bug for orders without a shipping_method
-------------------------
