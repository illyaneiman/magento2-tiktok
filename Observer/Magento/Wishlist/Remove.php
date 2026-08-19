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
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Listens to wishlist_item_delete_after, builds a TikTok RemoveFromWishlist payload and send it via API.
 */
class Remove implements ObserverInterface
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
        $isBeTrackAllowed = $this->configProvider->isBeTrackAllowed(
            TikTokInterface::EVENT_NAME_REMOVE_FROM_WISHLIST,
            $storeId
        );
        if (!$isBeTrackAllowed) {
            return;
        }

        $item = $observer->getEvent()->getDataObject();
        if (!$item) {
            return;
        }

        $product = $item->getProduct();
        if (!$product || !$product->getId()) {
            return;
        }

        $payload = $this->tiktokManagement->getRemoveFromWishlistEventPayload($product);
        $this->publisher->publish($payload, $storeId);
    }
}
