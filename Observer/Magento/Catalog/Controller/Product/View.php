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

namespace Ineiman\TikTok\Observer\Magento\Catalog\Controller\Product;

use Ineiman\TikTok\Api\Data\TikTokInterface;
use Ineiman\TikTok\Api\TiktokManagementInterface;
use Ineiman\TikTok\Model\Config\ConfigProvider;
use Ineiman\TikTok\Model\Queue\Publisher;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Listens to controller_action_postdispatch_catalog_product_view, builds a TikTok ViewContent payload
 * and send it via API.
 */
class View implements ObserverInterface
{
    /**
     * Construct
     *
     * @param TiktokManagementInterface $tiktokManagement
     * @param ConfigProvider $config
     * @param Publisher $publisher
     */
    public function __construct(
        private readonly TiktokManagementInterface $tiktokManagement,
        private readonly ConfigProvider $config,
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
        $isFeTrackAllowed = $this->config->isEventTrackAllowed(
            ConfigProvider::XML_PATH_GROUP_FRONTEND,
            ConfigProvider::XML_PATH_FIELD_VIEW_CONTENT_ENABLED
        );
        $isBeTrackAllowed = $this->config->isEventTrackAllowed(
            ConfigProvider::XML_PATH_GROUP_BACKEND,
            ConfigProvider::XML_PATH_FIELD_VIEW_CONTENT_ENABLED
        );

        if (!$isBeTrackAllowed) {
            return;
        }

        $payload = $this->tiktokManagement->getViewContentEventPayload();
        if ($isFeTrackAllowed) {
            $this->tiktokManagement->setPayloadInCustomerData($payload, TikTokInterface::EVENT_KEY_VIEW_CONTENT);
        }

        $this->publisher->publish($payload);
    }
}
