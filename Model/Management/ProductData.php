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

use Ineiman\TikTok\Logger\Logger;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Data as CatalogData;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Additional management class for product data
 */
class ProductData
{
    /**
     * Construct
     *
     * @param Logger $logger
     * @param ProductRepositoryInterface $productRepository
     * @param CatalogData $catalogData
     * @param RequestInterface $request
     */
    public function __construct(
        private readonly Logger $logger,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CatalogData $catalogData,
        private readonly RequestInterface $request
    ) {
    }

    /**
     * Get qty from request params
     *
     * @return int
     */
    public function getRequestedProductQty(): int
    {
        $qty = (int)$this->request->getParam('qty', 1);
        if ($qty <= 0) {
            $qty = 1;
        }

        return $qty;
    }

    /**
     * Get current product
     *
     * @return ProductInterface|Product|null
     */
    public function getProduct(): ProductInterface|Product|null
    {
        $product = $this->catalogData->getProduct();
        if (!$product) {
            $productId = $this->request->getParam('id');
            try {
                $product = $this->productRepository->getById($productId);
            } catch (NoSuchEntityException $e) {
                $this->logger->log('Ineiman_Tiktok could not load product: ' . $e);
                return null;
            }
        }

        return $product;
    }
}
