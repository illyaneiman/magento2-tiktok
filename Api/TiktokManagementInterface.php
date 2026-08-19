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

namespace Ineiman\TikTok\Api;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Data\Customer;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;

/**
 * Manage tiktok data
 */
interface TiktokManagementInterface
{
    /**
     * Get payload for Add To Cart event
     *
     * @param Product $product
     * @return array
     */
    public function getAddToCartEventPayload(Product $product): array;

    /**
     * Get payload for Update Cart event
     *
     * @param Quote $quote
     * @param array $updateInfo
     * @return array
     */
    public function getUpdateCartEventPayload(Quote $quote, array $updateInfo): array;

    /**
     * Get payload for Remove From Cart event
     *
     * @param Item $item
     * @return array
     */
    public function getRemoveFromCartEventPayload(Item $item): array;

    /**
     * Get payload for Add To Wishlist event
     *
     * @param Product $product
     * @return array
     */
    public function getAddToWishlistEventPayload(Product $product): array;

    /**
     * Get payload for Remove From Wishlist event
     *
     * @param Product $product
     * @return array
     */
    public function getRemoveFromWishlistEventPayload(Product $product): array;

    /**
     * Get payload for ViewContent event
     *
     * @return array
     */
    public function getViewContentEventPayload(): array;

    /**
     * Get payload for InitiateCheckout event
     *
     * @return array
     */
    public function getInitiateCheckoutEventPayload(): array;

    /**
     * Get payload for AddPaymentInfo event
     *
     * @param PaymentInterface $method
     * @param string $oldMethod
     * @return array
     */
    public function getAddPaymentInfoEventPayload(PaymentInterface $method, string $oldMethod): array;

    /**
     * Get payload for PlaceAnOrder event
     *
     * @param ?Order $order
     * @return array
     */
    public function getPlaceAnOrderEventPayload(?Order $order = null): array;

    /**
     * Get payload for Purchase event
     *
     * @param Invoice $invoice
     * @return array
     */
    public function getPurchaseEventPayload(Invoice $invoice): array;

    /**
     * Get payload for Search event
     *
     * @return array
     */
    public function getSearchEventPayload(): array;

    /**
     * Get payload for CompleteRegistration event
     *
     * @param Customer $customer
     * @return array
     */
    public function getCompleteRegistrationEventPayload(Customer $customer): array;

    /**
     * Set event payload to customerData
     *
     * @param array $payload
     * @param string $key
     * @return void
     */
    public function setPayloadInCustomerData(array $payload, string $key): void;

    /**
     * Get current store id
     *
     * @return int|string|null
     */
    public function getStoreId(): int|string|null;
}
