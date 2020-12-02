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

require([
    'jquery',
    'curbsideModal',
    'reloadCurbsideOrderGrid',
    'Magento_Ui/js/modal/alert'
], function(
    jQuery,
    curbsideOrderModalDialog,
    reloadCurbsideOrderGrid,
    alert
) {

    //<![CDATA[
    window.keepMultiModalWindow = true
    window.curbsidePickup = {

        openDetailsInModal : function(viewUrl, orderId) {
            if (viewUrl && orderId) {
                jQuery.ajax({
                    url: viewUrl,
                    type: 'GET',
                    data: { id: orderId },
                    showLoader: true,
                    dataType: 'html',
                    success: function(data) {
                        curbsideOrderModalDialog.openDialog(data, orderId);
                    },
                    error: function(error) {
                        console.error(error);
                    }
                });
            }
        },

        triggerOrderStatusChange : function(viewUrl, orderId) {
            if (viewUrl && orderId) {
                let btnSelector = jQuery('order-' + orderId);
                let buttonTitle = btnSelector.html();
                btnSelector.html(jQuery.mage.__('Processing...'));
                jQuery.ajax({
                    url: viewUrl,
                    type: 'POST',
                    cache: false,
                    data: { id: orderId },
                    showLoader: true,
                    dataType: 'json',
                    success: function(data) {
                        if (data.status) {
                            reloadCurbsideOrderGrid.refreshGridData();
                            jQuery('body').trigger('processStop');
                            alert({
                                title: jQuery.mage.__('Order Status'),
                                content: data.message || jQuery.mage.__('You assigned the order status.')
                            });
                        }
                        if (data.error) {
                            alert({
                                title: jQuery.mage.__('Order Status'),
                                content: data.message
                            });
                        }
                    },
                    error: function(error) {
                        console.error(error);
                        alert({
                            title: jQuery.mage.__('Order Status'),
                            content: jQuery.mage.__('Something went wrong while assigning the order status.')
                        });
                    },
                    complete: function () {
                        btnSelector.html(buttonTitle);
                    }
                });
            }
        }
    };
    //]]>
});
