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
    'underscore',
    'ko',
    'mage/template',
    'Bss_ProductStockAlert/js/helper/data',
    'Bss_ProductStockAlert/js/model/product/configurable',
    'Bss_ProductStockAlert/js/model/product/bundle'
], function ($, _, ko, mageTemplate, helper, configurableModel, bundleModel) {
    'use strict';

    return {
        loading: false,
        formDataActionUrl: ko.observable(),
        /**
         * Ajax request form data
         * @param parent
         */
        requestFormData: function (parent) {
            var self = this;
            if (!self.loading) {
                $("body").trigger('processStart');
                $.ajax({
                    url: self.getFormDataActionUrl(),
                    dataType: 'json',
                    type: 'GET',
                    contentType: 'application/json; charset=UTF-8',
                    headers: {
                        'Content-Type':'application/json'
                    },
                    beforeSend: function () {
                        self.loading = true;
                    },
                    complete: function (res) {
                        // Do something
                        self.loading = false;
                        // Re-enable button slide
                        $('#bundle-slide').prop('disabled', false);
                        $("body").trigger('processStop');
                    }
                }).done(function (dataResponse) {
                    if (dataResponse && (dataResponse.length || _.size(dataResponse)) && undefined === dataResponse._error) {
                        var hasEmail = dataResponse.has_email,
                            type = dataResponse.type,
                            productData = dataResponse.product_data,
                            templateId = parent.options.templateId,
                            templateCancelid = parent.options.templateCancelId;
                        if (type == 'simple') {
                            // Simple product
                            // Has only 1 sub array, so get first item for data
                            var cleanDataResponse = _.omit(dataResponse, 'product_data'),
                                firstKey = _.keys(productData)[0],
                                dataRenderer = helper.mergeObject(cleanDataResponse, productData[firstKey]),
                                hasEmail = dataRenderer.has_email,
                                htmlForm = '';

                            if (!hasEmail) {
                                var template = mageTemplate(templateId);
                                htmlForm = template({
                                    data: dataRenderer
                                });
                            } else {
                                var templateCancel = mageTemplate(templateCancelid);
                                htmlForm = templateCancel({
                                    data: dataRenderer
                                });
                            }

                            $(parent.element).html(htmlForm).trigger('contentUpdated');
                        } else if (type == 'grouped') {
                            var cleanDataResponse = _.omit(dataResponse, 'product_data'),
                                htmlForm = '';

                            _.forEach(productData, function ($val, $key) {
                                var dataRenderer = helper.mergeObject(cleanDataResponse, productData[$key]),
                                    hasEmail = dataRenderer.has_email,
                                    classSelector = '#stock-notice-elem-grouped-' + $val.product_id;

                                if (!hasEmail) {
                                    var template = mageTemplate(templateId);
                                    htmlForm += template({
                                        data: dataRenderer
                                    });
                                } else {
                                    var templateCancel = mageTemplate(templateCancelid);
                                    htmlForm += templateCancel({
                                        data: dataRenderer
                                    });
                                }
                            });

                            $(parent.element).html(htmlForm).trigger('contentUpdated');
                        } else if (type == 'bundle') {
                            bundleModel(dataResponse);
                        } else if (type == 'configurable') {
                            configurableModel(dataResponse);
                        }
                    }
                    return;
                });
            }
        },
        /**
         * Set ajax url
         * @param url
         * @returns {exports}
         */
        setFormDataActionUrl: function (url) {
            this.formDataActionUrl(url);
            return this;
        },
        /**
         * Get ajax url
         * @return {string}
         */
        getFormDataActionUrl: function () {
            return this.formDataActionUrl();
        }
    }
});
