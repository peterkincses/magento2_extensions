define([
    'jquery',
    'uiComponent'
], function ($, Component) {
    'use strict';

    var quoteItemData = window.checkoutConfig.quoteItemData,
        isPsnEnabled = window.checkoutConfig.isDevicePersonalisationEnabled;

    return Component.extend({
        defaults: {
            template: 'BAT_DevicePersonalisation/summary/item/psn-details'
        },

        quoteItemData: quoteItemData,

        /**
         * @param {Object} product
         * @return {Object}
         */
        getItemProduct: function(item_id) {
            var itemElement = null;
            _.each(this.quoteItemData, function(element, index) {
                if (element.item_id == item_id) {
                    itemElement = element;
                }
            });
            return itemElement;
        },

        /**
         * @param {Object} quoteItem
         * @return {Object}
         */
        getPsnData: function(quoteItem) {
            var itemProduct = this.getItemProduct(quoteItem.item_id),
                psn_data = {};

            if(itemProduct.psn_data) {
                psn_data = itemProduct.psn_data;
            }
            return psn_data;
        },

        hasPsnData: function(quoteItem) {
            if (isPsnEnabled) {
                var itemProduct = this.getItemProduct(quoteItem.item_id),
                    psn_data = {};

                if (itemProduct.psn_data) {
                    psn_data = itemProduct.psn_data;
                }

                return Object.keys(psn_data).length > 0;
            } else {
                return false;
            }
        },

        initCollapsible: function(el) {
            $('.opc-sidebar .psn-options').collapsible({collapsible: false});
        }
    });
});
