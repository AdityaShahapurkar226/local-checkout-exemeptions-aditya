define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.addToWishlist', widget, {
            options: {
                bundleSupperAttribute: '[name^="bundle_super_attribute"]',
                container: '.field.option',
                configurableFieldset: '.bundle-configurable'
            },

            /**
             * @param {HTMLElement} element
             * @return {Object}
             * @private
             */
            _getElementData: function (element) {
                var superAttr = this.getSuperAttributes(element);
                return superAttr.length ? this._getSuperAttributesData(superAttr, this._super(element)) : this._super(element);
            },

            /**
             * Get data with super attributes
             * @param {jQuery} superAttr
             * @param {Object} defaultData
             * @returns {Object}
             * @private
             */
            _getSuperAttributesData: function (superAttr, defaultData) {
                var data = {},
                    isFilled = true;

                superAttr.each(function () {
                    if ($(this).val()) {
                        data[$(this).attr('name')] = $(this).val();
                    } else {
                        isFilled = false;
                    }
                });
                /* Ignore data if one or more of super attribute isn't selected */
                data = isFilled ? $.extend({}, defaultData, data) : {};
                return data;
            },

            /**
             * Get super attributes
             * @param element
             * @returns {jQuery}
             */
            getSuperAttributes: function (element) {
                return $(element).closest(this.options.container).find(this.options.configurableFieldset).filter(':visible').find(this.options.bundleSupperAttribute);
            }
        });

        return $.mage.addToWishlist;
    };
});
