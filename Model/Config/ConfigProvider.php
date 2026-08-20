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
declare(strict_types=1);

namespace Ineiman\TikTok\Model\Config;

use Ineiman\TikTok\Api\Data\TikTokInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * TikTok config provider class
 */
class ConfigProvider
{
    /**#@+
     * System configurations paths
     */
    public const XML_PATH_IS_ENABLED = 'tiktok/general/enabled';
    public const XML_PATH_PIXEL_ID = 'tiktok/general/pixel_id';
    public const XML_PATH_API_KEY = 'tiktok/general/tiktok_api_key';
    public const XML_PATH_API_URL = 'tiktok/general/tiktok_api_url';
    public const XML_PATH_DEBUG = 'tiktok/general/debug';
    public const XML_PATH_FRONTEND_EVENTS_ENABLED = 'tiktok/frontend_events/enabled';
    public const XML_PATH_FE_ADD_TO_CART_ENABLED = 'tiktok/frontend_events/add_to_cart_enabled';
    public const XML_PATH_FE_UPDATE_CART_ENABLED = 'tiktok/frontend_events/update_cart_enabled';
    public const XML_PATH_FE_REMOVE_FROM_CART_ENABLED = 'tiktok/frontend_events/remove_from_cart_enabled';
    public const XML_PATH_FE_ADD_TO_WISHLIST_ENABLED = 'tiktok/frontend_events/add_to_wishlist_enabled';
    public const XML_PATH_FE_VIEW_CONTENT_ENABLED = 'tiktok/frontend_events/view_content_enabled';
    public const XML_PATH_FE_INITIATE_CHECKOUT_ENABLED = 'tiktok/frontend_events/initiate_checkout_enabled';
    public const XML_PATH_FE_ADD_PAYMENT_INFO_ENABLED = 'tiktok/frontend_events/add_payment_info_enabled';
    public const XML_PATH_FE_PLACE_AN_ORDER_ENABLED = 'tiktok/frontend_events/place_an_order_enabled';
    public const XML_PATH_FE_SEARCH_ENABLED = 'tiktok/frontend_events/search_enabled';
    public const XML_PATH_FE_COMPLETE_REGISTRATION_ENABLED = 'tiktok/frontend_events/complete_registration_enabled';
    public const XML_PATH_BACKEND_EVENTS_ENABLED = 'tiktok/backend_events/enabled';
    public const XML_PATH_BE_ADD_TO_CART_ENABLED = 'tiktok/backend_events/add_to_cart_enabled';
    public const XML_PATH_BE_UPDATE_CART_ENABLED = 'tiktok/backend_events/update_cart_enabled';
    public const XML_PATH_BE_REMOVE_FROM_CART_ENABLED = 'tiktok/backend_events/remove_from_cart_enabled';
    public const XML_PATH_BE_ADD_TO_WISHLIST_ENABLED = 'tiktok/backend_events/add_to_wishlist_enabled';
    public const XML_PATH_BE_REMOVE_FROM_WISHLIST_ENABLED = 'tiktok/backend_events/remove_form_wishlist_enabled';
    public const XML_PATH_BE_VIEW_CONTENT_ENABLED = 'tiktok/backend_events/view_content_enabled';
    public const XML_PATH_BE_INITIATE_CHECKOUT_ENABLED = 'tiktok/backend_events/initiate_checkout_enabled';
    public const XML_PATH_BE_ADD_PAYMENT_INFO_ENABLED = 'tiktok/backend_events/add_payment_info_enabled';
    public const XML_PATH_BE_PLACE_AN_ORDER_ENABLED = 'tiktok/backend_events/place_an_order_enabled';
    public const XML_PATH_BE_PURCHASE_ENABLED = 'tiktok/backend_events/purchase_enabled';
    public const XML_PATH_BE_SEARCH_ENABLED = 'tiktok/backend_events/search_enabled';
    public const XML_PATH_BE_COMPLETE_REGISTRATION_ENABLED = 'tiktok/backend_events/complete_registration_enabled';
    /**#@- */

    /**
     * Construct
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Check if module enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_IS_ENABLED, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Get Pixel Id from config
     *
     * @param int|string|null $storeId
     * @return string
     */
    public function getPixelId(int|string $storeId = null): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PIXEL_ID, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Get Api Key from config
     *
     * @param int|string|null $storeId
     * @return string
     */
    public function getApiKey(int|string $storeId = null): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_KEY, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Get Api Url from config
     *
     * @param int|string|null $storeId
     * @return string
     */
    public function getApiUrl(int|string $storeId = null): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_URL, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Check if debug enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isDebugEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_DEBUG, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Check if Frontend Events Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isFrontendEventsEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FRONTEND_EVENTS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if Frontend Events Allowed
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isFrontendEventsAllowed(int|string $storeId = null): bool
    {
        return $this->isEnabled($storeId) && $this->isFrontendEventsEnabled($storeId);
    }

    /**
     * Check if FE tracking for specific event allowed
     *
     * @param string $eventCode
     * @param int|string|null $storeId
     * @return bool
     */
    public function isFeTrackAllowed(string $eventCode, int|string $storeId = null): bool
    {
        $isEventTrackingAllowed = match ($eventCode) {
            TikTokInterface::EVENT_NAME_ADD_TO_CART => $this->isFeAddToCartEnabled($storeId),
            TikTokInterface::EVENT_NAME_UPDATE_CART => $this->isFeUpdateCartEnabled($storeId),
            TikTokInterface::EVENT_NAME_REMOVE_FROM_CART => $this->isFeRemoveFromCartEnabled($storeId),
            TikTokInterface::EVENT_NAME_ADD_TO_WISHLIST => $this->isFeAddToWishlistEnabled($storeId),
            TikTokInterface::EVENT_NAME_VIEW_CONTENT => $this->isFeViewContentEnabled($storeId),
            TikTokInterface::EVENT_NAME_INITIATE_CHECKOUT => $this->isFeInitiateCheckoutEnabled($storeId),
            TikTokInterface::EVENT_NAME_ADD_PAYMENT_INFO => $this->isFeAddPaymentInfoEnabled($storeId),
            TikTokInterface::EVENT_NAME_PLACE_AN_ORDER => $this->isFePlaceAnOrderEnabled($storeId),
            TikTokInterface::EVENT_NAME_SEARCH => $this->isFeSearchEnabled($storeId),
            TikTokInterface::EVENT_NAME_COMPLETE_REGISTRATION => $this->isFeCompleteRegistrationEnabled($storeId),
            default => false
        };

        return $this->isFrontendEventsAllowed($storeId) && $isEventTrackingAllowed;
    }

    /**
     * Check if FE event Add To Cart Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isFeAddToCartEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FE_ADD_TO_CART_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if FE event Update Cart Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isFeUpdateCartEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FE_UPDATE_CART_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if FE event Remove From Cart Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isFeRemoveFromCartEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FE_REMOVE_FROM_CART_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if FE event Add To Wishlist Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isFeAddToWishlistEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FE_ADD_TO_WISHLIST_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if FE event Add To Wishlist Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isFeViewContentEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FE_VIEW_CONTENT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if Fe event Add Payment Info Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isFeInitiateCheckoutEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FE_INITIATE_CHECKOUT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if Fe event Add Payment Info Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isFeAddPaymentInfoEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FE_ADD_PAYMENT_INFO_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if Fe event Place An Order Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isFePlaceAnOrderEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FE_PLACE_AN_ORDER_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if FE event Search Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isFeSearchEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FE_SEARCH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if Fe event Complete Registration Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isFeCompleteRegistrationEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_FE_COMPLETE_REGISTRATION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if Backend Events Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isBackendEventsEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BACKEND_EVENTS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if Backend Events Allowed
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isBackendEventsAllowed(int|string $storeId = null): bool
    {
        return $this->isEnabled($storeId) && $this->isBackendEventsEnabled($storeId);
    }

    /**
     * Check if BE tracking for specific event allowed
     *
     * @param string $eventCode
     * @param int|string|null $storeId
     * @return bool
     */
    public function isBeTrackAllowed(string $eventCode, int|string $storeId = null): bool
    {
        $isEventTrackingAllowed = match ($eventCode) {
            TikTokInterface::EVENT_NAME_ADD_TO_CART => $this->isBeAddToCartEnabled($storeId),
            TikTokInterface::EVENT_NAME_UPDATE_CART => $this->isBeUpdateCartEnabled($storeId),
            TikTokInterface::EVENT_NAME_REMOVE_FROM_CART => $this->isBeRemoveFromCartEnabled($storeId),
            TikTokInterface::EVENT_NAME_ADD_TO_WISHLIST => $this->isBeAddToWishlistEnabled($storeId),
            TikTokInterface::EVENT_NAME_REMOVE_FROM_WISHLIST => $this->isBeRemoveFromWishlistEnabled($storeId),
            TikTokInterface::EVENT_NAME_VIEW_CONTENT => $this->isBeViewContentEnabled($storeId),
            TikTokInterface::EVENT_NAME_INITIATE_CHECKOUT => $this->isBeInitiateCheckoutEnabled($storeId),
            TikTokInterface::EVENT_NAME_ADD_PAYMENT_INFO => $this->isBeAddPaymentInfoEnabled($storeId),
            TikTokInterface::EVENT_NAME_PLACE_AN_ORDER => $this->isBePlaceAnOrderEnabled($storeId),
            TikTokInterface::EVENT_NAME_PURCHASE => $this->isBePurchaseEnabled($storeId),
            TikTokInterface::EVENT_NAME_SEARCH => $this->isBeSearchEnabled($storeId),
            TikTokInterface::EVENT_NAME_COMPLETE_REGISTRATION => $this->isBeCompleteRegistrationEnabled($storeId),
            default => false
        };

        return $this->isBackendEventsAllowed($storeId) && $isEventTrackingAllowed;
    }

    /**
     * Check if BE event Add To Cart Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isBeAddToCartEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BE_ADD_TO_CART_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if BE event Update Cart Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isBeUpdateCartEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BE_UPDATE_CART_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if BE event Remove From Cart Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isBeRemoveFromCartEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BE_REMOVE_FROM_CART_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if BE event Add To Wishlist Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isBeAddToWishlistEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BE_ADD_TO_WISHLIST_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if BE event Remove From Wishlist Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isBeRemoveFromWishlistEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BE_REMOVE_FROM_WISHLIST_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if BE event View Content Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isBeViewContentEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BE_VIEW_CONTENT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if BE event View Content Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isBeInitiateCheckoutEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BE_INITIATE_CHECKOUT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if BE event Add Payment Info Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isBeAddPaymentInfoEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BE_ADD_PAYMENT_INFO_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if BE event Place An Order Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isBePlaceAnOrderEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BE_PLACE_AN_ORDER_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if BE event Place An Order Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isBePurchaseEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BE_PURCHASE_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if BE event Search Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isBeSearchEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BE_SEARCH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if BE event Complete Registration Enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isBeCompleteRegistrationEnabled(int|string $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BE_COMPLETE_REGISTRATION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
