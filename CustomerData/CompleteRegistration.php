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

namespace Ineiman\TikTok\CustomerData;

use Ineiman\TikTok\Api\Data\TikTokInterface;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Exposes queued TikTok payload for CompleteRegistration events as a standard Magento customer-data section.
 *
 * etc/frontend/sections.xml causes this section to be reloaded automatically by Magento's private-content mechanism
 * after every request - whether that request was made via ajax or a plain form submit that redirects to the exact page.
 * The storefront JS (view/frontend/web/js/track/events-section.js) subscribes to this section and fires
 * ttq.track('EventName', ...) whenever new events show up.
 */
class CompleteRegistration implements SectionSourceInterface
{
    /**
     * Construct
     *
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        private readonly DataPersistorInterface $dataPersistor
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getSectionData()
    {
        /**
         * Retrieve events to pass them in customer section data. Clear events data directly after retrieval to avoid
         * duplications on customer sections reload or after page reload.
         */
        $events = $this->dataPersistor->get(TikTokInterface::EVENT_KEY_COMPLETE_REGISTRATION);
        $this->dataPersistor->clear(TikTokInterface::EVENT_KEY_COMPLETE_REGISTRATION);

        return [
            'events' => is_array($events) ? $events : [],
        ];
    }
}
