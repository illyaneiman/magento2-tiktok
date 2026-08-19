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
define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    return function (config) {
        let sectionName = config.eventSectionName,
            section = customerData.get(sectionName);

        /**
         * Fire ttq.track('EventName', ...) for every queued payload, then immediately overwrite the browser-cached
         * copy of this section with an empty events array for each event which already sent.
         *
         * @param {Object} data
         */
        function fireEvents(data) {
            if (!data || !data.events || !data.events.length) {
                return;
            }

            data.events.forEach(function (payload) {
                if (typeof window.ttq !== 'undefined' && payload.properties) {
                    /**
                     * Events additional data. For example 'event_id' set for deduplication.
                     *
                     * @type {{}}
                     */
                    let additionalData = {};
                    if (payload.event_id) {
                        additionalData['event_id'] = payload.event_id
                    }
                    window.ttq.track(config.eventName, payload.properties, additionalData);
                } else {
                    console.warn(
                        'Ineiman_Tiktok: ttq is not defined - check if the TikTok snippet is loaded. Skipped payload:',
                        payload
                    );
                }
            });

            customerData.set(sectionName, {events: []});
        }

        /**
         * Handles the case where the section already contains events on page load. For example: a non-ajax
         * "Add to Wishlist" event which redirects customer to the wishlist page
         */
        fireEvents(section());

        /**
         * Handles the case a theme/module wires up an ajax event flow.
         */
        section.subscribe(function (data) {
            fireEvents(data);
        });
    };
});
