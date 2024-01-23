# Change Log
All notable changes to this project will be documented in this file.
This project adheres to Semantic Versioning(http://semver.org/).

## 4.2.2
-Fix issue that would cause quantity based promotions to be applied to certain customer price groups

## 4.2.1
- Fix a bug where the product categories would not be rendered correctly in the product tagging

## 4.2.0
- Update tagging with category listing support
- Add category export

## 4.1.1
- Fixes an issue that would cause category id's to be removed by the crawler

## 4.1.0
- Add compatibility with Prestashop 8
- Add category ids to product tagging

## 4.0.5
- Remove tax calculation from supplier cost in product builder

## 4.0.4
- Fix the bug where order is sent to all shops with multistore setup

## 4.0.3
- Always redirect to Nosto dashboard after connecting the account

## 4.0.2
- Fix new Nosto connection setup to be fully compatible with Prestashop versions 1.6.x.x
- Remove unique id from connection setup
- Improve message handling for Nosto connection

## 4.0.1
- Add redirect parameters for uninstalling Nosto account when SSO fails 

## 4.0.0
- Remove Iframe. Nosto account connection is now configured externally and redirects back to the Magento 2 admin
- Remove preview URLs that were used in the iframe
- Improve message handling for Nosto account connection

## 3.11.0
- Add product published date to product tagging

## 3.10.1
- Fix issue where orders were synced to all connected Nosto accounts

## 3.10.0
- Fix compatibility with Prestashop modules that use vlucas/phpdotenv ^3.6
- Remove PII data from Order API

## 3.9.4
* Fix backward compatibility with Prestashop 1.6

## 3.9.3
* Round product price, list_price and supplier_cost to 2 decimals

## 3.9.2
- Set correct price to variation based on tax include configuration 

### 3.9.1
- Add PHPStorm Inspections as a CI build step

## 3.9.0
- Upgrade Nosto-PHP-SDK to improve compatibility with Prestashop ps_checkout module
- Update copyright headers
- Update Github workflow and remove Jenkins
- Use GraphQL to send orders

## 3.8.1
- Update composer dependencies

## 3.8.0
- Add feature flag to enable/disable search term escaping for Nosto tagging
- Send active domain with API calls when upserting product and creating order

## 3.7.1
- Allow reconnecting same account to the same store in same language

## 3.7.0
- Encode HTML characters automatically
- Sort how order data is imported

## 3.6.3
- Fix order import sorting

## 3.6.2
- Fix missing language id parameter for Prestashop < 1.6 in item availability

## 3.6.1
- Bump Nosto SDK version to fix the double encoded Oauth redirect URL
- Fix constructing the product categories 

## 3.6.0
- Bump Nosto SDK version to support HTTP 2

## 3.5.3
- Fix negative SKU inventory in product availability
- Escape search term in tagging

## 3.5.2
- Fixed a bug that it failed to be installed to prestashop below version 1.6.1.0 with multi-language enabled

## 3.5.1
- Fixed a bug that was causing discrepancy in data sent to Nosto (Object Resolver)

## 3.5.0
- Update customer's marketing permission to Nosto via api
- Fixed a bug that page type div was rendered inside html head
- Fixed a bug that same Nosto account could be connected to different stores or languages.

## 3.4.3
- Fixed a bug that was preventing the tagging to show due to a missing cache clear after the update

## 3.4.2
- Fixed a bug that customer tagging has been disabled by default on prestashop 1.6
- Fixed a bug that it is not possible to enable or disable customer tagging

## 3.4.1
- Fixed a issue that it failed to be installed to some special prestashop environments

## 3.4.0
- Made it possible to override category by hooking to nosto events
- Add a switch to disable sending customer information to nosto
- Send an empty cart to nosto if the cart is empty to empty the cart in nosto server

## 3.3.3
- Fixed a bug that recommendations could get displayed on top of the product info on product page or similarly on category page
 
## 3.3.2
- Fixed a bug that after upgrading nosto from 2.x to 3.x, some of the nosto controllers and hooks are not registered to prestashop

## 3.3.1
- Fixed a bug that nosto injects html content to prestashop ajax responses on older prestashop version

## 3.3.0
- Add marketing permission for customer tagging and for buyer 
- Fix the compatibility issue with some third-party plugins which update catalog without setting employee to prestashop context

## 3.2.4
- Fix the bug that could not add product to cart without nosto account 
 
## 3.2.3
- Fix the issue that custom field keys were converted to snake case by html serializer
 
## 3.2.2
- Fix auto slots timing issue if center_column is not loaded before jQuery 
- Refactor: remove redundant variables, remove excess whitespaces and fix few Prestashop validation violations

## 3.2.1
- Fix compatibility issue with some checkout modules

## 3.2.0
- Add support for add to cart popup recommendations

## 3.1.0
- Support restoring abandoned shopping cart
- Add product features to product custom field tagging
- Add sku id to cart and order tagging

## 3.0.3
- Change product tag1, tag2 and tag3 tagging to tags1, tags2 and tags3 to compatible with nosto backend

## 3.0.2
- Fixed the issue that some products got incorrect images if there was a combination with id 0

## 3.0.1
- Fixed the issue that currency format was incorrect
- Fixed the issue that configuration key was too long on prestashop 1.5
- Fixed the product variation id is missing when multi-currency is enabled
- Fixed the product brand was presented as "false" when brand was not set

## 3.0.0
### Added
- Support product price variation tagging based on currency, country and customer group
- Support product sku tagging
- Reload recommendation after prestashop cart ajax actions
- Show notification if nosto plugin is not activated for all shops
- Support adding sku to shopping cart

### Changed
- Generate html tagging programmatically instead of using template
- Javascript function addProductToCart now supports quantity parameter
- Now nosto plugin always sends the original image to nosto

### Fixed
- Product price was incorrect if multi-store was enabled and shops had different base currencies
- The hook selector was not working
- Default variation id was not sent to nosto at the first time merchant turning on multi-currency
- Fixed the price rounding to respect system rounding setting and currency rounding setting
- Fixed the issue that product image url was incorrect in some situations such as product cover image was not enabled in the default combination
- Fixed the issue that sometimes recommendation was not showing on prestashop 1.7 because jquery was loaded to the page after nosto javascript 

## 2.8.9
- Fixed a bug that nosto injects html content to prestashop ajax responses

## 2.8.6
- Add a workaround for occasionally missing smarty before assigning variables

## 2.8.5
- Fix a bug that it doesn't work with certain prestashop version 

## 2.8.4
- Add qualification check based on traffic

## 2.8.3
- Bug fix to make extension work with PHP < 5.5

## 2.8.2
- Improved error handling for sending invalid product data to Nosto

## 2.8.1
- Fixed the handling of accounts in multi-store mode
- Inspection fixes and other code cleanup

## 2.8.0
- Send following attributes to Nosto
    - inventory level
    - supplier cost / wholesale price
    - alternative images
- Add hcid tagging to handle caching cart and customer tagging 
- Fix scope issue with multi-store setup
- Add Nosto icon to admin menu

## 2.7.1
- Fix the content forging to use cloned context

## 2.7.0
- Support for Prestashop 1.7
- Fix non-active product saving
- Add module version for Nosto calls
- Add page type tagging
- Add customer reference tagging
- Remove date published from product tagging
- Add possibility to choose the image for product tagging
- Fix add to cart viewed product event
- Use direct include for Nosto script
- Fix product status issue when product visibility is changed
- Add more error messages and warnings when settings are not correct or something goes wrong
- Refactor auto slots logic so that recos are not loaded twice
- Fix image urls to use https when SSL is enabled everywhere 

## 2.6.2
- Make plug-in compatible with PHP 5.2

## 2.6.1
- Change order and product tagging to header / footer
- Remove sending deleted currencies to Nosto

## 2.6.0
- Add support for multiple currencies (using exchange rates)
- Add tagging to 404 and order confirmation pages
- Clear cache after Nosto account is installed or reconnected
- Display proper error messages
- Add setting where to render default Nosto tagging
- Add support for account details
- Update also invisible products to Nosto
- Define user agent for API calls
- Fix the missing static token
- Add alert for missing Nosto API tokens
- Add support for external order ref

## 2.5.0
- Add possibility to override data for product and order models
- Handle empty payment module in order
- Add database prefix to custom queries
- Loosen up the product and order validation
- Change coding standards & module structure to meet Prestashop validation rules
- Introduce packaging with phing (development only)

## 2.4.4
- Fix occasionally missing smarty
- Remove submodules for backward compatibility and Nosto PHP SDK

## 2.4.3
- Update module admin page

## 2.4.2
### Fixed
- Issue with missing "addCss" method in hook "displayBackOfficeHeader", when controller in context inherits
from "AdminTab" instead of "AdminController" in PS 1.5+

## 2.4.1
### Fixed
- Bad release package

## 2.4.0
### Added
- Nosto admin tab to PS 1.5 and 1.6 versions for easy access to the Nosto admin pages
- Product attribute combinations to the product name in cart and order tagging to easily recognise them
- Order status and payment provider info to order tagging
- Support for account specific sub-domains when configuring Nosto

## 2.3.0
### Added
- New product update API and removed deprecated product re-crawl
- Order status information to the order confirmation API
- Feature to add product to cart directly from recommendations

### Changed
- Refactored data model validation and allow incomplete data to be tagged in frontend
- Updated Nosto SDK

## 2.2.1
### Fixed
- Connecting existing Nosto account using OAuth for multi-shop setups in Prestashop 1.5.0.0 - 1.5.4.1
- Preview urls on module admin page for multi-shop setups in Prestashop 1.5.0.0 - 1.5.4.1

## 2.2.0
### Added
- New module admin UI
- Hook for sending new product data to Nosto right after product has been created
- Tagging for search terms

### Changed
- Improved OAuth error messages

### Fixed
- Product price tagging tax display to depend on user active group
- Product availability tagging to also depend on the products visibility in PS 1.5+

## 2.1.1
### Fixed
- SDK sub-repository to use https instead of ssh

## 2.1.0
### Changed
- Improve server-to-server order confirmations
- Implement all communication with Nosto through the Nosto SDK library

## 2.0.1
### Fixed
- Cart tagging to show also when cart is empty

## 2.0.0
### Changed
- Updated translations

## 1.3.7
### Fixed
- Customer tagging for PS 1.4 versions

## 1.3.6
### Added
- Added http request adapter for curl

### Fixed
- Prestashop standards-compliant

## 1.3.5
### Fixed
- Check for current controller in some PS 1.5 versions

## 1.3.4
### Fixed
- Issue with logged in customer tagging on PS 1.4
- Recommendation slot logic to check the whole document and not only the center column before adding defaults

## 1.3.3
### Fixed
- Issue with logged in customer tagging

## 1.3.2
### Fixed
- Fixes

## 1.3.1
### Fixed
- Issue with front page url on account creation for shops without clean urls

## 1.3.0
### Added
- Support for multi-shop installations
- Support for multi-lingual installations
- OAuth2 authorization for connecting an existing Nosto account
- SSO support for logging in to Nosto
- Support for tagging brand pages (manufacturer)
- PS 1.4 support
- Support for re-crawling products

### Changed
- Disabled the left/right column hooks
- Updated localizations for English, French, German and Spanish
- Updated module admin page UI

## 1.2.0
### Added
- Option to create a new Nosto account in module settings, or use an existing one
- Support for basic recommendation slot editor in module settings (Prestashop 1.6 only)
- Support for sending order confirmations to Nosto's REST API without having the Nosto customer-id link
- Support for getting product and order history from the shop

### Fixed
- Bug in Nosto account creation where the employee first name was put as last name and vice versa
- Structure of order confirmations in REST API calls

## 1.1.2
### Fixed
- Category page tagging for Prestashop versions 1.5.x - 1.5.5.0

## 1.1.1
### Fixed
- Issue with category not always being passed to hook "hookDisplayFooterProduct"

## 1.1.0
### Added
- Current category to the product page tagging
- Product tags to the product page tagging
- French translations
- Nosto icon and improved the description
- Support for actionPaymentConfirmation hook to push order confirmations to Nosto's REST API
- Support for automatic account creation on module install
- Configuration option for automatically injecting recommendation slots on the category and search pages

### Changed
- Removed the "Top Sellers" page
- Removed "Server Address" from module configuration
- Updated Nosto JavaScript to the newest version
- Updated the admin user interface for Prestashop 1.6

### Fixed
- Fixed initial installation error
- Fixed category tagging to display only active and visible categories
- Fixed broken product image URL when using the legacy image filesystem

## 1.0.0
### Added
- Initial release
