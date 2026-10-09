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
use Psr\Http\Message\ResponseInterface;

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
     * @return void
     */
    public function send(array $payload): void
    {
        $response = $this->sendEvent($payload);
        if (!$response) {
            return;
        }

        $this->checkResponseStatus($response, $payload);
    }

    /**
     * Check response status and log if not OK
     *
     * @param ResponseInterface $response
     * @param array $payload
     * @return void
     */
    private function checkResponseStatus(ResponseInterface $response, array $payload): void
    {
        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            $this->logger->log('TikTok Events API returned HTTP ' . $statusCode);
            $this->logger->log('Missed payload: ' . json_encode($payload));
        }
    }

    /**
     * Send TikTok event
     *
     * @param array $payload
     * @return bool|ResponseInterface
     */
    private function sendEvent(array $payload): bool|ResponseInterface
    {
        $apiUrl = $this->configProvider->getApiUrl();
        $apiKey = $this->configProvider->getApiKey();

        try {
            return $this->httpClient->post(
                $apiUrl,
                [
                    'headers' => [
                        'Access-Token' => $apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'body' => $this->prepareBody($payload),
                    'timeout' => 3.0,
                    'connect_timeout' => 2.0,
                ]
            );
        } catch (GuzzleException $e) {
            $this->logger->log('TikTok API request failed: ' . $e->getMessage());
            $this->logger->log('Missed payload: ' . json_encode($payload));
            return false;
        }
    }

    /**
     * Prepare request body for TikTok event
     *
     * @param array $payload
     * @return string
     */
    private function prepareBody(array $payload): string
    {
        $pixelId = $this->configProvider->getPixelId();
        $data = [
            'event_source' => 'web',
            'event_source_id' => $pixelId,
            'data' => [$payload],
        ];

        return json_encode($data);
    }
}
