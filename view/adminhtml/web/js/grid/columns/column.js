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

define([], function (Column) {
    'use strict';

    return function (Column) {
        return Column.extend({

            initialize: function () {
                this._super();
            },

            /**
             * @param {Object} order
             * @returns {String}
             */
            getColorClassByCurbsideOrderStatus: function (order) {

                if (order.status === 'curbside_accepted') {
                    return 'curbside-accepted';
                } else if (order.status === 'curbside_ready') {
                    return 'curbside-ready';
                } else if (order.status === 'curbside_customer_ready') {
                    return 'curbside-customer-ready';
                }  else if (order.status === 'complete') {
                    return 'delivered';
                }
                return 'pending';
            }
        });
    }
});
