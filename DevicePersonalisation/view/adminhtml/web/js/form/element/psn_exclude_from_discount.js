define([
    'Magento_Ui/js/form/element/single-checkbox-toggle-notice'
], function (Checkbox) {
    'use strict';

    return Checkbox.extend({
        defaults: {
            imports: {
                toggleDisabled: '${ $.parentName }.simple_action:value'
            }
        },

        /**
         * Toggle element disabled state according to simple action value.
         *
         * @param {String} action
         */
        toggleDisabled: function (action) {
            switch (action) {
                case 'buyxgetn_perc':
                    this.disabled(false);
                    this.visible(true);
                    break;
                default:
                    this.disabled(true);
                    this.visible(false);
            }

            if (this.disabled()) {
                this.checked(false);
                this.visible(false);
            }
        }
    });
});
