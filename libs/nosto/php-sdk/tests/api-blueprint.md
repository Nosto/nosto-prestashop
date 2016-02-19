FORMAT: 1A
HOST: http://localhost:3000

# nosto/php-sdk

# Group Account
Account related resources

## Create Account [/accounts/create/{lang}]

+ Parameters

    + lang (string) ... the 2-letter language ISO ((ISO 639-1)) code for the account language

### New account [POST]

+ Request (application/json)

        {
            "title": "My Shop",
            "name": "00000000",
            "platform": "platform name",
            "front_page_url": "http://my.shop.com",
            "currency_code": "USD",
            "language_code": "en",
            "owner": {
                "first_name": "James",
                "last_name": "Kirk",
                "email": "james.kirk@example.com"
            },
            "billing_details": {
                "country": "us"
            },
            "api_tokens": ["sso", "products"],
            "currencies": {
                "USD": {
                    "currency_before_amount": true,
                    "currency_token": "$",
                    "decimal_character": ".",
                    "grouping_separator": ",",
                    "decimal_places": 2,
                },
                "EUR": {
                    "currency_before_amount": false,
                    "currency_token": "€",
                    "decimal_character": ".",
                    "grouping_separator": ",",
                    "decimal_places": 2,
                }
            },
            "default_variant_id": "USD",
            "use_exchange_rates": true
        }

+ Response 200 (application/json)

        {
            "sso_token": "01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783",
            "products_token": "01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783",
            "rates_token": "01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783",
            "settings_token": "01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783"
        }

## OAuth Access Token [/token{?code,client_id,client_secret,redirect_uri,grant_type}]

+ Parameters

    + code (string) ... the authorization code that was received in the redirect url from the oauth server
    + client_id (string) ... the oauth client id
    + client_secret (string) ... the oauth client secret
    + grant_type (string) ... must be "authorization_code"

### Get the OAuth access token [GET]

+ Response 200 (application/json)

        {
            "access_token": "01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783",
            "merchant_name": "platform-00000000"
        }

## Sync Account [/exchange{?access_token}]

+ Parameters

    + access_token (string) ... the access token received in the oauth token request (above)

### Sync existing account details [GET]

+ Response 200 (application/json)

        {
            "api_sso": "01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783",
            "api_products": "01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783",
            "api_rates": "01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783",
            "api_settings": "01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783"
        }

## Single Sign On [/hub/{platform}/load/{email}]

+ Parameters

    + platform (string) ... the platform name
    + email (string) ... the email address of the user who is doing the SSO

### SSO login [POST]

+ Request (application/x-www-form-urlencoded)

    + Headers

            Authorization: Basic OjAxMDk4ZDBmYzg0ZGVkN2M0MjI2ODIwZDVkMTIwN2M2OTI0M2NiYjM2MzdkYzRiYzJhMjE2ZGFmY2YwOWQ3ODM=

    + Body

            fname=James&lname=Kirk

+ Response 200 (application/json)

        {
            "login_url": "https://nosto.com/auth/sso/sso%2Bplatform-00000000@nostosolutions.com/xAd1RXcmTMuLINVYaIZJJg"
        }

## Deleting Account [/hub/uninstall]

### Notify nosto about deleted account [POST]

+ Request (application/json)

    + Headers

            Authorization: Basic OjAxMDk4ZDBmYzg0ZGVkN2M0MjI2ODIwZDVkMTIwN2M2OTI0M2NiYjM2MzdkYzRiYzJhMjE2ZGFmY2YwOWQ3ODM=

+ Response 200

## Update Account [/settings]

### Update existing account in Nosto [PUT]

+ Request (application/json)

        {
            "title": "My Shop",
            "front_page_url": "http://my.shop.com",
            "currency_code": "USD",
            "language_code": "en",
            "currencies": {
                "USD": {
                    "currency_before_amount": true,
                    "currency_token": "$",
                    "decimal_character": ".",
                    "grouping_separator": ",",
                    "decimal_places": 2,
                },
                "EUR": {
                    "currency_before_amount": false,
                    "currency_token": "€",
                    "decimal_character": ".",
                    "grouping_separator": ",",
                    "decimal_places": 2,
                }
            },
            "default_variant_id": "USD",
            "use_exchange_rates": true
        }

+ Response 200 (application/json)

        {
            "sso_token": "01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783",
            "products_token": "01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783",
            "rates_token": "01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783",
            "settings_token": "01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783"
        }

# Group Order
Order related resources

## Matched Order Confirmation [/visits/order/confirm/{m}/{cid}]

+ Parameters

    + m (string) ... the account name for which this order was placed
    + cid (string) ... the nosto customer id that placed the order

### New account [POST]

+ Request (application/json)

        {
            "order_number": 1,
            "order_status_code": "complete",
            "order_status_label": "Complete",
            "buyer": {
                "first_name": "James",
                "last_name": "Kirk",
                "email": "james.kirk@example.com"
            },
            "created_at": "2014-12-12",
            "payment_provider": "test-gateway [1.0.0]",
            "purchased_items": [
                {
                    "product_id": 1,
                    "quantity": 2,
                    "name": "Test Product",
                    "unit_price": "99.99",
                    "price_currency_code": "USD"
                }
            ]
        }

+ Response 200 (application/json)

        {}

## Un-matched Order Confirmation [/visits/order/unmatched/{m}]

+ Parameters

    + m (string) ... the account name for which this order was placed

### New account [POST]

+ Request (application/json)

        {
            "order_number": 1,
            "order_status_code": "complete",
            "order_status_label": "Complete",
            "buyer": {
                "first_name": "James",
                "last_name": "Kirk",
                "email": "james.kirk@example.com"
            },
            "created_at": "2014-12-12",
            "payment_provider": "test-gateway [1.0.0]",
            "purchased_items": [
                {
                    "product_id": 1,
                    "quantity": 2,
                    "name": "Test Product",
                    "unit_price": "99.99",
                    "price_currency_code": "USD"
                }
            ]
        }

+ Response 200 (application/json)

        {}

# Group Product
Product related resources

## Product Re-crawl [/products/recrawl]

### Send product re-crawl request [POST]

+ Request (application/json)

    + Headers

            Authorization: Basic OjAxMDk4ZDBmYzg0ZGVkN2M0MjI2ODIwZDVkMTIwN2M2OTI0M2NiYjM2MzdkYzRiYzJhMjE2ZGFmY2YwOWQ3ODM=

    + Body

            {
                "products": [
                    {
                        "product_id": 1,
                        "url": "http://my.shop.com/products/test_product.html"
                    }
                ]
            }

+ Response 200 (application/json)

        {}

## Product upsert [/v1/products/upsert]

### Send product create request [POST]

+ Request (application/json)

    + Headers

            Authorization: Basic OjAxMDk4ZDBmYzg0ZGVkN2M0MjI2ODIwZDVkMTIwN2M2OTI0M2NiYjM2MzdkYzRiYzJhMjE2ZGFmY2YwOWQ3ODM=

    + Body

            [
                {
                    "url": "http://my.shop.com/products/test_product.html",
                    "product_id": 1,
                    "name": "Test Product",
                    "image_url": "http://my.shop.com/images/test_product.jpg",
                    "thumb_url": "http://my.shop.com/images/thumbnails/test_product200x200.jpg",
                    "price": "99.99",
                    "list_price": "110.99",
                    "price_currency_code": "USD",
                    "availability": "InStock",
                    "tag1": ["tag1", "tag2"],
                    "categories": ["/a/b", "/a/b/c"],
                    "description": "Lorem ipsum dolor sit amet",
                    "brand": "Super Brand",
                    "date_published": "2013-01-05"
                }
            ]

+ Response 200 (application/json)

        {}

### Send product update request [POST]

+ Request (application/json)

    + Headers

            Authorization: Basic OjAxMDk4ZDBmYzg0ZGVkN2M0MjI2ODIwZDVkMTIwN2M2OTI0M2NiYjM2MzdkYzRiYzJhMjE2ZGFmY2YwOWQ3ODM=

    + Body

            [
                {
                    "url": "http://my.shop.com/products/test_product.html",
                    "product_id": 1,
                    "name": "Test Product",
                    "image_url": "http://my.shop.com/images/test_product.jpg",
                    "thumb_url": "http://my.shop.com/images/thumbnails/test_product200x200.jpg",
                    "price": "99.99",
                    "list_price": "110.99",
                    "price_currency_code": "USD",
                    "availability": "InStock",
                    "tag1": ["tag1", "tag2"],
                    "categories": ["/a/b", "/a/b/c"],
                    "description": "Lorem ipsum dolor sit amet",
                    "brand": "Super Brand",
                    "date_published": "2013-01-05"
                }
            ]

+ Response 200 (application/json)

        {}

## Product create [/v1/products/create]

### Send product create request [POST]

+ Request (application/json)

    + Headers

            Authorization: Basic OjAxMDk4ZDBmYzg0ZGVkN2M0MjI2ODIwZDVkMTIwN2M2OTI0M2NiYjM2MzdkYzRiYzJhMjE2ZGFmY2YwOWQ3ODM=

    + Body

            [
                {
                    "url": "http://my.shop.com/products/test_product.html",
                    "product_id": 1,
                    "name": "Test Product",
                    "image_url": "http://my.shop.com/images/test_product.jpg",
                    "thumb_url": "http://my.shop.com/images/thumbnails/test_product200x200.jpg",
                    "price": "99.99",
                    "list_price": "110.99",
                    "price_currency_code": "USD",
                    "availability": "InStock",
                    "tag1": ["tag1", "tag2"],
                    "categories": ["/a/b", "/a/b/c"],
                    "description": "Lorem ipsum dolor sit amet",
                    "brand": "Super Brand",
                    "date_published": "2013-01-05"
                }
            ]

+ Response 200 (application/json)

        {}

## Product update [/v1/products/update]

### Send product update request [PUT]

+ Request (application/json)

    + Headers

            Authorization: Basic OjAxMDk4ZDBmYzg0ZGVkN2M0MjI2ODIwZDVkMTIwN2M2OTI0M2NiYjM2MzdkYzRiYzJhMjE2ZGFmY2YwOWQ3ODM=

    + Body

            [
                {
                    "url": "http://my.shop.com/products/test_product.html",
                    "product_id": 1,
                    "name": "Test Product",
                    "image_url": "http://my.shop.com/images/test_product.jpg",
                    "thumb_url": "http://my.shop.com/images/thumbnails/test_product200x200.jpg",
                    "price": "99.99",
                    "list_price": "110.99",
                    "price_currency_code": "USD",
                    "availability": "InStock",
                    "tag1": ["tag1", "tag2"],
                    "categories": ["/a/b", "/a/b/c"],
                    "description": "Lorem ipsum dolor sit amet",
                    "brand": "Super Brand",
                    "date_published": "2013-01-05"
                }
            ]

+ Response 200 (application/json)

        {}

## Product delete [/v1/products/discontinue]

### Send product delete request [POST]

+ Request (application/json)

    + Headers

            Authorization: Basic OjAxMDk4ZDBmYzg0ZGVkN2M0MjI2ODIwZDVkMTIwN2M2OTI0M2NiYjM2MzdkYzRiYzJhMjE2ZGFmY2YwOWQ3ODM=

    + Body

            [
                1
            ]

+ Response 200 (application/json)

        {}

# Group Currency
Currency related resources

## Exchange rates [/exchangerates]

### Send exchange rate update request [POST]

+ Request (application/json)

    + Headers

            Authorization: Basic OjAxMDk4ZDBmYzg0ZGVkN2M0MjI2ODIwZDVkMTIwN2M2OTI0M2NiYjM2MzdkYzRiYzJhMjE2ZGFmY2YwOWQ3ODM=

    + Body

            {
                "rates": {
                    "EUR": {
                        "rate": "0.706700000000",
                        "price_currency_code": "EUR"
                    }
                },
                "valid_until": "2015-02-27T12:00:00Z"
            }

+ Response 200 (application/json)

        {}
