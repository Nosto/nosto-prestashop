# Change Log
All notable changes to this project will be documented in this file.
This project adheres to Semantic Versioning(http://semver.org/).

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