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

namespace Ineiman\TikTok\Model;

use Ineiman\TikTok\Api\Data\TikTokInterface;
use Ineiman\TikTok\Api\TiktokManagementInterface;
use Ineiman\TikTok\Model\Management\CustomerData;
use Ineiman\TikTok\Model\Management\Data;
use Ineiman\TikTok\Model\Management\ProductData;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;

/**
 * TikTok management class to retrieve and manage data
 */
class TiktokManagement implements TiktokManagementInterface
{
    /**
     * Construct
     *
     * @param CustomerData $customerData
     * @param Data $data
     * @param ProductData $productData
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        private readonly CustomerData $customerData,
        private readonly Data $data,
        private readonly ProductData $productData,
        private readonly DataPersistorInterface $dataPersistor
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getAddToCartEventPayload(Product $product): array
    {
        $productsContent = [];
        $price = (float)$product->getFinalPrice();
        $qty = $this->productData->getRequestedProductQty();
        $totalPrice = round($price * $qty, 2);
        $contentType = $product->getTypeId() === Configurable::TYPE_CODE
            ? TikTokInterface::CONTENT_TYPE_PRODUCT_GROUP
            : TikTokInterface::CONTENT_TYPE_PRODUCT;
        $productsContent[] = [
            'content_id' => (string)$product->getSku(),
            'content_name' => (string)$product->getName(),
            'quantity' => $qty,
            'price' => $price
        ];
        $properties = $this->preparePropertiesData($productsContent, $contentType, $product->getName(), $totalPrice);

        return $this->getEventPayload(TikTokInterface::EVENT_NAME_ADD_TO_CART, $properties);
    }

    /**
     * @inheritDoc
     */
    public function getUpdateCartEventPayload(Quote $quote, array $updateInfo): array
    {
        $items = $quote->getAllVisibleItems();
        foreach ($items as $key => $item) {
            $itemId = $item->getItemId();
            if (!isset($updateInfo[$itemId])) {
                unset($items[$key]);
            }
        }

        $productsContent = $this->prepareProductsContent($items);
        $properties = $this->preparePropertiesData(
            $productsContent,
            TikTokInterface::CONTENT_TYPE_PRODUCT_GROUP,
            'Update Cart',
            (float)$quote->getGrandTotal()
        );

        return $this->getEventPayload(TikTokInterface::EVENT_NAME_UPDATE_CART, $properties);
    }

    /**
     * @inheritDoc
     */
    public function getRemoveFromCartEventPayload(Item $item): array
    {
        $productsContent = $this->prepareProductsContent([$item]);
        $properties = $this->preparePropertiesData(
            $productsContent,
            TikTokInterface::CONTENT_TYPE_PRODUCT,
            $item->getName(),
            (float)$item->getRowTotal()
        );

        return $this->getEventPayload(TikTokInterface::EVENT_NAME_REMOVE_FROM_CART, $properties);
    }

    /**
     * @inheritDoc
     */
    public function getAddToWishlistEventPayload(Product $product): array
    {
        $productsContent = $this->prepareProductsContent([$product]);
        $properties = $this->preparePropertiesData(
            $productsContent,
            TikTokInterface::CONTENT_TYPE_PRODUCT,
            $product->getName(),
            $product->getFinalPrice()
        );

        return $this->getEventPayload(TikTokInterface::EVENT_NAME_ADD_TO_WISHLIST, $properties);
    }

    /**
     * @inheritDoc
     */
    public function getRemoveFromWishlistEventPayload(Product $product): array
    {
        $productsContent = $this->prepareProductsContent([$product]);
        $properties = $this->preparePropertiesData(
            $productsContent,
            TikTokInterface::CONTENT_TYPE_PRODUCT,
            $product->getName(),
            $product->getFinalPrice()
        );

        return $this->getEventPayload(TikTokInterface::EVENT_NAME_REMOVE_FROM_WISHLIST, $properties);
    }

    /**
     * @inheritDoc
     */
    public function getViewContentEventPayload(): array
    {
        /**
         * If both FE and BE events enabled than for FE event payload already saved in dataPersistor
         */
        $payload = $this->dataPersistor->get(TikTokInterface::EVENT_KEY_VIEW_CONTENT);
        if ($payload) {
            $this->dataPersistor->clear(TikTokInterface::EVENT_KEY_VIEW_CONTENT);
            return $payload[0];
        }

        $product = $this->productData->getProduct();
        $productsContent = $product ? $this->prepareProductsContent([$product]) : [];
        $properties = $this->preparePropertiesData(
            $productsContent,
            TikTokInterface::CONTENT_TYPE_PRODUCT,
            $product?->getName(),
            $product?->getFinalPrice()
        );

        return $this->getEventPayload(TikTokInterface::EVENT_NAME_VIEW_CONTENT, $properties);
    }

    /**
     * @inheritDoc
     */
    public function getInitiateCheckoutEventPayload(): array
    {
        /**
         * If both FE and BE events enabled than for FE event payload already saved in dataPersistor
         */
        $payload = $this->dataPersistor->get(TikTokInterface::EVENT_KEY_INITIATE_CHECKOUT);
        if ($payload) {
            $this->dataPersistor->clear(TikTokInterface::EVENT_KEY_INITIATE_CHECKOUT);
            return $payload[0];
        }

        $quote = $this->data->loadQuote();
        if (!$quote) {
            return [];
        }
        $items = $quote->getAllVisibleItems();

        $productsContent = $this->prepareProductsContent($items);
        $properties = $this->preparePropertiesData(
            $productsContent,
            TikTokInterface::CONTENT_TYPE_PRODUCT_GROUP,
            'Initiate Checkout',
            (float)$quote->getGrandTotal()
        );

        return $this->getEventPayload(TikTokInterface::EVENT_NAME_INITIATE_CHECKOUT, $properties);
    }

    /**
     * @inheritDoc
     */
    public function getAddPaymentInfoEventPayload($method, $oldMethod): array
    {
        $properties = [
            'old_method_name' => $oldMethod,
            'new_method_name' => $method->getMethod()
        ];

        return $this->getEventPayload(TikTokInterface::EVENT_NAME_ADD_PAYMENT_INFO, $properties);
    }

    /**
     * @inheritDoc
     */
    public function getPlaceAnOrderEventPayload(?Order $order = null): array
    {
        /**
         * If both FE and BE events enabled than for FE event payload already saved in dataPersistor
         */
        $payload = $this->dataPersistor->get(TikTokInterface::EVENT_KEY_PLACE_AN_ORDER);
        if ($payload) {
            $this->dataPersistor->clear(TikTokInterface::EVENT_KEY_PLACE_AN_ORDER);
            return $payload[0];
        }

        /**
         * If only FE event enabled there are no order passed so retrieve it from the checkout session
         */
        if (!$order) {
            $order = $this->data->getLastRealOrder();
        }
        $items = $order->getAllVisibleItems();

        $productsContent = $this->prepareProductsContent($items);
        $properties = $this->preparePropertiesData(
            $productsContent,
            TikTokInterface::CONTENT_TYPE_PRODUCT_GROUP,
            'Checkout Success Page',
            (float)$order->getGrandTotal()
        );

        return $this->getEventPayload(TikTokInterface::EVENT_NAME_PLACE_AN_ORDER, $properties);
    }

    /**
     * @inheritDoc
     */
    public function getPurchaseEventPayload(Invoice $invoice): array
    {
        $items = $invoice->getAllItems();
        $order = $invoice->getOrder();
        $productsContent = $this->prepareProductsContent($items);
        $properties = $this->preparePropertiesData(
            $productsContent,
            TikTokInterface::CONTENT_TYPE_PRODUCT_GROUP,
            'Invoice for an order #' . $order->getIncrementId(),
            (float)$invoice->getGrandTotal()
        );

        $payload = $this->getEventPayload(TikTokInterface::EVENT_NAME_PURCHASE, $properties);
        $payload['user'] = [
            'email' => $this->customerData->hashCustomerData($order->getCustomerEmail()),
            'phone' => $this->customerData->hashCustomerData($invoice->getShippingAddress()->getTelephone())
        ];

        $customerId = $order->getCustomerId();
        if ($customerId) {
            $payload['user']['external_id'] = $this->customerData->hashCustomerData((string)$customerId);
        }

        return $payload;
    }

    /**
     * @inheritDoc
     */
    public function getSearchEventPayload(): array
    {
        /**
         * If both FE and BE events enabled than for FE event payload already saved in dataPersistor
         */
        $payload = $this->dataPersistor->get(TikTokInterface::EVENT_KEY_SEARCH);
        if ($payload) {
            $this->dataPersistor->clear(TikTokInterface::EVENT_KEY_SEARCH);
            return $payload[0];
        }

        $properties = [
            'search_string' => $this->data->getSearchText()
        ];

        return $this->getEventPayload(TikTokInterface::EVENT_NAME_SEARCH, $properties);
    }

    /**
     * @inheritDoc
     */
    public function getCompleteRegistrationEventPayload(Customer $customer): array
    {
        $properties = [
            'customer_id' => $customer->getId(),
            'customer_email' => $customer->getEmail()
        ];

        return $this->getEventPayload(TikTokInterface::EVENT_NAME_COMPLETE_REGISTRATION, $properties);
    }

    /**
     * @inheritDoc
     */
    public function setPayloadInCustomerData(array $payload, string $key): void
    {
        $events = $this->dataPersistor->get($key);
        if (!is_array($events)) {
            $events = [];
        }
        $events[] = $payload;
        $this->dataPersistor->set($key, $events);
    }

    /**
     * @inheritDoc
     */
    public function getHashedCustomerEntityId(): string
    {
        return $this->customerData->getHashedCustomerEntityId();
    }

    /**
     * @inheritDoc
     */
    public function getHashedCustomerEmail(): string
    {
        return $this->customerData->getHashedCustomerEmail();
    }

    /**
     * @inheritDoc
     */
    public function getHashedCustomerTelephone(): string
    {
        return $this->customerData->getHashedCustomerTelephone();
    }

    /**
     * Prepare event properties data
     *
     * @param array $contents
     * @param string $type
     * @param string|null $description
     * @param float|null $total
     * @return array
     */
    private function preparePropertiesData(array $contents, string $type, ?string $description, ?float $total): array
    {
        return [
            'contents' => $contents,
            'content_type' => $type,
            'description' => $description,
            'value' => $total,
            'currency' => $this->data->getCurrencyCode(),
        ];
    }

    /**
     * Get prepared event payload
     *
     * @param string $eventName
     * @param array $properties
     * @return array
     */
    private function getEventPayload(string $eventName, array $properties): array
    {
        $eventTime = time();
        $payload = [
            'event' => $eventName,
            'event_id' => $this->getEventId($eventTime),
            'event_time' => $eventTime,
            'properties' => $properties
        ];

        $customer = $this->customerData->getCustomer();
        if ($customer->getId()) {
            $defaultShipping = $customer->getDefaultShippingAddress();
            $phone = $defaultShipping ? $defaultShipping->getTelephone() : '';

            $payload['user'] = [
                'email' => $this->customerData->hashCustomerData($customer->getEmail()),
                'phone' => $this->customerData->hashCustomerData($phone),
                'external_id' => $this->customerData->hashCustomerData($customer->getId())
            ];
        }

        return $payload;
    }

    /**
     * Get unique event id.
     *
     * @param int $eventTime
     * @return string
     */
    private function getEventId(int $eventTime): string
    {
        return 'eventId_' . $eventTime . '_' . $this->data->getEventIdSalt();
    }

    /**
     * Prepare Products content data
     *
     * @param array $items
     * @return array
     */
    private function prepareProductsContent(array $items): array
    {
        $productsContent = [];
        foreach ($items as $item) {
            $productsContent[] = [
                'content_id' => $item->getSku(),
                'content_name' => $item->getName(),
                'quantity' => (int)($item->getQty() ?: 1),
                'price' => $item->getFinalPrice() ?: $item->getPrice(),
            ];
        }

        return $productsContent;
    }
}
