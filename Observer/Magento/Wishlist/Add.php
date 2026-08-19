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

namespace Ineiman\TikTok\Observer\Magento\Wishlist;

use Ineiman\TikTok\Api\Data\TikTokInterface;
use Ineiman\TikTok\Api\TiktokManagementInterface;
use Ineiman\TikTok\Model\Config\ConfigProvider;
use Ineiman\TikTok\Model\Queue\Publisher;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Listens to wishlist_add_product (dispatched by Magento\Wishlist\Controller\Index\Add::execute()) and builds a TikTok
 * AddToWishlist payload.
 * For FE Event the payload is stashed in dataPersistor so the "ineiman_tiktok_add_to_wishlist" customer-data
 * section can hand it off to the storefront JS on the next section reload - which Magento triggers automatically after
 * every wishlist/index/add request (see etc/frontend/sections.xml).
 *
 * For BE Event just send payload directly via API
 */
class Add implements ObserverInterface
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
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $storeId = $this->tiktokManagement->getStoreId();
        $isFeTrackAllowed = $this->configProvider->isFeTrackAllowed(
            TikTokInterface::EVENT_NAME_ADD_TO_WISHLIST,
            $storeId
        );
        $isBeTrackAllowed = $this->configProvider->isBeTrackAllowed(
            TikTokInterface::EVENT_NAME_ADD_TO_WISHLIST,
            $storeId
        );
        if (!$isFeTrackAllowed && !$isBeTrackAllowed) {
            return;
        }

        $product = $observer->getEvent()->getProduct();
        if (!$product || !$product->getId()) {
            return;
        }

        $payload = $this->tiktokManagement->getAddToWishlistEventPayload($product);
        if ($isFeTrackAllowed) {
            $this->tiktokManagement->setPayloadInCustomerData($payload, TikTokInterface::EVENT_KEY_ADD_TO_WISHLIST);
        }

        if ($isBeTrackAllowed) {
            $this->publisher->publish($payload, $storeId);
        }
    }
}
