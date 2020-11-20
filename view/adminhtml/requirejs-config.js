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

var config = {
    map: {
        '*': {
            reloadCurbsideOrderGrid: 'ImaginationMedia_CurbsidePickup/js/grid/component/reload',
            curbsideModal: 'ImaginationMedia_CurbsidePickup/js/modal-dialog',
            curbsideOrderInfo: 'ImaginationMedia_CurbsidePickup/js/order/info',
        }
    },
    config: {
        mixins: {
            'Magento_Ui/js/grid/columns/column': {
                'ImaginationMedia_CurbsidePickup/js/grid/columns/column': true
            },
        }
    },
};
