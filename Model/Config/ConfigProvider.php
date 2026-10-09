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
    public const XML_PATH_GROUP_FRONTEND = 'tiktok/frontend_events/';
    public const XML_PATH_GROUP_BACKEND = 'tiktok/backend_events/';
    public const XML_PATH_FIELD_ENABLED = 'enabled';
    public const XML_PATH_FIELD_ADD_TO_CART_ENABLED = 'add_to_cart_enabled';
    public const XML_PATH_FIELD_UPDATE_CART_ENABLED = 'update_cart_enabled';
    public const XML_PATH_FIELD_REMOVE_FROM_CART_ENABLED = 'remove_from_cart_enabled';
    public const XML_PATH_FIELD_ADD_TO_WISHLIST_ENABLED = 'add_to_wishlist_enabled';
    public const XML_PATH_FIELD_REMOVE_FROM_WISHLIST_ENABLED = 'remove_form_wishlist_enabled';
    public const XML_PATH_FIELD_VIEW_CONTENT_ENABLED = 'view_content_enabled';
    public const XML_PATH_FIELD_INITIATE_CHECKOUT_ENABLED = 'initiate_checkout_enabled';
    public const XML_PATH_FIELD_ADD_PAYMENT_INFO_ENABLED = 'add_payment_info_enabled';
    public const XML_PATH_FIELD_PLACE_AN_ORDER_ENABLED = 'place_an_order_enabled';
    public const XML_PATH_FIELD_PURCHASE_ENABLED = 'purchase_enabled';
    public const XML_PATH_FIELD_SEARCH_ENABLED = 'search_enabled';
    public const XML_PATH_COMPLETE_REGISTRATION_ENABLED = 'complete_registration_enabled';
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
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_IS_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Pixel Id from config
     *
     * @return string
     */
    public function getPixelId(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PIXEL_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Api Key from config
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_KEY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Api Url from config
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_URL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if debug mode enabled
     *
     * @return bool
     */
    public function isDebugEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_DEBUG, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if Specific Event Group (FE or BE) Enabled
     *
     * @param string $group
     * @return bool
     */
    private function isEventGroupEnabled(string $group): bool
    {
        return $this->scopeConfig->isSetFlag($group . self::XML_PATH_FIELD_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check if tracking for specific event allowed
     *
     * @param string $group
     * @param string $field
     * @return bool
     */
    public function isEventTrackAllowed(string $group, string $field): bool
    {
        $isEnabled = $this->scopeConfig->isSetFlag($group . $field, ScopeInterface::SCOPE_STORE);

        return $this->isEnabled() && $this->isEventGroupEnabled($group) && $isEnabled;
    }
}
