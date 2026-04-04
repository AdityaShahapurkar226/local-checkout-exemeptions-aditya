define([
    'jquery',
    'underscore',
    'mage/template',
    'priceUtils',
    'priceBox',
    'mage/validation',
    'Magento_Catalog/product/view/validation',
    'Magento_Catalog/js/validate-product'
], function ($, _) {
    'use strict';

    return function (widget) {
        $.widget('mage.priceBundle', widget, {
            options: {
                priceBundleConfigurable: '.bundle-configurable',
                superSelector: '.super-attribute-select',
                hiddenClass: 'no-display'
            },
            _create: function () {
                this._super();
                $(this.element).on('productValidateInitialized', $.proxy(this._validateConfigurable, this));
            },
            _onBundleOptionChanged: function (event) {
                this._super(event);
                this._reloadImage(event);
                if ($(this.element).data('mageValidation') !== undefined) {
                    this._validateConfigurable();
                }
            },
            _validateConfigurable: function () {
                var self = this,
                    $element = $(self.element);
                if ($(this.options.priceBundleConfigurable).length) {
                    $element.find(self.options.superSelector).each(function () {
                        $(this).rules('remove', 'required');
                    });
                    $(this.options.priceBundleConfigurable).addClass(self.options.hiddenClass);
                    $element.find('input:radio:checked, input:checkbox:checked, .bundle-option-select, input.bundle.option[type="hidden"]').each(function () {
                        var $selection = $(this),
                            selectionId = $selection.val(),
                            $configurableForm = $('#bundle_configurable_form-' + selectionId);
                        if ($configurableForm.length) {
                            $configurableForm.removeClass(self.options.hiddenClass);
                            $configurableForm.find(self.options.superSelector).each(function () {
                                $(this).rules('add', { 'required': true });
                            });
                        }
                    });
                }
            },
            _reloadImage: function (event) {
                var $bundleOption = $(event.target);

                if (event.currentTarget.tagName.toLowerCase() === 'select') {
                    $('[data-image*="' + $bundleOption.attr('id') + '"]').removeClass('-active');
                    if ($bundleOption.val()) {
                        if (typeof $bundleOption.val() === 'object') {
                            $.each($bundleOption.val(), function (i, item) {
                                $('[data-image="' + $bundleOption.attr('id') + '-' + item + '"]').addClass('-active');
                            });
                        } else {
                            $('[data-image="' + $bundleOption.attr('id') + '-' + $bundleOption.val() + '"]').addClass('-active');
                        }
                    }
                }
            }
        });

        return $.mage.priceBundle;
    };
});
