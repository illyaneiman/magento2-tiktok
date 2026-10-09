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

namespace Ineiman\TikTok\Plugin\Magento\Quote\Model;

use Ineiman\TikTok\Api\Data\TikTokInterface;
use Ineiman\TikTok\Api\TiktokManagementInterface;
use Ineiman\TikTok\Logger\Logger;
use Ineiman\TikTok\Model\Config\ConfigProvider;
use Ineiman\TikTok\Model\Queue\Publisher;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\GuestPaymentMethodManagementInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;

/**
 * Plugin class to trigger AddPaymentInfo payload generation and send payload to the TikTok
 */
class PaymentMethodManagement
{
    /**
     * @var string|null
     */
    private ?string $oldMethod = null;

    /**
     * Construct
     *
     * @param TiktokManagementInterface $tiktokManagement
     * @param Logger $logger
     * @param ConfigProvider $config
     * @param Publisher $publisher
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        private readonly TiktokManagementInterface $tiktokManagement,
        private readonly Logger $logger,
        private readonly ConfigProvider $config,
        private readonly Publisher $publisher,
        private readonly CartRepositoryInterface $quoteRepository
    ) {
    }

    /**
     * Before plugin for set payment method.
     *
     * @param PaymentMethodManagementInterface|GuestPaymentMethodManagementInterface $subject
     * @param int|string $cartId
     * @param PaymentInterface $method
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSet(
        PaymentMethodManagementInterface|GuestPaymentMethodManagementInterface $subject,
        int|string $cartId,
        PaymentInterface $method
    ) {
        $isFeTrackAllowed = $this->config->isEventTrackAllowed(
            ConfigProvider::XML_PATH_GROUP_FRONTEND,
            ConfigProvider::XML_PATH_FIELD_ADD_PAYMENT_INFO_ENABLED
        );
        $isBeTrackAllowed = $this->config->isEventTrackAllowed(
            ConfigProvider::XML_PATH_GROUP_BACKEND,
            ConfigProvider::XML_PATH_FIELD_ADD_PAYMENT_INFO_ENABLED
        );

        if (!$isFeTrackAllowed && !$isBeTrackAllowed || (int)$cartId <= 0) {
            return [$cartId, $method];
        }

        /**
         * Save the old method before Magento changes it.
         */
        try {
            $quote = $this->quoteRepository->get((int)$cartId);
        } catch (NoSuchEntityException $e) {
            $this->logger->log('Ineiman_Tiktok could not load quote for Add Payment Event  ' . $e->getMessage());
            $this->logger->log($e->getTraceAsString());
            return [$cartId, $method];
        }

        $this->oldMethod = $quote->getPayment()->getMethod();

        return [$cartId, $method];
    }

    /**
     * After plugin for set payment method
     *
     * @param PaymentMethodManagementInterface|GuestPaymentMethodManagementInterface $subject
     * @param mixed $result
     * @param int|string $cartId
     * @param PaymentInterface $method
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSet(
        PaymentMethodManagementInterface|GuestPaymentMethodManagementInterface $subject,
        mixed $result,
        int|string $cartId,
        PaymentInterface $method
    ) {
        $newMethod = $method->getMethod();
        $isFeTrackAllowed = $this->config->isEventTrackAllowed(
            ConfigProvider::XML_PATH_GROUP_FRONTEND,
            ConfigProvider::XML_PATH_FIELD_ADD_PAYMENT_INFO_ENABLED
        );
        $isBeTrackAllowed = $this->config->isEventTrackAllowed(
            ConfigProvider::XML_PATH_GROUP_BACKEND,
            ConfigProvider::XML_PATH_FIELD_ADD_PAYMENT_INFO_ENABLED
        );

        /**
         * Don't send when Magento sets the same method again, e.g. during payment-information processing. Also, magento
         * triggers set method twice (for masked cartId and original cartId) so just skip masked quoteId to avoid
         * duplication events send.
         */
        if (!$isFeTrackAllowed && !$isBeTrackAllowed
            || $newMethod === ''
            || $this->oldMethod === $newMethod
            || (int)$cartId <= 0
        ) {
            return $result;
        }

        $payload = $this->tiktokManagement->getAddPaymentInfoEventPayload($method, $this->oldMethod);
        if ($isFeTrackAllowed) {
            $this->tiktokManagement->setPayloadInCustomerData($payload, TikTokInterface::EVENT_KEY_ADD_PAYMENT_INFO);
        }

        if ($isBeTrackAllowed) {
            $this->publisher->publish($payload);
        }

        return $result;
    }
}
