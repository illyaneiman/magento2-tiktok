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

namespace Ineiman\TikTok\Logger;

use Ineiman\TikTok\Model\Config\ConfigProvider;
use Psr\Log\LoggerInterface;

class Logger
{
    /**
     * @var bool
     */
    private $isDebugModeEnabled;

    /**
     * Construct
     *
     * @param ConfigProvider $configProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Log message to /var/log/tiktok/debug.log
     *
     * @param string $message
     * @return void
     */
    public function log(string $message): void
    {
        if ($this->isDebugModeEnabled()) {
            $this->logger->debug($message);
        }
    }

    /**
     * Checks whether is debug mode enabled
     *
     * @return bool
     */
    private function isDebugModeEnabled(): bool
    {
        if ($this->isDebugModeEnabled === null) {
            $this->isDebugModeEnabled = $this->configProvider->isDebugEnabled();
        }

        return $this->isDebugModeEnabled;
    }
}
