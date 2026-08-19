<?php
/**
 * Illia Neiman
 *
 * NOTICE OF LICENSE
 *
 * According to LICENCE file you are not allowed to copy, use or recreate this file in any ways.
 * Specially for eCommerce usage.
 *
 * @category Ineiman
 * @package TikTok
 * @copyright Copyright (c) 2021-current Ineiman (https://github.com/illyaneiman)
 * @email kg.illya.ney@gmail.com
 */

namespace Ineiman\TikTok\Api\Data;

/**
 * TikTok data interface
 */
interface TikTokInterface
{
    /**#@+
     * Constants for event names
     */
    public const EVENT_NAME_ADD_TO_CART = 'AddToCart';
    public const EVENT_NAME_UPDATE_CART = 'UpdateCart';
    public const EVENT_NAME_REMOVE_FROM_CART = 'RemoveFromCart';
    public const EVENT_NAME_ADD_TO_WISHLIST = 'AddToWishlist';
    public const EVENT_NAME_REMOVE_FROM_WISHLIST = 'RemoveFromWishlist';
    public const EVENT_NAME_VIEW_CONTENT = 'ViewContent';
    public const EVENT_NAME_INITIATE_CHECKOUT = 'InitiateCheckout';
    public const EVENT_NAME_ADD_PAYMENT_INFO = 'AddPaymentInfo';
    public const EVENT_NAME_PLACE_AN_ORDER = 'PlaceAnOrder';
    public const EVENT_NAME_PURCHASE = 'Purchase';
    public const EVENT_NAME_SEARCH = 'Search';
    public const EVENT_NAME_COMPLETE_REGISTRATION = 'CompleteRegistration';
    /**#@-*/

    /**#@+
     * Constants for content types
     */
    public const CONTENT_TYPE_PRODUCT  = 'product';
    public const CONTENT_TYPE_PRODUCT_GROUP  = 'product_group';
    /**#@-*/

    /**
     * Constant for default currency code
     */
    public const DEFAULT_CURRENCY_CODE = 'USD';

    /**#@+
     * Constants for event keys similar to section name and keys to store payloads in dataPersistor
     */
    public const EVENT_KEY_ADD_TO_CART = 'ineiman_tiktok_add_to_cart';
    public const EVENT_KEY_REMOVE_FROM_CART = 'ineiman_tiktok_remove_from_cart';
    public const EVENT_KEY_UPDATE_CART = 'ineiman_tiktok_update_cart';
    public const EVENT_KEY_ADD_TO_WISHLIST = 'ineiman_tiktok_add_to_wishlist';
    public const EVENT_KEY_VIEW_CONTENT = 'view_content_payload';
    public const EVENT_KEY_INITIATE_CHECKOUT = 'initiate_checkout_payload';
    public const EVENT_KEY_ADD_PAYMENT_INFO = 'ineiman_tiktok_add_payment_info';
    public const EVENT_KEY_PLACE_AN_ORDER = 'ineiman_tiktok_place_an_order';
    public const EVENT_KEY_SEARCH = 'search_payload';
    public const EVENT_KEY_COMPLETE_REGISTRATION = 'ineiman_tiktok_complete_registration';
    /**#@-*/
}
