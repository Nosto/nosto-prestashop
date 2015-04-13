# Nosto Tagging for PrestaShop

## Description

The module integrates the Nosto marketing automation service, that can produce personalized product recommendations on
the site.

The module adds the needed data to the site through PrestaShop's hook system. There are two types of data added by the
module; tagging blocks and nosto elements.

Tagging blocks are used to hold meta-data about products, categories, orders, shopping cart and customers on your site.
These types of blocks do not hold any visual elements, only meta-data. The meta-data is sent to the Nosto marketing
automation service when customers are browsing the site. The service then produces product recommendations based on the
information that is sent and displays the recommendations in the nosto elements.

Nosto elements are placeholders for the product recommendations coming from the Nosto marketing automation service. The
elements consist of only an empty div element that is populated with content from the Nosto marketing automation
service.

By default the module creates the following nosto elements:

* 3 elements for the product page
	* "Other Customers Were Interested In" ( nosto-page-product1 )
	* "You Might Also Like"  ( nosto-page-product2 )
	* "Most Popular Products In This Category"  ( nosto-page-product3 )
* 3 elements for the shopping cart page
	* "Customers Who Bought These Also Bought" ( nosto-page-cart1 )
	* "Products You Recently Viewed" ( nosto-page-cart2 )
	* "Most Popular Right Now" ( nosto-page-cart3 )
* 2 elements for the product category page, top and bottom
	* "Most Popular Products In This Category" ( nosto-page-category1 )
	* "Your Recent History" ( nosto-page-category2 )
* 2 elements for the brand page, top and bottom
    * "Most Popular Products In This Category" ( nosto-page-category1 )
    * "Your Recent History" ( nosto-page-category2 )
* 2 elements for the search results page, top and bottom
	* "Customers who searched '{search term}' viewed" ( nosto-page-search1 )
	* "Your Recent History" ( nosto-page-search2 )
* 2 elements for the sidebars, 1 left and 1 right
	* "Popular Products" ( nosto-column-left )
	* "Products You Recently Viewed" ( nosto-column-right )
* 2 elements for all pages, top and bottom
	* "Products containing '{keywords}'" ( nosto-page-top )
	* "Products You Recently Viewed" ( nosto-page-footer )
	

Note that you can change what recommendations are shown in which nosto elements. You can also add additional elements
to the site by simply dropping in div elements of the following format:
'`<div class="nosto_element" id="{id of your choice}"></div>`'

## Installation

Please refer to the PrestaShop documentation on how to get the module to appear in your installation admin section.

Once the module appears in your installation, you must install it into the store. Navigate to the "Modules" section and
locate the module, it will show up under the "Advertising & Marketing" section. The installation is done simply by
clicking the "install" button on the right by the module in the list.

During the install the module also creates some new hooks for PrestaShop, namely "displayCategoryTop",
"displayCategoryFooter", "displaySearchTop" and "displaySearchFooter". You will need to implement these in your
installation in order for the module to work properly.

* displayCategoryTop
	* This hook should be placed above the product list on category pages
	* You need to add "`{hook h='displayCategoryTop'}`" in your themes category.tpl file

* displayCategoryFooter
	* This hook should be placed below the product list on category pages
	* You need to add "`{hook h='displayCategoryFooter'}`" in your themes category.tpl file

* displaySearchTop
	* This hook should be placed above the search result list on search pages
	* You need to add "`{hook h='displaySearchTop'}`" in your themes search.tpl file

* displaySearchFooter
	* This hook should be placed below the search result list on search pages
	* You need to add "`{hook h='displaySearchFooter'}`" in your themes search.tpl file

## Configuration

Once you have installed the module, you need to configure it. This is done by clicking the "Configure" link for the
module in the modules listing. This will open a new page with the module configuration.

## License

Academic Free License ("AFL") v. 3.0

## Dependencies

PrestaShop version 1.4.x - 1.6.x

## Changelog

* 2.1.1
    * Fix SDK sub-repository to use https instead of ssh

* 2.1.0
    * Improve server-to-server order confirmations
    * Implement all communication with Nosto through the Nosto SDK library

* 2.0.1
    * Fixed cart tagging to show also when cart is empty

* 2.0.0
    * Updated translations

* 1.3.7
    * Fixed customer tagging for PS 1.4 versions

* 1.3.6
    * Added http request adapter for curl
    * Prestashop standards-compliant

* 1.3.5
    * Fixed check for current controller in some PS 1.5 versions

* 1.3.4
    * Fixed issue with logged in customer tagging on PS 1.4
    * Fixed recommendation slot logic to check the whole document and not only the center column before adding defaults

* 1.3.3
    * Fixed issue with logged in customer tagging

* 1.3.2
    * Fixes

* 1.3.1
    * Fixed issue with front page url on account creation for shops without clean urls

* 1.3.0
    * Added support for multi-shop installations
    * Added support for multi-lingual installations
    * Added OAuth2 authorization for connecting an existing Nosto account
    * Added SSO support for logging in to Nosto
    * Added support for tagging brand pages (manufacturer)
    * Added PS 1.4 support
    * Added support for re-crawling products
    * Disabled the left/right column hooks
    * Updated localizations for English, French, German and Spanish
    * Updated module admin page UI

* 1.2.0
    * Added option to create a new Nosto account in module settings, or use an existing one
    * Added support for basic recommendation slot editor in module settings (Prestashop 1.6 only)
    * Added support for sending order confirmations to Nosto's REST API without having the Nosto customer-id link
    * Added support for getting product and order history from the shop
    * Fixed bug in Nosto account creation where the employee first name was put as last name and vice versa
    * Fixed structure of order confirmations in REST API calls

* 1.1.2
    * Fixed category page tagging for Prestashop versions 1.5.x => 1.5.5.0

* 1.1.1
    * Fixed issue with category not always being passed to hook "hookDisplayFooterProduct"

* 1.1.0
    * Added current category to the product page tagging
    * Added product tags to the product page tagging
    * Added French translations
    * Added Nosto icon and improved the description
    * Added support for actionPaymentConfirmation hook to push order confirmations to Nosto's REST API
    * Added support for automatic account creation on module install
    * Added configuration option for automatically injecting recommendation slots on the category and search pages
    * Fixed initial installation error
    * Fixed category tagging to display only active and visible categories
    * Fixed broken product image URL when using the legacy image filesystem
    * Removed the "Top Sellers" page
    * Removed "Server Address" from module configuration
    * Updated Nosto JavaScript to the newest version
    * Updated the admin user interface for Prestashop 1.6

* 1.0.0
	* Initial release
