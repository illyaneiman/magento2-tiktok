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

namespace Ineiman\TikTok\Model\Event;

use Ineiman\TikTok\Logger\Logger;
use Ineiman\TikTok\Model\Config\ConfigProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Sender class to send TikTok BE events to the TikTok API
 */
class Sender
{
    /**
     * Construct
     *
     * @param Logger $logger
     * @param ConfigProvider $configProvider
     * @param Client $httpClient
     */
    public function __construct(
        private readonly Logger $logger,
        private readonly ConfigProvider $configProvider,
        private readonly Client $httpClient
    ) {
    }

    /**
     * Send event to TikTok API
     *
     * @param array $payload
     * @param int|string|null $storeId
     * @return void
     */
    public function send(array $payload, int|string $storeId = null): void
    {
        $apiUrl = $this->configProvider->getApiUrl($storeId);
        $apiKey = $this->configProvider->getApiKey($storeId);
        $pixelId = $this->configProvider->getPixelId($storeId);
        $data = [
            'event_source' => 'web',
            'event_source_id' => $pixelId,
            'data' => [$payload],
        ];

        try {
            $response = $this->httpClient->post(
                $apiUrl,
                [
                    'headers' => [
                        'Access-Token' => $apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'body' => json_encode($data),
                    'timeout' => 3.0,
                    'connect_timeout' => 2.0,
                ]
            );

            $statusCode = $response->getStatusCode();
            if ($statusCode < 200 || $statusCode >= 300) {
                $this->logger->log('TikTok Events API returned HTTP ' . $statusCode);
                $this->logger->log('Missed payload: ' . json_encode($payload));
            }
        } catch (GuzzleException $e) {
            $this->logger->log('TikTok API request failed: ' . $e->getMessage());
            $this->logger->log('Missed payload: ' . json_encode($payload));
        }
    }
}
