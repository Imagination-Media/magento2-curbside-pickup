/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_InventoryInStorePickupFrontend/js/model/pickup-locations-service'
], function (
    $,
    _,
    setShippingInformationAction,
    pickupLocationsService
) {
    'use strict';

    return function (storeSelectorObj) {

        return storeSelectorObj.extend({
            defaults: {
                selectedLocationTemplate: 'ImaginationMedia_CurbsidePickup/store-selector/selected-location',
                storeSelectorPopupItemTemplate: 'ImaginationMedia_CurbsidePickup/store-selector/popup-item',
                curbsideMode: pickupLocationsService.curbsideMode
            },

            /**
             * @param {Object} location
             * @returns void
             */
            selectPickupLocation: function (location) {
                pickupLocationsService.curbsideMode(false);
                pickupLocationsService.selectForShipping(location);
                this.getPopup().closeModal();
            },

            /**
             * @param {Object} location
             * @returns void
             */
            selectCurbsidePickupLocation: function (location) {
                pickupLocationsService.selectCurbsideForShipping(location);
                this.getPopup().closeModal();
            },

            /**
             * @param {Object} location
             * @returns {*|Boolean}
             */
            isPickupLocationSelected: function (location) {
                return _.isEqual(this.selectedLocation(), location) && _.isEqual(pickupLocationsService.curbsideMode(), false);
            },

            /**
             * @param {Object} location
             * @returns {*|Boolean}
             */
            isCurbsidePickupLocation: function (location) {
                return location.is_curbside_pickup_location_active;
            },

            /**
             * @param {Object} location
             * @returns {*|Boolean}
             */
            isCurbsidePickupLocationUnSelected: function (location) {
                return false == _.isEqual(this.selectedLocation(), location) || _.isEqual(pickupLocationsService.curbsideMode(), false);
            },

            /**
             * @param {Object} location
             * @returns {*|Boolean}
             */
            isCurbsidePickupLocationSelected: function (location) {
                return _.isEqual(this.selectedLocation(), location) && _.isEqual(pickupLocationsService.curbsideMode(), true);
            }
        });
    };
});
