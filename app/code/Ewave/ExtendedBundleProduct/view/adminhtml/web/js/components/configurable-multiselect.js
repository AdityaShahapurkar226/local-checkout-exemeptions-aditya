define([
    'jquery',
    'Magento_Ui/js/form/element/multiselect'
], function ($, MultiSelect) {
    'use strict';

    return MultiSelect.extend({
        defaults: {
            url: '',
            productId: '',
            selectionId: ''
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super();

            var _this = this;
            setTimeout(function () {
                _this.initConfigurableOptions(_this.selectionId, _this.productId);
            }, 0);

            return this;
        },

        /**
         * Init configurable options
         */
        initConfigurableOptions: function (selectionId, productId) {
            var _this = this;
            $.ajax({
                url: this.url,
                data: {
                    selectionId: selectionId,
                    productId: productId
                },
                type: 'get',
                dataType: 'json',
                showLoader: true,

                /**
                 * @callback
                 */
                success: $.proxy(function (resp) {
                    if (resp.length > 0) {
                        var selected = [];
                        $.each(resp, function (index, option) {
                            if (option.selected) {
                                selected.push(option.value);
                            }
                        });
                        _this.default = selected.join(',');
                        _this.setOptions(resp);
                        _this.setInitialValue();
                    }
                }, this)
            });
        }
    });
});
