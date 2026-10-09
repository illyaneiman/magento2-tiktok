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

namespace Ineiman\TikTok\Model\Management;

use Ineiman\TikTok\Api\Data\TikTokInterface;
use Ineiman\TikTok\Logger\Logger;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Additional class for management to retrieve data
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Data
{
    /**
     * Construct
     *
     * @param Logger $logger
     * @param CheckoutSession $checkoutSession
     * @param Random $random
     * @param QueryFactory $queryFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly Logger $logger,
        private readonly CheckoutSession $checkoutSession,
        private readonly Random $random,
        private readonly QueryFactory $queryFactory,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Get current search string for customer
     *
     * @return mixed|string|null
     */
    public function getSearchText(): mixed
    {
        $query = $this->queryFactory->get();

        return $query->getQueryText();
    }

    /**
     * Get store currency code
     *
     * @return string
     */
    public function getCurrencyCode(): string
    {
        $store = $this->getStore();
        return $store ? $store->getBaseCurrency()->getCode() : TikTokInterface::DEFAULT_CURRENCY_CODE;
    }

    /**
     * Get store data
     *
     * @return StoreInterface|null
     */
    private function getStore(): ?StoreInterface
    {
        try {
            return $this->storeManager->getStore();
        } catch (NoSuchEntityException $e) {
            $this->logger->log('Ineiman_Tiktok could not get store: ' . $e->getMessage());
            $this->logger->log($e->getTraceAsString());
            return null;
        }
    }

    /**
     * Get salt for eventId
     *
     * @return string
     */
    public function getEventIdSalt(): string
    {
        try {
            return $this->random->getRandomString(24);
        } catch (LocalizedException $e) {
            $this->logger->log('Error with randomizing string for eventId: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Get Last Real Order from Checkout Session
     *
     * @return Order
     */
    public function getLastRealOrder(): Order
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * Load quote from checkout session
     *
     * @return CartInterface|Quote|null
     */
    public function loadQuote(): CartInterface|Quote|null
    {
        try {
            return $this->checkoutSession->getQuote();
        } catch (NoSuchEntityException|LocalizedException $e) {
            $this->logger->log('Ineiman_Tiktok could not load quote from session: ' . $e->getMessage());
            $this->logger->log($e->getTraceAsString());
            return null;
        }
    }
}
