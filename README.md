# Personalization for PrestaShop

Increase your conversion rate and average order value by delivering your customers personalized product recommendations
throughout their shopping journey.

Nosto allows you to deliver every customer a personalized shopping experience through recommendations based on their
unique user behavior - increasing conversion, average order value and customer retention as a result.

[http://nosto.com](http://nosto.com/)

## Getting started

### How it works

The extension automatically adds product recommendation elements to the shop when installed. Basically, empty "div"
placeholder elements. These elements will appear on the home page, product pages, category pages, search result pages
and the shopping cart page. These elements are automatically populated with product recommendations from your shop.

This is possible by mining data from the shop when the user visits the pages. For example, when the user is browsing a
product page, the product information is asynchronously sent to Nosto, that in turn delivers product recommendations
based on that product to the shop and displays them to the user.

The more users that are visiting the site, and the more page views they create, the better and more accurate the
recommendations become.

In addition to the recommendation elements and the real time data gathering, the extension also includes some behind the
scenes features for keeping the product information up to date and keeping track of orders in the shop.

Every time a product is updated in the shop, e.g. the price is changed, the information is sent to Nosto over an API.
This will sync the data across all the users visiting the shop that will see up to date recommendations.

All orders that are placed in the shop are also sent to Nosto. This is done to keep track of the orders that were a
direct result of the product recommendations, i.e. when a user clicks a product in the recommendation, adds it to the
shopping cart and places the order.

Nosto also keeps track of the order statuses, i.e. when an order is changed to "payed" or "canceled" the order is
updated over an API.

All you need to take Nosto into use in your shop, is to install the extension and create a Nosto account for your
shop. This is as easy as clicking a button, so read on.

### Installing

The module comes bundled with PrestaShop 1.5 and 1.6. For PrestaShop 1.4 you wll need to fetch the module archive from
the [addons page](http://addons.prestashop.com/en/advertising-marketing-newsletter-modules/18349-nostotagging.html) and
upload the archive in the backend of your PrestaShop installation. Please note that this version of Nosto module is only 
compatible with Prestashop > 1.5. The latest module version where Prestashop < 1.5 is supported is 2.7.1.   

To install the module in PrestaShop, navigate to the "Modules" section in the backend and locate the module under the
"Advertising & Marketing" section. Then just click the "Install" button next to the module in the list. That's it.

### Configuration

By clicking the modules "Configure" link in the modules listing, you will be redirected to the Nosto account
configuration page were you can create and manage your Nosto accounts. You will need a Nosto account for each shop
(multi-shop) and language in the installation.

Creating the account is as easy as clicking the install button on the page. Note the email field above it. You will need
to enter your own email to be able to activate your account. After clicking install, the window will refresh and show
the account configuration.

You can also connect and existing Nosto account to a shop, by using the link below the install button. This will take
you to Nosto where you choose the account to connect, and you will then be redirected back where you will see the same
configuration screen as when having created a new account.

This concludes the needed configurations in PrestaShop. Now you should be able to view the default recommendations in
your shops frontend by clicking the preview button on the page.

You can read more about how to modify Nosto to suit your needs in our [support center](https://support.nosto.com/),
where you will find PrestaShop related documentation and guides.

### Extending

#### Change position of recommendation elements

All recommendations are added through the PrestaShop hook system, which means that their position is dependent on the
hooks position in the theme.

In order to change the position of any recommendation element added by Nosto, you can either move the PrestaShop hook
position in your theme or unlink the Nosto module from the hook and add the elements directly into your theme. Please
refer to the PrestaShop documentation on how to re-position hooks.

During the module installation, some new hooks are also created. These are:

* displayCategoryTop
* displayCategoryFooter
* displaySearchTop
* displaySearchFooter

These can be used to position the recommendations on the product category and search result pages, as PrestaShop does
not include any hooks out-of-the-box for these pages. The module will automatically add the recommendations without
these hooks as well, but for more precise positioning we recommend to include them in your theme. This is as easy as
adding a line like `{hook h='displayCategoryTop'}` to your theme layout file.

#### Adding new recommendation elements

The easiest way to add your own recommendation elements is to simply add the placeholder "div" in your theme layout,
e.g. `<div class="nosto_element" id="{id-of-your-choice}"></div>`. Note that you need to register this new element in
your [Nosto account settings](https://my.nosto.com/), so that Nosto can start using it.

## License

Academic Free License ("AFL") v. 3.0

## Dependencies

PrestaShop version 1.5.x - 1.6.x
