/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'mage/template',
    'Bss_ProductStockAlert/js/model/actions',
    'Bss_ProductStockAlert/js/helper/data',
    'Bss_ProductStockAlert/js/helper/validation'
], function ($, mageTemplate, actionsModel, helper, validation) {
    'use strict';

    $.widget('mage.bssProductStockAlertProcessor', {
        options: {
            templateId: '#bss-stock-notice-form',
            templateCancelId: '#bss-stock-notice-cancel-form',
            actionViewController: 'catalog_product_view',
            productType: 'grouped'
        },
        eventsBound: false,
        mainContext: '.page-wrapper',
        bodyContext: 'body',
        /**
         * @private
         */
        _create: function () {
            this._super();
            this._bindComponent();
            this._bindDocumentEvents();
        },
        /**
         * Bind widget
         * @private
         */
        _bindComponent: function () {
            actionsModel.setFormDataActionUrl(this.options.formDataActionUrl);
            actionsModel.requestFormData(this);
        },
        /**
         * Catch all events used
         * @private
         */
        _bindDocumentEvents: function () {
            var self = this;
            if (!self.eventsBound) {
                $(window).resize(function () {
                    // Bound event to display notice-form at child (the child product out of stock) position
                    // Css
                    if (self.options.productType == 'grouped' &&
                        (self.options.actionViewController == 'catalog_product_view' ||
                            self.options.actionViewController == 'wishlist_index_configure')) {
                        self.__bindGroupedCss();
                    }
                    if (self.options.productType == 'bundle' &&
                        (self.options.actionViewController == 'catalog_product_view' ||
                            self.options.actionViewController == 'wishlist_index_configure')) {
                        self.__bindBundleCss();
                    }
                });
                $(self.element).on('contentUpdated', function (e) {
                    // Bound event to display notice form at child (the child product out of stock) position
                    if (self.options.productType == 'grouped' &&
                        (self.options.actionViewController == 'catalog_product_view' ||
                            self.options.actionViewController == 'wishlist_index_configure')) {
                        self.__bindGroupedCss();
                    }
                    if (self.options.productType == 'bundle' &&
                        (self.options.actionViewController == 'catalog_product_view' ||
                            self.options.actionViewController == 'wishlist_index_configure')) {
                        self.__bindBundleCss();
                    }
                });
                if (self.options.productType == 'bundle') {
                    // Bundle with dropdown select
                    // Because of default, dropdown not select any option
                    // After choose one which is out of stock option, stock notice form will be appear
                    // It cause to change layout.
                    $('.bundle-option-select').change(function (e) {
                        self.__bindBundleCss();
                    });
                }
                self.eventsBound = true;
            }

            // Bind event validate email before submit
            $(document).on('click', '.add-notice-email', function (e) {
                e.preventDefault();
                var resultValidate = validation.isEmailValid(this);
                if (!resultValidate) {
                    return;
                }
                var formElem = $(this).closest('form.stockalert-form'),
                    tempFormElem = formElem.clone(),
                    uniqId = Math.random().toString(36).substring(7);
                tempFormElem.attr('id', uniqId);
                tempFormElem.appendTo($('body')).submit();
                tempFormElem.detach();
                return;
            });
        },

        /**
         * Bind css grouped product
         * @private
         */
        __bindGroupedCss: function () {
            var _this = this;
            $.each($('.block-stockalert'), function (key, val) {
                if ($(val).length) {
                    var pid = $(val).attr('data-product'),
                        destinationElem = $('#stock-notice-elem-grouped-' + pid);
                    _this.__bindCssToDestination(val, destinationElem);
                }
            });
        },

        /**
         * Bind css bundle product
         * @private
         */
        __bindBundleCss: function () {
            var _this = this;
            $.each($('.block-stockalert'), function (key, val) {
                if ($(val).length) {
                    /**
                     * Type select, all options bind to only specific div.
                     * Type radio, checkout, every one option has specific div
                     */
                    var destinationElem = null;
                    if ($(val).attr('data-option-type') != 'select') {
                        var pid = $(val).attr('data-product');
                        destinationElem = $('#stock-notice-elem-bundle-' + pid);
                    } else {
                        var optionId = $(val).attr('data-option');
                        destinationElem = $('#stock-notice-elem-bundle-select-' + optionId);
                    }

                    _this.__bindCssToDestination(val, destinationElem);
                }
            });
        },

        /**
         * @param elem
         * @param destinationElem
         * @private
         */
        __bindCssToDestination: function (elem, destinationElem) {
            var _this = this;
            if (destinationElem.length) {
                var minWidthShouldBeSet = destinationElem.closest('div.field').width();
                $(elem).css({
                    'width': minWidthShouldBeSet,
                    'margin-top': '8px',
                    'margin-bottom': '8px'
                });
                destinationElem.css('height', ($(elem).height() + 12) + 'px');
                $(elem).appendTo(destinationElem);
            }
        }
    });

    return $.mage.bssProductStockAlertProcessor;
});
