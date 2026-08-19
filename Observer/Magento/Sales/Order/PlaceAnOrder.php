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

namespace Ineiman\TikTok\Observer\Magento\Sales\Order;

use Ineiman\TikTok\Api\Data\TikTokInterface;
use Ineiman\TikTok\Api\TiktokManagementInterface;
use Ineiman\TikTok\Model\Config\ConfigProvider;
use Ineiman\TikTok\Model\Queue\Publisher;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Listens to sales_order_place_after and builds a TikTok PlaceAnOrder payload.
 *
 * For FE Event the payload is stashed in the dataPersistor so the "ineiman_tiktok_add_to_cart"
 * customer-data section can hand it off to the storefront JS on the very next section reload - which Magento triggers
 * automatically after every checkout/cart/add request, ajax or not (see etc/frontend/sections.xml).
 *
 * For BE Event just send payload directly via API
 */
class PlaceAnOrder implements ObserverInterface
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
            TikTokInterface::EVENT_NAME_PLACE_AN_ORDER,
            $storeId
        );
        $isBeTrackAllowed = $this->configProvider->isBeTrackAllowed(
            TikTokInterface::EVENT_NAME_PLACE_AN_ORDER,
            $storeId
        );
        if (!$isBeTrackAllowed) {
            return;
        }

        $order = $observer->getEvent()->getOrder();
        if (!$order) {
            return;
        }

        $payload = $this->tiktokManagement->getPlaceAnOrderEventPayload($order);
        if ($isFeTrackAllowed) {
            $this->tiktokManagement->setPayloadInCustomerData($payload, TikTokInterface::EVENT_KEY_PLACE_AN_ORDER);
        }

        $this->publisher->publish($payload, $storeId);
    }
}
