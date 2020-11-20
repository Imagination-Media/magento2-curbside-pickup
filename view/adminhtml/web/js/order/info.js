/**
 * Curbside pickup for Magento 2
 *
 * This module extends the base pick up in-store Magento 2 functionality
 * adding an option for a curbside pick up
 *
 * @package ImaginationMedia\CurbsidePickup
 * @author Antonio Lolić <antonio@imaginationmedia.com>
 * @copyright Copyright (c) 2020 Imagination Media (https://www.imaginationmedia.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 *
 **/

define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mage.curbsideOrderInfo', {

        /** @inheritdoc */
        _create: function () {
            var selector = $(".order-information-table tbody"),
                orderInfo = this.options.curbsideOrderInfoHtml.orderInfo;

            if (selector.length && orderInfo) {
                selector.append(orderInfo);
            }
        }
    });

    return $.mage.curbsideOrderInfo;
});
