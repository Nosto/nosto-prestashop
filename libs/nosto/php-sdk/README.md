php-sdk
=======

Provides tools for building modules that integrate Nosto into your e-commerce platform.

## Requirements

* PHP 5.2+

## What's included?

### Classes

* **NostoApiRequest** class for making API requests to the Nosto APIs
* **NostoApiToken** class that represents an API token which can be used whn making authenticated requests to the Nosto APIs
* **NostoCollection** collection base class
* **NostoProductCollection** collection class for nosto product objects
* **NostoOrderCollection** collection class for nosto order objects
* **NostoException** main exception class for all exceptions thrown by the SDK
* **NostoHttpException** HTTP request exceptions upon request failure
* **NostoExporter** class for exporting encrypted historical data from the shop
* **NostoExportOrderCollection** class for exporting historical order data
* **NostoExportProductCollection** class for exporting historical product data
* **NostoHelper** base class for all nosto helpers
* **NostoHelperDate** helper class for date related operations
* **NostoHelperIframe** helper class for iframe related operations
* **NostoHelperPrice** helper class for price related operations
* **NostoHttpRequest** class for making HTTP request, supports both curl and socket connections
* **NostoHttpRequestAdapter** base class for creating http request adapters
* **NostoHttpRequestAdapterCurl** http request adapter for making http requests using curl
* **NostoHttpRequestAdapterSocket** http request adapter for making http requests using sockets
* **NostoHttpResponse** class that represents a response for an http request made through the NostoHttpRequest class
* **NostoOAuthClient** class for authorizing the module to act on the Nosto account owners behalf using OAuth2 Authorization Code method
* **NostoOAuthToken** class that represents a token granted using the OAuth client
* **NostoOperationProduct** class for performing create/update/delete operations on product object
* **Nosto** main sdk class for common functionality
* **NostoAccount** class that represents a Nosto account which can be used to create new accounts and connect to existing accounts using OAuth2
* **NostoCipher** class for AES encrypting product/order information that can be exported for Nosto to improve recommendations from the get-go
* **NostoDotEnv** class for handling environment variables used while developing and testing
* **NostoMessage** util class for holding info about messages that can be forwarded to the account administration iframe to show to the user
* **NostoObject** base class for Nosto objects that need to share functionality
* **NostoOrderConfirmation** class for sending order confirmations through the API
* **NostoProductReCrawl** class for sending product re-crawl requests to Nosto over the API
* **NostoValidator** class for performing data validations on objects implementing NostoValidatableInterface

### Interfaces

* **NostoAccountInterface** interface defining methods needed to manage Nosto accounts
* **NostoAccountMetaDataBillingDetailsInterface** interface defining getters for billing information needed during Nosto account creation over the API
* **NostoAccountMetaDataIframeInterface** interface defining getters for information needed by the Nosto account configuration iframe
* **NostoAccountMetaDataInterface** interface defining getters for information needed during Nosto account creation over the API
* **NostoAccountMetaDataOwnerInterface** interface defining getters for account owner information needed during Nosto account creation over the API
* **NostoOrderBuyerInterface** interface defining getters for buyer information needed during order confirmation requests
* **NostoOrderInterface** interface defining getters for information needed during order confirmation requests
* **NostoOrderPurchasedItemInterface** interface defining getters for purchased item information needed during order confirmation requests
* **NostoOrderStatusInterface** interface defining getters for order status information needed during order confirmation requests
* **NostoExportCollectionInterface** interface defining getters for exportable data collections for the historical data
* **NostoOauthMetaDataInterface** interface defining getters for information needed during OAuth2 requests
* **NostoProductInterface** interface defining getters for product information needed during product re-crawl requests to Nosto over the API
* **NostoValidatableInterface** interface defining getters for validatable objects that can be used in conjunction with the NostoValidator class

### Libs

* **NostoCryptAES** class for aes encryption that uses mcrypt if available and an internal implementation otherwise
* **NostoCryptBase** base class for creating encryption classes
* **NostoCryptRijndael** class for rijndael encryption that uses mcrypt if available and an internal implementation otherwise
* **NostoCryptRandom** class for generating random strings

## Getting started

### Creating a new Nosto account

A Nosto account is needed for every shop and every language within each shop.

```php
    .....
    try {
        /** @var NostoAccountMetaDataInterface $meta */
        /** @var NostoAccount $account */
        $account = NostoAccount::create($meta);
        // save newly created account according to the platforms requirements
        .....
    } catch (NostoException $e) {
        // handle failure
        .....
    }
    .....
```

### Connecting with an existing Nosto account

This should be done in the shops back end when the admin user wants to connect an existing Nosto account to the shop.

First redirect to the Nosto OAuth2 server.

```php
    .....
    /** @var NostoOAuthClientMetaDataInterface $meta */
    $client = new NostoOAuthClient($meta);
  	header('Location: ' . $client->getAuthorizationUrl());
```

Then have a public endpoint ready to handle the return request.

```php
    if (isset($_GET['code'])) {
        try {
            /** @var NostoOAuthClientMetaDataInterface $meta */
            $account = NostoAccount::syncFromNosto($meta, $_GET['code']);
            // save the synced account according to the platforms requirements
        } catch (NostoException $e) {
            // handle failures
        }
        // redirect to the admin page where the user can see the account configuration iframe
        .....
    }
    } elseif (isset($_GET['error'])) {
        // handle errors; 3 parameter will be sent, 'error', 'error_reason' and 'error_description'
        // redirect to the admin page where the user can see an error message
        .....
    } else {
        // 404
        .....
    }
```

### Deleting a Nosto account

This should be used when you delete a Nosto account for a shop. It will notify Nosto that this account is no longer used.

```php
    try {
        /** @var NostoAccount $account */
        $account->delete();
    } catch (NostoException $e) {
        // handle failure
    }
```

### Get authenticated iframe URL for Nosto account configuration

The Nosto account can be created and managed through an iframe that should be accessible to the admin user in the shops
backend.
This iframe will load only content from nosto.com.

```php
    .....
    /**
     * @var NostoAccount|null $account account with at least the 'SSO' token loaded or null if no account exists yet
     * @var NostoAccountMetaDataIframeInterface $meta
     * @var array $params (optional) extra params to add to the iframe url
     */
    try
    {
        $url = Nosto::helper('iframe')->getUrl($meta, $account, $params);
    }
    catch (NostoException $e)
    {
        // handle failure
    }
    // show the iframe to the user with given url
    .....
```

The iframe can communicate with your module through window.postMessage
(https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage). In order to set this up you can include the JS
file `src/js/NostoIframe.min.js` on the page where you show the iframe and just init the API.

```js
    ...
    Nosto.iframe({
        iframeId: "nosto_iframe",
        urls: {
            createAccount: "url_to_the_create_account_endpoint_for_current_shop",
            connectAccount: "url_to_the_connect_account_endpoint_for_current_shop",
            deleteAccount: "url_to_the_delete_account_endpoint_for_current_shop"
        },
        xhrParams: {} // additional xhr params to include in the requests
    });
```

The iframe API makes POST requests to the specified endpoints with content-type `application/x-www-form-urlencoded`.
The response for these requests should always be JSON and include a `redirect_url` key. This url will be used to
redirect the iframe after the action has been performed. In case of the connect account, the url will be used to
redirect your browser to the Nosto OAuth server.
The redirect url also needs to include error/success message keys, if you want to show messages to the user after the
actions, e.g. when a new account has been created a success message can be shown with instructions. These messages are
hard-coded in Nosto.
You do NOT need to use this JS API, but instead set up your own postMessage handler in your application.

### Sending order confirmations using the Nosto API

Sending order confirmations to Nosto is a vital part of the functionality. Order confirmations should be sent when an
order has been completed in the shop. It is NOT recommended to do this when the "thank you" page is shown to the user,
as payment gateways work differently and you cannot rely on the user always being redirected back to the shop after a
payment has been made. Therefore, it is recommended to send the order conformation when the order is marked as payed
in the shop.

Order confirmations can be sent two different ways:

* matched orders; where we know the Nosto customer ID of the user who placed the order
* un-matched orders: where we do not know the Nosto customer ID of the user who placed the order

The Nosto customer ID is set in a cookie "2c.cId" by Nosto and it is up to the platform to keep a link between users
and the Nosto customer ID. It is recommended to tie the Nosto customer ID to the order or shopping cart instead of an
user ID, as the platform may support guest checkouts.

```php
    .....
    try {
        /**
         * @var NostoOrderInterface $order
         * @var NostoAccountInterface $account
         * @var string $customerId
         */
        NostoOrderConfirmation::send($order, $account, $customerId);
    } catch (NostoException $e) {
        // handle error
    }
    .....
```

### Sending product re-crawl requests using the Nosto API

Note: this feature has been deprecated in favor of the create/update/delete method below.

When a product changes in the store, stock is reduced, price is updated etc. it is recommended to send an API request
to Nosto that initiates a product "re-crawl" event. This is done to update the recommendations including that product
so that the newest information can be shown to the users on the site.

Note: the $product model needs to include only `productId` and `url` properties, all others can be omitted.

```php
    .....
    try {
        /**
         * @var NostoProductInterface $product
         * @var NostoAccountInterface $account
         */
        NostoProductReCrawl::send($product, $account);
    } catch (NostoException $e) {
        // handle error
    }
    .....
```

Batch re-crawling is also possible by creating a collection of product models:

```php
    .....
    try {
        /**
         * @var NostoExportProductCollection $collection
         * @var NostoProductInterface $product
         * @var NostoAccountInterface $account
         */
        $collection[] = $product;
        NostoProductReCrawl::sendBatch($collection, $account);
    } catch (NostoException $e) {
        // handle error
    }
    .....
```

### Sending product create/update/delete requests using the Nosto API

When a product changes in the store, stock is reduced, price is updated etc. it is recommended to send an API request
to Nosto to handle the updated product info. This is also true when adding new products as well as deleting existing ones.
This is done to update the recommendations including that product so that the newest information can be shown to the users
on the site.

Creating new products:

```php
    .....
    try {
        /**
         * @var NostoProductInterface $product
         * @var NostoAccountInterface $account
         */
        $op = new NostoOperationProduct($account);
        $op->addProduct($product);
        $op->create();
    } catch (NostoException $e) {
        // handle error
    }
    .....
```

Note: you can call `addProduct` multiple times to add more products to the request. This way you can batch create products.

Updating existing products:

```php
    .....
    try {
        /**
         * @var NostoProductInterface $product
         * @var NostoAccountInterface $account
         */
        $op = new NostoOperationProduct($account);
        $op->addProduct($product);
        $op->update();
    } catch (NostoException $e) {
        // handle error
    }
    .....
```

Note: you can call `addProduct` multiple times to add more products to the request. This way you can batch update products.

Deleting existing products:

```php
    .....
    try {
        /**
         * @var NostoProductInterface $product
         * @var NostoAccountInterface $account
         */
        $op = new NostoOperationProduct($account);
        $op->addProduct($product);
        $op->delete();
    } catch (NostoException $e) {
        // handle error
    }
    .....
```

Note: you can call `addProduct` multiple times to add more products to the request. This way you can batch delete products.

### Exporting encrypted product/order information that Nosto can request

When new Nosto accounts are created for a shop, Nosto will try to fetch historical data about products and orders.
This information is used to bootstrap recommendations and decreases the time needed to get accurate recommendations
showing in the shop.

For this to work, Nosto requires 2 public endpoints that can be called once a new account has been created through
the API. These endpoints should serve the history data encrypted with AES. The SDK comes bundled with the ability to
encrypt the data with a pure PHP solution (http://phpseclib.sourceforge.net/), It is recommended, but not required, to
have mcrypt installed on the server.

Additionally, the endpoints need to support the ability to limit the amount of products/orders to export and an offset
for fetching batches of data. These must be implemented as GET parameters "limit" and "offset" which will be sent as
integer values and expected to be applied to the data set being exported.

```php
    .....
    /**
     * @var NostoProductInterface[] $products
     * @var NostoAccountInterface $account
     */
    $collection = new NostoExportProductCollection();
    foreach ($products as $product) {
        $collection[] = $product;
    }
    // The exported will encrypt the collection and output the result.
    $cipher_text = NostoExporter::export($account, $collection);
    echo $cipher_text;
    // It is important to stop the script execution after the export, in order to avoid any additional data being outputted.
    die();
```

```php
    .....
    /**
     * @var NostoOrderInterface[] $orders
     * @var NostoAccountInterface $account
     */
    $collection = new NostoExportOrderCollection();
    foreach ($orders as $order) {
        $collection[] = $order;
    }
    // The exported will encrypt the collection and output the result.
    $cipher_text = NostoExporter::export($account, $collection);
    echo $cipher_text;
    // It is important to stop the script execution after the export, in order to avoid any additional data being outputted.
    die();
```

## Testing

The SDK is unit tested with Codeception (http://codeception.com/).
API and OAuth2 requests are tested using api-mock server (https://www.npmjs.com/package/api-mock) running on Node.

### Install Codeception & api-mock

First cd into the root directory.

Then install Codeception via composer:

```bash
    php composer.phar install
```

And then install Node (http://nodejs.org/) and the npm package manager (https://www.npmjs.com/). After that you can install the api-mock server via npm:

```bash
    npm install -g api-mock
```

### Running tests

First cd into the root directory.

Then start the api-mock server with the API blueprint:

```bash
    api-mock tests/api-blueprint.md
```

Then in another window run the tests:

```bash
    vendor/bin/codecept run
```
