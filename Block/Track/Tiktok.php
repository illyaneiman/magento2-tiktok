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

namespace Ineiman\TikTok\Block\Track;

use Ineiman\TikTok\Model\Config\ConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Main block for TikTok tracking
 */
class Tiktok extends Template
{
    /**
     * @var Customer|null
     */
    private ?Customer $customer = null;

    /**
     * Construct
     *
     * @param ConfigProvider $configProvider
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param Context $context
     */
    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly CheckoutSession $checkoutSession,
        private readonly CustomerSession $customerSession,
        Template\Context $context,
    ) {
        parent::__construct($context);
    }

    /**
     * Get Pixel Id key
     *
     * @return string
     */
    public function getPixelId(): string
    {
        return $this->configProvider->getPixelId();
    }

    /**
     * Get customer model object
     *
     * @return Customer
     */
    private function getCustomer(): Customer
    {
        if (!$this->customer) {
            $this->customer = $this->customerSession->getCustomer();
        }

        return $this->customer;
    }

    /**
     * Get customer entity id
     *
     * @return string
     */
    public function getCustomerEntityId(): string
    {
        $customer = $this->getCustomer();
        $customerId = $customer->getId();

        return $customerId ? $this->hashCustomerData($customerId) : '';
    }

    /**
     * Get customer email
     *
     * @return string
     */
    public function getCustomerEmail(): string
    {
        $customer = $this->getCustomer();

        /**
         * If customer is guest retrieve email from order at Checkout Success page
         */
        $email = $customer->getEmail()
            ? $customer->getEmail()
            : $this->checkoutSession->getLastRealOrder()?->getCustomerEmail();

        return $email ? $this->hashCustomerData($email) : '';
    }

    /**
     * Get customer telephone
     *
     * @return string
     */
    public function getCustomerTelephone(): string
    {
        $defaultShipping = $this->getCustomer()->getDefaultShippingAddress();

        /**
         * If customer is guest retrieve telephone from order at Checkout Success page
         */
        $phone = $defaultShipping
            ? $defaultShipping->getTelephone()
            : $this->checkoutSession->getLastRealOrder()?->getShippingAddress()?->getTelephone();

        return $phone ? $this->hashCustomerData($phone) : '';
    }

    /**
     * Hash customer field value
     *
     * @param string $field
     * @return string
     */
    private function hashCustomerData(string $field): string
    {
        return hash('sha256', strtolower(trim($field)));
    }
}
