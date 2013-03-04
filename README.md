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
	* "Other Customers Were Interested In"
	* "You Might Also Like"
	* "Most Popular Products In This Category"

* 3 elements for the shopping cart page
	* "Customers Who Bought These Also Bought"
	* "Products You Recently Viewed"
	* "Most Popular Right Now"

* 2 elements for the product category page, top and bottom
	* "Most Popular Products In This Category"
	* "Your Recent History"

* 2 elements for the search results page, top and bottom
	* "Customers who searched '{search term}' viewed"
	* "Your Recent History"

* 2 elements for the sidebars, 1 left and 1 right
	* "Popular Products"
	* "Products You Recently Viewed"

* 2 elements for all pages, top and bottom
	* "Products containing '{keywords}'"
	* "Products You Recently Viewed"

Note that you can change what recommendations are shown in which nosto elements. You can also add additional elements
to the site by simply dropping in div elements of the following format:
'<div class="nosto_element" id="{id of your choice}"></div>'

The module also creates a new page called "Top Sellers". The page is added to the sites main menu automatically when
installing the module. The page contains only one Nosto element by default.

## Installation



## Configuration



## License

Open Software License ("OSL") v. 3.0

## Dependencies

PrestaShop version 1.5.x

## Changelog
