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

namespace Ineiman\TikTok\Model\Queue;

use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Publisher class to prepare encoded data and publish in queue
 */
class Publisher
{
    /**
     * Topic name for TikTok event sender.
     * @see Ineiman/TikTok/etc/communication.xml
     */
    private const TIKTOK_SEND_EVENT_QUEUE_TOPIC_NAME = 'ineiman.tiktok.send.event';

    /**
     * Construct
     *
     * @param PublisherInterface $publisher
     */
    public function __construct(
        private readonly PublisherInterface $publisher
    ) {
    }

    /**
     * Send payload to the queue
     *
     * @param array $payload
     * @param int|string|null $storeId
     * @return void
     */
    public function publish(array $payload, int|string|null $storeId)
    {
        $data = [
            'payload' => $payload,
            'storeId' => $storeId
        ];
        $encodedData = json_encode($data);
        $this->publisher->publish(self::TIKTOK_SEND_EVENT_QUEUE_TOPIC_NAME, $encodedData);
    }
}
