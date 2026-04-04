define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function ($, _, uiRegistry, Select) {
    'use strict';

    return Select.extend({
        fieldsHideIfConditional: [
            'index = discount_amount',
            'index = apply_for_parent',
            'index = apply_condition',
            'index = cart_message',
        ],

        updateVisibleFields: function (value) {
            var isConditional = value == 2;

            _.each(this.fieldsHideIfConditional, function (fieldHideIfConditional) {
                uiRegistry.get(fieldHideIfConditional, function (elem) {
                    elem.visible(!isConditional);
                });
            });

            uiRegistry.get('hideIfConditional = 1', function () {
                _.each(uiRegistry.filter('hideIfConditional = 1'), function (elem) {
                    elem.visible(!isConditional);
                });
            });

            uiRegistry.get('showIfConditional = 1', function () {
                _.each(uiRegistry.filter('showIfConditional = 1'), function (elem) {
                    elem.visible(isConditional);
                });
            });
        }
    });
});
