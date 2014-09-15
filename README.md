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
module in the modules listing. This will open a new page with the module configuration that includes three settings:

* Server address
	* This is the server address for the Nosto marketing automation service
	* It will have the default value of "connect.nosto.com" and you do not need to change this
* Account name
	* This is your Nosto marketing automation service account name that you got when registering for the service
* Use default nosto elements
	* This setting controls if the module should create and output the default nosto elements for showing the product
	recommendations
	* You can disable the defaults if you want to use your own elements in your layout

## License

Open Software License ("OSL") v. 3.0

## Dependencies

PrestaShop version 1.5.x

## Changelog

* 1.0.0
	* Initial release
