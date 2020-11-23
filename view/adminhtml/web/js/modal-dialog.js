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
    'Magento_Ui/js/modal/modal'
], function (jQuery) {
    'use strict';

    return {
        overlayShowEffectOptions : null,
        overlayHideEffectOptions : null,
        modal: null,

        openDialog : function(data, orderId) {
            var self = this;
            if (this.modal && data) {
                this.modal.html(jQuery(data).html());
            } else {
                this.modal = jQuery(data).modal({
                    title: jQuery.mage.__('Curbside Order Summary'),
                    modalClass: 'modal',
                    type: 'slide',
                    firedElementId: orderId,
                    buttons: [{
                        text: jQuery.mage.__('Close'),
                        class: 'action- scalable back',
                        click: function () {
                            self.closeDialog(this);
                        }
                    }],
                    close: function () {
                        self.closeDialog(this);
                    }
                });
            }
            this.modal.modal('openModal');
        },

        closeDialog : function(dialog) {
            jQuery('body').trigger('processStop');
            dialog.closeModal();
            window.overlayHideEffectOptions = this.overlayHideEffectOptions;
            window.overlayShowEffectOptions = this.overlayShowEffectOptions;
        }
    };
});
