# Ineiman_TikTok Module for Magento2

[![Ineiman TikTok](https://img.shields.io/badge/version-2.4.7.0-green.svg)](https://github.com/illyaneiman/magento2-tiktok.git)
[![Package](https://img.shields.io/badge/package-2.4.7.0-blue.svg)](https://github.com/illyaneiman/magento2-tiktok.git)

This module allows you to track TikTok events.

## Overview
Manage and track TikTok events at your Magento2 store.
You could track both: FE and BE events.

## Features
* TikTok Pixel (frontend) events tracking:
  * AddToCart
  * UpdateCart
  * RemoveFromCart
  * AddToWishlist
  * AddPaymentInfo
  * InitiateCheckout
  * PlaceAnOrder
  * ViewContent
  * Search
  * CompleteRegistration
* TikTok API (backend) events tracking:
  * AddToCart
  * UpdateCart
  * RemoveFromCart
  * AddToWishlist
  * AddPaymentInfo
  * InitiateCheckout
  * PlaceAnOrder
  * ViewContent
  * Search
  * CompleteRegistration

# User Manual
## Usage in Admin Panel
- Go to Admin Panel -> Stores -> Configuration -> Ineiman -> TikTok
- Enable module
- Fill 'Pixel ID' field with your Pixel ID if you want to use FE events
- Fill 'TikTok API Key' with your generated API key if you to use BE events
- 'TikTok API Url' should be filled automatically. If not - check TikTok docs and find which url used for API (backend) events 
- Enable debug mode if you want to log error data (if any appear)
- Enable event types (FE, BE or both of them) which you want to track
- Enable events you want to track
- Save configuration and clean the cache

## Debug mode
Enable this mode if you want any errors(exceptions) which could appear in module's workaround were saved to the custom log file. If this mode disabled exceptions could not being logged. If mode enabled and any exception happened you could find custom log file with logged data `/var/log/tiktok/debug.log`.

## Deduplication
Deduplication was implemented for each event which has FE and BE tracking (for example: 'AddToCart'). You could enable both FE and BE tracking - TikTok will receive both of them but will combine it in one event for statistic.

## Usage at storefront
- Go to your website page and trigger events by clicking on the related buttons (for AddToCart, AddToWishlist and similar events) or meet some requirements (for example: place an order to trigger PlaceAnOrder event)
- Check your TikTok account - events should be received (there are can be a delay ~30-60 minutes)
- Also, you could catch events at your storefront by using official google-chrome extension https://chromewebstore.google.com/detail/tiktok-pixel-helper/aelgobmabdmlfmiblddjfnjodalhidnn

## TikTok Pixel (frontend) events realization and additional info:
List of the events which could be tracked directly from FE.
- `Ineiman\TikTok\view\frontend\templates\main.phtml` - main phtml just to render all nested TikTok blocks.
- `Ineiman\TikTok\view\frontend\templates\script-tiktok.phtml` - phtml with main TikTok script which initialize TikTok tracking at FE. Also, includes script to identify customer.

### AddToCart, UpdateCart, RemoveFromCart, AddToWishlist, AddPaymentInfo, CompleteRegistration
These events have similar logic and use same files (at most cases) to pass data or track event.
It was made in such way with same files to lower the number of created Classes with same logic.
Also, each event related to new customerData section which reloads for each event in different situations.

```
AddToCart - event fires after product was added to the cart.
UpdateCart - event fires after cart data was updated.
RemoveFromCart - event fires after product was removed from the cart.
AddToWishlist- event fires after product was added to the wishlist.
AddPaymentInfo - event fires after payment method was selected or changed at checkout.
CompleteRegistration - event fires after customer complete registration.
```

#### CustomerData sections
1. `Ineiman\TikTok\etc\frontend\di.xml` - at this di.xml file you could find new customerData sections passed into `Magento\Customer\CustomerData\SectionPoolInterface`.
2. `Ineiman\TikTok\etc\frontend\sections.xml` - describes when each new customerData section should be reloaded and what action they are related.
3. `Ineiman\TikTok\CustomerData\AddToCart` - class to pass AddToCart event data into customerData with further retrieval and tracking via script.
4. `Ineiman\TikTok\CustomerData\UpdateCart` - class to pass UpdateCart event data into customerData with further retrieval and tracking via script.
5. `Ineiman\TikTok\CustomerData\RemoveFromCart` - class to pass RemoveFromCart event data into customerData with further retrieval and tracking via script.
6. `Ineiman\TikTok\CustomerData\AddToWishlist` - class to pass AddToWishlist event data into customerData with further retrieval and tracking via script.
7. `Ineiman\TikTok\CustomerData\AddPaymentInfo` - class to pass AddPaymentInfo event data into customerData with further retrieval and tracking via script.
8. `Ineiman\TikTok\CustomerData\CompleteRegistration` - class to pass CompleteRegistration event data into customerData with further retrieval and tracking via script.

#### Files to render and track events
1. `Ineiman\TikTok\view\frontend\layout\default_head_blocks.xml` - main xml file where blocks for each event renders at FE. Each block related to enabled configuration for each event. So only enabled events will be rendered. Also, unique arguments passed to each block to simplify detection of which event should be tracked and where to get data in script.
2. `Ineiman\TikTok\view\frontend\templates\track\tiktok\events-section-tracking.phtml` - main phtml file which renders script to track each event.
3. `Ineiman\TikTok\view\frontend\web\js\track\events-section.js` - script file to track each of this event.

#### Observers
1. `Ineiman\TikTok\Observer\Magento\Checkout\Cart\ProductAddAfter.php` - observer to create AddToCart event payload and pass it to customerData section class.
2. `Ineiman\TikTok\Observer\Magento\Checkout\Cart\UpdateItemsAfter.php` - observer to create UpdateCart event payload and pass it to customerData section class.
3. `Ineiman\TikTok\Observer\Magento\Sales\Quote\RemoveItem.php` - observer to create RemoveFromCart event payload and pass it to customerData section class.
4. `Ineiman\TikTok\Observer\Magento\Wishlist\Add.php` - observer to create AddToWishlist event payload and pass it to customerData section class.
5. `Ineiman\TikTok\Observer\Magento\Customer\Registration\Success` - observer to create CompleteRegistration event payload and pass it to customerData section class.

#### Plugins
1. `Ineiman\TikTok\Plugin\Quote\Model\PaymentMethodManagement` - plugin to create AddPaymentInfo event payload and pass it to customerData section class.

### ViewContent, InitiateCheckout, PlaceAnOrder, Search
These events have similar logic and some same files to simplify implementation and not overload module with files.

```
ViewContent - event fires when customer loads PDP of any product.
InitiateCheckout - event tracks checkout initialization. On other words it triggered when customer enters the checkout page.
PlaceAnOrder - event tracks successful order placement at checkout success page.
Search - event tracks when customer search something via search or advanced search. Take in mind FE event fires only when search_result page is loaded.
```

#### Files to render and track event
1. `Ineiman\TikTok\view\frontend\templates\track\tiktok\events-direct-tracking.phtml` - phtml file to fire an event tracking. One phtml for all events.
2. `Ineiman\TikTok\view\frontend\layout\catalog_product_view.xml` - xml file where block for ViewContent event added.
3. `Ineiman\TikTok\view\frontend\layout\checkout_index_index.xml` - xml file where block for InitiateCheckout event added.
4. `Ineiman\TikTok\view\frontend\layout\checkout_onepage_success.xml` - xml file where block for PlaceAnOrder event added.
5. `Ineiman\TikTok\view\frontend\layout\catalogsearch_result_index.xml` - xml file where block for Search event added.
6. `Ineiman\TikTok\view\frontend\layout\catalogsearch_advanced_result.xml` - xml file where block for Search event added.


## TikTok API (backend) events realization and additional info:
List of the events which could be tracked via backend.

### Event Sender
`Ineiman\TikTok\Model\Event\Sender.php` - main logic to send events payload to the TikTok API with help of Guzzle Client.

### AddToCart
BE event triggered after product was added to the cart.

#### Observer
1. `Ineiman\TikTok\Observer\Magento\Checkout\Cart\ProductAddAfter.php` - same observer as for FE event but in BE case send event data directly to TikTok API.

### UpdateCart
BE event triggered after cart was updated.

#### Observer
1. `Ineiman\TikTok\Observer\Magento\Checkout\Cart\UpdateItemsAfter.php` - same observer as for FE event but in BE case send event data directly to TikTok API.

### RemoveFromCart
BE event triggered after item was removed from cart.

#### Observer
1. `Ineiman\TikTok\Observer\Magento\Sales\Quote\RemoveItem.php` - same observer as for FE event but in BE case send event data directly to TikTok API.

### AddToWishlist
BE event triggered after item added to the wishlist.

#### Observer
1. `Ineiman\TikTok\Observer\Magento\Wishlist\Add.php` - same observer as for FE event but in BE case send event data directly to TikTok API.

### RemoveFromWishlist
BE event triggered after item was removed from the wishlist.

#### Observer
1. `Ineiman\TikTok\Observer\Magento\Wishlist\Remove.php` - logic to retrieve product from deleted wishlist item and send event with product`s data via TikTok API.

### ViewContent
BE event triggered when customer view a PDP.

#### Observer
1. `Ineiman\TikTok\Observer\Magento\Catalog\Controller\Product\View.php` - logic to retrieve product data on product`s PDP page and send event via TikTok API.

### InitiateCheckout
BE event triggered when customer go to the Checkout page.

#### Observer
1. `Ineiman\TikTok\Observer\Magento\Checkout\Controller\Index\Index` - logic to retrieve quote data and send event via TikTok API.

### AddPaymentInfo
BE event triggered when customer choose or change payment method on checkout

#### Plugin
1. `Ineiman\TikTok\Plugin\Quote\Model\PaymentMethodManagement` - same plugin as for FE event but in BE case send event data directly to TikTok API.

### PlaceAnOrder
BE event triggered after customer place an order.

#### Observer
1. `Ineiman\TikTok\Observer\Magento\Sales\Order\PlaceAnOrder` - logic to retrieve order data and send event via TikTok API.

### Purchase
BE event triggered when invoice is paid.

#### Observer
1. `Ineiman\TikTok\Observer\Magento\Sales\Order\Invoice\Pay` - logic to retrieve invoice data and send event via TikTok API.

### Search
BE event triggered when customer loads search result page.

#### Plugin
1. `Ineiman\TikTok\Plugin\Magento\CatalogSearch\Controller\Result\Index` - logic to retrieve search string data and send event via TikTok API.

### CompleteRegistration
BE event triggered after success customer registration.

#### Observer
1. `Ineiman\TikTok\Observer\Magento\Customer\Registration\Success` - same observer as for FE event but in BE case send event data directly to TikTok API.
