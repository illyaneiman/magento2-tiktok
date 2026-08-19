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

use Ineiman\TikTok\Model\Event\Sender;

/**
 * Process TikTok queue
 */
class Consumer
{
    /**
     * Construct
     *
     * @param Sender $sender
     */
    public function __construct(
        private readonly Sender $sender
    ) {
    }

    /**
     * Process TikTok queue and send event data to the TikTok API
     *
     * @param string $data
     * @return void
     */
    public function process(string $data): void
    {
        $data = json_decode($data, true);
        if (!isset($data['payload'])) {
            return;
        }

        $payload = $data['payload'];
        $storeId = $data['storeId'];

        $this->sender->send($payload, $storeId);
    }
}
