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

use Ineiman\TikTok\Api\TiktokManagementInterface;
use Ineiman\TikTok\Model\Config\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Main block for TikTok tracking
 */
class Tiktok extends Template
{
    /**
     * Construct
     *
     * @param TiktokManagementInterface $tiktokManagement
     * @param ConfigProvider $configProvider
     * @param Context $context
     */
    public function __construct(
        private readonly TiktokManagementInterface $tiktokManagement,
        private readonly ConfigProvider $configProvider,
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
     * Get customer entity id
     *
     * @return string
     */
    public function getCustomerEntityId(): string
    {
        return $this->tiktokManagement->getHashedCustomerEntityId();
    }

    /**
     * Get customer email
     *
     * @return string
     */
    public function getCustomerEmail(): string
    {
        return $this->tiktokManagement->getHashedCustomerEmail();
    }

    /**
     * Get customer telephone
     *
     * @return string
     */
    public function getCustomerTelephone(): string
    {
        return $this->tiktokManagement->getHashedCustomerTelephone();
    }
}
