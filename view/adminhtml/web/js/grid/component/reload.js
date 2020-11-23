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

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiRegistry'
], function (registry) {
    'use strict';

    return {
        dataSourcePath: 'curbside_order_listing.curbside_order_listing_data_source',

        refreshGridData: function () {
            var grid = registry.get(this.dataSourcePath);
            if (grid && typeof grid === 'object') {
                grid.set('params.t', Date.now());
            }
        }
    };
});
