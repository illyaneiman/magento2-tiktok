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

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Additional management class for customer data
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CustomerData
{
    /**
     * Construct
     *
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     */
    public function __construct(
        private readonly CheckoutSession $checkoutSession,
        private readonly CustomerSession $customerSession
    ) {
    }

    /**
     * Get customer from customerSession
     *
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customerSession->getCustomer();
    }

    /**
     * Hash customer field value
     *
     * @param string $field
     * @return string
     */
    public function hashCustomerData(string $field): string
    {
        return hash('sha256', strtolower(trim($field)));
    }

    /**
     * Get hashed customer id
     *
     * @return string
     */
    public function getHashedCustomerEntityId(): string
    {
        $customer = $this->getCustomer();
        $customerId = $customer->getId();

        return $customerId ? $this->hashCustomerData($customerId) : '';
    }

    /**
     * Get hashed customer email
     *
     * @return string
     */
    public function getHashedCustomerEmail(): string
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
     * Get hashed customer telephone
     *
     * @return string
     */
    public function getHashedCustomerTelephone(): string
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
}
