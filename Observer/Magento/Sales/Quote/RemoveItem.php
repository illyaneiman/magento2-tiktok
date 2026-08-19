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

namespace Ineiman\TikTok\Observer\Magento\Sales\Quote;

use Ineiman\TikTok\Api\Data\TikTokInterface;
use Ineiman\TikTok\Api\TiktokManagementInterface;
use Ineiman\TikTok\Model\Config\ConfigProvider;
use Ineiman\TikTok\Model\Queue\Publisher;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Listens to sales_quote_remove_item (dispatched by Magento\Quote\Model\Quote::removeItem()) and builds a TikTok
 * RemoveFromCart payload.
 *
 * For FE Event the payload is stashed in dataPersistor so the "ineiman_tiktok_remove_from_cart"
 * customer-data section can hand it off to the storefront JS on the next section reload - which Magento triggers
 * automatically after every removeItem request (see etc/frontend/sections.xml).
 *
 * For BE Event just send payload directly via API
 */
class RemoveItem implements ObserverInterface
{
    /**
     * Construct
     *
     * @param TiktokManagementInterface $tiktokManagement
     * @param ConfigProvider $configProvider
     * @param Publisher $publisher
     */
    public function __construct(
        private readonly TiktokManagementInterface $tiktokManagement,
        private readonly ConfigProvider $configProvider,
        private readonly Publisher $publisher
    ) {
    }

    /**
     * Execute observer action
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $storeId = $this->tiktokManagement->getStoreId();
        $isFeTrackAllowed = $this->configProvider->isFeTrackAllowed(
            TikTokInterface::EVENT_NAME_REMOVE_FROM_CART,
            $storeId
        );
        $isBeTrackAllowed = $this->configProvider->isBeTrackAllowed(
            TikTokInterface::EVENT_NAME_REMOVE_FROM_CART,
            $storeId
        );
        if (!$isFeTrackAllowed && !$isBeTrackAllowed) {
            return;
        }

        $item = $observer->getEvent()->getQuoteItem();
        if (!$item || !$item->getId()) {
            return;
        }

        $payload = $this->tiktokManagement->getRemoveFromCartEventPayload($item);
        if ($isFeTrackAllowed) {
            $this->tiktokManagement->setPayloadInCustomerData($payload, TikTokInterface::EVENT_KEY_REMOVE_FROM_CART);
        }

        if ($isBeTrackAllowed) {
            $this->publisher->publish($payload, $storeId);
        }
    }
}
