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

namespace Ineiman\TikTok\Observer\Magento\Customer\Registration;

use Ineiman\TikTok\Api\Data\TikTokInterface;
use Ineiman\TikTok\Api\TiktokManagementInterface;
use Ineiman\TikTok\Model\Config\ConfigProvider;
use Ineiman\TikTok\Model\Queue\Publisher;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Listens to customer_register_success (\Magento\Customer\Controller\Account\CreatePost::execute()) and builds
 * a TikTok CompleteRegistration payload.
 *
 * For FE Event the payload is stashed in the dataPersistor so the "ineiman_tiktok_complete_registration"
 * customer-data section can hand it off to the storefront JS on the very next section reload - which Magento triggers
 * automatically (see etc/frontend/sections.xml).
 *
 * For BE Event just send payload directly via API
 */
class Success implements ObserverInterface
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
            TikTokInterface::EVENT_NAME_COMPLETE_REGISTRATION,
            $storeId
        );
        $isBeTrackAllowed = $this->configProvider->isBeTrackAllowed(
            TikTokInterface::EVENT_NAME_COMPLETE_REGISTRATION,
            $storeId
        );
        if (!$isFeTrackAllowed && !$isBeTrackAllowed) {
            return;
        }

        $customer = $observer->getEvent()->getCustomer();
        if (!$customer || !$customer->getId()) {
            return;
        }

        $payload = $this->tiktokManagement->getCompleteRegistrationEventPayload($customer);
        if ($isFeTrackAllowed) {
            $this->tiktokManagement->setPayloadInCustomerData(
                $payload,
                TikTokInterface::EVENT_KEY_COMPLETE_REGISTRATION
            );
        }

        if ($isBeTrackAllowed) {
            $this->publisher->publish($payload, $storeId);
        }
    }
}
