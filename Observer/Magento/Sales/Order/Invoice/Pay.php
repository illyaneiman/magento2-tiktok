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

namespace Ineiman\TikTok\Observer\Magento\Sales\Order\Invoice;

use Ineiman\TikTok\Api\TiktokManagementInterface;
use Ineiman\TikTok\Model\Config\ConfigProvider;
use Ineiman\TikTok\Model\Queue\Publisher;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Listens to sales_order_invoice_pay, builds a TikTok Purchase payload and send it via API.
 */
class Pay implements ObserverInterface
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
     */
    public function execute(Observer $observer)
    {
        $isTrackEnabled = $this->config->isEventTrackAllowed(
            ConfigProvider::XML_PATH_GROUP_BACKEND,
            ConfigProvider::XML_PATH_FIELD_PURCHASE_ENABLED
        );

        if (!$isTrackEnabled) {
            return;
        }

        $invoice = $observer->getEvent()->getInvoice();
        $payload = $this->tiktokManagement->getPurchaseEventPayload($invoice);

        $this->publisher->publish($payload);
    }
}
