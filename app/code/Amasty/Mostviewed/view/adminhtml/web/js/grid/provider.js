define([
    'jquery',
    'Magento_Ui/js/grid/provider'
], function ($, provider) {
    'use strict';

    return provider.extend({
        reload: function (options) {
            var conditionElements = $('.ammost-' +
                this.params['relation'] +
                '-to-display [data-form-part="amasty_mostviewed_product_group_form"]'
            ), onlyOutOfStock = $(
                '[data-index="for_out_of_stock"] input'
            ), conditions = {};
            $.each(conditionElements, function (index, element) {
                conditions[element.name] = element.value;
            });
            if (onlyOutOfStock.length) {
                conditions['for_out_of_stock'] = onlyOutOfStock.val();
            }

            var params = {};
            $.each(this.params, function(index, item) {
                var temp = {};
                temp[index] = item;
                $.extend(params, temp);
            });
            $.extend(this.params, conditions);

            this._super({'refresh': true});

            this.params = params;
        }
    });
});
