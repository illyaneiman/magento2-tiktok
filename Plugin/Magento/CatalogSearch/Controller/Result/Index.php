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

namespace Ineiman\TikTok\Plugin\Magento\CatalogSearch\Controller\Result;

use Ineiman\TikTok\Api\Data\TikTokInterface;
use Ineiman\TikTok\Api\TiktokManagementInterface;
use Ineiman\TikTok\Model\Config\ConfigProvider;
use Ineiman\TikTok\Model\Queue\Publisher;
use Magento\CatalogSearch\Controller\Result\Index as OriginalClass;

/**
 * Plugin class to trigger Search payload generation and send payload to the TikTok
 */
class Index
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
     * After execute plugin method
     *
     * @param OriginalClass $subject
     * @return void
     */
    public function afterExecute(OriginalClass $subject)
    {
        $storeId = $this->tiktokManagement->getStoreId();
        $isFeTrackAllowed = $this->configProvider->isFeTrackAllowed(TikTokInterface::EVENT_NAME_SEARCH, $storeId);
        $isBeTrackAllowed = $this->configProvider->isBeTrackAllowed(TikTokInterface::EVENT_NAME_SEARCH, $storeId);
        if (!$isBeTrackAllowed) {
            return;
        }

        $payload = $this->tiktokManagement->getSearchEventPayload();
        if ($isFeTrackAllowed) {
            $this->tiktokManagement->setPayloadInCustomerData($payload, TikTokInterface::EVENT_KEY_SEARCH);
        }

        $this->publisher->publish($payload, $storeId);
    }
}
