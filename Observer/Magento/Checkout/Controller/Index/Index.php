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

namespace Ineiman\TikTok\Observer\Magento\Checkout\Controller\Index;

use Ineiman\TikTok\Api\Data\TikTokInterface;
use Ineiman\TikTok\Api\TiktokManagementInterface;
use Ineiman\TikTok\Model\Config\ConfigProvider;
use Ineiman\TikTok\Model\Queue\Publisher;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Listens to controller_action_postdispatch_checkout_index_index, builds a TikTok ViewContent payload
 * and send it via API.
 */
class Index implements ObserverInterface
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $storeId = $this->tiktokManagement->getStoreId();
        $isFeTrackAllowed = $this->configProvider->isFeTrackAllowed(
            TikTokInterface::EVENT_NAME_INITIATE_CHECKOUT,
            $storeId
        );
        $isBeTrackAllowed = $this->configProvider->isBeTrackAllowed(
            TikTokInterface::EVENT_NAME_INITIATE_CHECKOUT,
            $storeId
        );
        if (!$isBeTrackAllowed) {
            return;
        }

        $payload = $this->tiktokManagement->getInitiateCheckoutEventPayload();
        if ($isFeTrackAllowed) {
            $this->tiktokManagement->setPayloadInCustomerData($payload, TikTokInterface::EVENT_KEY_INITIATE_CHECKOUT);
        }

        $this->publisher->publish($payload, $storeId);
    }
}
