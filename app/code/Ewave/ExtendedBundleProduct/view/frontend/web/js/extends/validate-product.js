define([
    'jquery',
    'mage/mage',
    'Magento_Catalog/product/view/validation',
    'catalogAddToCart'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.productValidate', widget, {
            _create: function () {
                this._super();
                this.element.trigger('productValidateInitialized');
            }
        });

        return $.mage.productValidate;
    };
});
