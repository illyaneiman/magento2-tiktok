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
use Ineiman\TikTok\Logger\Logger;
use Magento\Catalog\Helper\Data as CatalogData;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * TikTok management class to retrieve and manage data
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class TiktokManagement implements TiktokManagementInterface
{
    /**
     * @var StoreInterface|null
     */
    private ?StoreInterface $store = null;

    /**
     * Construct
     *
     * @param Logger $logger
     * @param CatalogData $catalogData
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param DataPersistorInterface $dataPersistor
     * @param RequestInterface $request
     * @param Random $random
     * @param QueryFactory $queryFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly Logger $logger,
        private readonly CatalogData $catalogData,
        private readonly CheckoutSession $checkoutSession,
        private readonly CustomerSession $customerSession,
        private readonly DataPersistorInterface $dataPersistor,
        private readonly RequestInterface $request,
        private readonly Random $random,
        private readonly QueryFactory $queryFactory,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getAddToCartEventPayload(Product $product): array
    {
        $qty = $this->request->getParam('qty', 1);
        if ($qty <= 0) {
            $qty = 1;
        }

        $price = (float)$product->getFinalPrice();
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
        if ($payload = $this->dataPersistor->get(TikTokInterface::EVENT_KEY_VIEW_CONTENT)) {
            $this->dataPersistor->clear(TikTokInterface::EVENT_KEY_VIEW_CONTENT);
            return $payload[0];
        }

        $product = $this->catalogData->getProduct();
        $productsContent = $this->prepareProductsContent([$product]);
        $properties = $this->preparePropertiesData(
            $productsContent,
            TikTokInterface::CONTENT_TYPE_PRODUCT,
            $product->getName(),
            $product->getFinalPrice()
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
        if ($payload = $this->dataPersistor->get(TikTokInterface::EVENT_KEY_INITIATE_CHECKOUT)) {
            $this->dataPersistor->clear(TikTokInterface::EVENT_KEY_INITIATE_CHECKOUT);
            return $payload[0];
        }

        $quote = $this->loadQuote();
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
        if ($payload = $this->dataPersistor->get(TikTokInterface::EVENT_KEY_PLACE_AN_ORDER)) {
            $this->dataPersistor->clear(TikTokInterface::EVENT_KEY_PLACE_AN_ORDER);
            return $payload[0];
        }

        /**
         * If only FE event enabled there are no order passed so retrieve it from the checkout session
         */
        if (!$order) {
            $order = $this->checkoutSession->getLastRealOrder();
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
        $properties = [
            'increment_id' => $invoice->getIncrementId(),
            'value' => $invoice->getGrandTotal(),
            'order_id' => $invoice->getOrder()?->getIncrementId()
        ];

        return $this->getEventPayload(TikTokInterface::EVENT_NAME_PURCHASE, $properties);
    }

    /**
     * @inheritDoc
     */
    public function getSearchEventPayload(): array
    {
        /**
         * If both FE and BE events enabled than for FE event payload already saved in dataPersistor
         */
        if ($payload = $this->dataPersistor->get(TikTokInterface::EVENT_KEY_SEARCH)) {
            $this->dataPersistor->clear(TikTokInterface::EVENT_KEY_SEARCH);
            return $payload[0];
        }

        $query = $this->queryFactory->get();
        $properties = [
            'search_string' => $query->getQueryText()
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
    public function getStoreId(): int|string|null
    {
        return $this->getStore()?->getId();
    }

    /**
     * Prepare event properties data
     *
     * @param array $contents
     * @param string $type
     * @param string $description
     * @param float $total
     * @return array
     */
    private function preparePropertiesData(array $contents, string $type, string $description, float $total): array
    {
        return [
            'contents' => $contents,
            'content_type' => $type,
            'description' => $description,
            'value' => $total,
            'currency' => $this->getCurrencyCode(),
        ];
    }

    /**
     * Get store currency code
     *
     * @return string
     */
    private function getCurrencyCode(): string
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
        if (!$this->store) {
            try {
                return $this->storeManager->getStore();
            } catch (NoSuchEntityException $e) {
                $this->logger->log('Ineiman_Tiktok could not get store: ' . $e->getMessage());
                $this->logger->log($e->getTraceAsString());
                return null;
            }
        }

        return $this->store;
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

        $customer = $this->customerSession->getCustomer();
        if ($customer && $customer->getId()) {
            $defaultShipping = $customer->getDefaultShippingAddress();

            $payload['user'] = [
                'email' => $this->hashCustomerData($customer->getEmail()),
                'phone' => $this->hashCustomerData($defaultShipping->getTelephone()),
                'external_id' => $this->hashCustomerData($customer->getId())
            ];
        }

        return $payload;
    }

    /**
     * Get unique event id.
     *
     * @param int|string|null $eventTime
     * @return string
     */
    private function getEventId(int|string $eventTime = null): string
    {
        return 'eventId_' . $eventTime . '_' . $this->getEventIdSalt();
    }

    /**
     * Get salt for eventId
     *
     * @return string
     */
    private function getEventIdSalt(): string
    {
        try {
            return $this->random->getRandomString(24);
        } catch (LocalizedException $e) {
            $this->logger->log('Error with randomizing string for eventId: ' . $e->getMessage());
            return '';
        }
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
                'quantity' => $item->getQty() ?: 1,
                'price' => $item->getFinalPrice() ?: $item->getPrice(),
            ];
        }

        return $productsContent;
    }

    /**
     * Load quote from checkout session
     *
     * @return CartInterface|Quote|null
     */
    private function loadQuote(): CartInterface|Quote|null
    {
        try {
            return $this->checkoutSession->getQuote();
        } catch (NoSuchEntityException|LocalizedException $e) {
            $this->logger->log('Ineiman_Tiktok could not load quote from session: ' . $e->getMessage());
            $this->logger->log($e->getTraceAsString());
            return null;
        }
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
