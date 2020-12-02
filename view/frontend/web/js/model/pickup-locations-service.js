/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'knockout',
    'underscore',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/action/select-shipping-address',
    'mage/utils/wrapper'
], function (
    $,
    ko,
    _,
    checkoutData,
    addressConverter,
    selectShippingAddressAction,
    wrapper
) {
    'use strict';

    return function (pickupLocationsServiceObj) {

        pickupLocationsServiceObj.curbsideMode = ko.observable(false);

        pickupLocationsServiceObj.selectCurbsideForShipping = wrapper.wrapSuper(pickupLocationsServiceObj.selectCurbsideForShipping, function (location) {
            var address = $.extend(
                {},
                addressConverter.formAddressDataToQuoteAddress({
                    firstname: location.name,
                    lastname: 'Store',
                    street: location.street,
                    city: location.city,
                    postcode: location.postcode,
                    'country_id': location['country_id'],
                    telephone: location.telephone,
                    'region_id': location['region_id'],
                    'save_in_address_book': 0
                }),
                {
                    /**
                     * Is address can be used for billing
                     *
                     * @return {Boolean}
                     */
                    canUseForBilling: function () {
                        return false;
                    },

                    /**
                     * Returns address type
                     *
                     * @return {String}
                     */
                    getType: function () {
                        return 'store-pickup-address';
                    },
                    'extension_attributes': {
                        'pickup_location_code': location['pickup_location_code'],
                        'is_curbside_pickup_location_active': location['is_curbside_pickup_location_active'],
                    }
                }
            );

            this.selectedLocation(location);
            this.curbsideMode(true);
            selectShippingAddressAction(address);
            checkoutData.setSelectedShippingAddress(address.getKey());
        });

        pickupLocationsServiceObj.formatAddress = wrapper.wrapSuper(pickupLocationsServiceObj.formatAddress, function (address) {
            return {
                name: address.name,
                description: address.description,
                latitude: address.latitude,
                longitude: address.longitude,
                street: [address.street],
                city: address.city,
                postcode: address.postcode,
                'country_id': address['country_id'],
                country: this.getCountryName(address['country_id']),
                telephone: address.phone,
                'region_id': address['region_id'],
                region: this.getRegionName(
                    address['country_id'],
                    address['region_id']
                ),
                'pickup_location_code': address['pickup_location_code'],
                'is_curbside_pickup_location_active': (typeof address['extension_attributes']['is_curbside_pickup_location_active'] != 'undefined' ? address['extension_attributes']['is_curbside_pickup_location_active'] : false)
            };
        });

        return pickupLocationsServiceObj;
    };
});
