2.4.7.1
=============
Changes:
* Reworked approach for retrieving config data
* Code fix for new config approach
* Code simplification
* Compatibility with Magento 2.4.8 version
* Fixed issues related to cached data
* Fixed typos in module files

2.4.7.0
=============
* New features:
    * Added TikTok Pixel (frontend) events tracking:
        * AddToCart
        * UpdateCart
        * RemoveFromCart
        * AddToWishlist
        * ViewContent
        * InitiateCheckout
        * AddPaymentInfo
        * PlaceAnOrder
        * Search
        * CompleteRegistration
    * Added TikTok API (backend) events tracking:
        * AddToCart
        * UpdateCart
        * RemoveFromCart
        * AddToWishlist
        * RemoveFromWishlist
        * ViewContent
        * InitiateCheckout
        * AddPaymentInfo
        * PlaceAnOrder
        * Purchase
        * Search
        * CompleteRegistration
    * Implemented deduplication for:
        * AddToCart
        * UpdateCart
        * RemoveFromCart
        * AddToWishlist
        * ViewContent
        * InitiateCheckout
        * AddPaymentInfo
        * PlaceAnOrder
        * Search
        * CompleteRegistration
