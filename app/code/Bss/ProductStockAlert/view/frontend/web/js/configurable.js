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
    'mage/template',
    'Bss_ProductStockAlert/js/model/product/configurable',
    'Bss_ProductStockAlert/js/helper/data',
    'mage/translate'
], function ($, _, mageTemplate, configurableModel, helper) {
    'use strict';
    return function (widget) {

        $.widget('mage.configurable', widget, {
            /**
             * Get product id
             * @param element
             * @return {undefined|*}
             * @private
             */
            _getSimpleProductIdBss: function (element) {
                // TODO: Rewrite algorithm. It should return ID of
                //        simple product based on selected options.
                var allOptions = element.config.options,
                    value = element.value,
                    config;

                config = _.filter(allOptions, function (option) {
                    return option.id === value;
                });
                config = _.first(config);

                if (!_.isEmpty(config) && config.allowedProducts.length === 1) {
                    return _.first(config.allowedProducts);
                } else {
                    return undefined;
                }

            },

            /**
             * Configure an option, initializing it's state and enabling related options, which
             * populates the related option's selection and resets child option selections.
             * @private
             * @param {*} element - The element associated with a configurable option.
             */
            _configureElement: function (element) {
                this._super(element);
                this.simpleProductBss = this._getSimpleProductIdBss(element);
                this._UpdateDetailStock($(element));
                this._UpdateDetailStockForm();
            },

            /**
             * Update stock notice form every time click swatches
             * @private
             */
            _UpdateDetailStockForm: function () {
                var $widget = this,
                    index = null;
                index =  $widget.simpleProductBss;
                var templateId = '#bss-stock-notice-form';
                var templateCancelid = '#bss-stock-notice-cancel-form';
                var element = '#product_stock_alert_container';

                if (index !== null && index && !isNaN(parseInt(index))) {
                    var confiurableData = configurableModel();
                    if ((confiurableData.length || _.size(confiurableData))) {
                        var productData = null;
                        var simpleData = confiurableData['product_data'];
                        if (undefined !== simpleData[parseInt(index)]) {
                            productData = simpleData[parseInt(index)];
                        } else if (undefined !== simpleData[index]) {
                            productData = simpleData[index];
                        }
                        if (productData !== null) {
                            var confiurableData = _.omit(confiurableData, 'product_data'),
                                dataRenderer = helper.mergeObject(confiurableData, productData),
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

                            $(element).html(htmlForm).trigger('contentUpdated');
                        } else {
                            $(element).empty();
                        }
                    }
                }
            },

            /**
             * Update stock
             * @param $this
             * @return {boolean}
             * @private
             */
            _UpdateDetailStock: function ($this) {
                var $widget = this,
                    index = '',
                    childProductData = this.options.spConfig.productStockAlert;

                index = this.simpleProductBss;

                if (!childProductData['child'].hasOwnProperty(index)) {
                    $widget._ResetStock($this);
                    return false;
                }
                $widget._UpdateStock(
                    $this,
                    childProductData['child'][index]['stock_status'],
                    childProductData['child'][index]['action'],
                    childProductData['child'][index]['preorder']
                );
            },
            /**
             * Update stock
             * @param $this
             * @param $status
             * @param $action
             * @param $preorder
             * @private
             */
            _UpdateStock: function ($this, $status, $action, $preorder) {
                if ($status > 0) {
                    if ($this.parents('.product-item').length > 0) {
                        $this.parents('.product-item').find('.action.tocart').removeAttr('disabled');
                    } else {
                        $('#product-addtocart-button').removeAttr('disabled');
                        $('.container-child-product').html("");
                        $('.product-info-stock-sku .stock span').html("In Stock");
                    }
                } else {
                    if ($this.parents('.product-item').length > 0) {
                        if (!$preorder) {
                            $this.parents('.product-item').find('.action.tocart').attr('disabled', 'disabled');
                        }
                    } else {
                        if (!$preorder) {
                            $('#product-addtocart-button').attr('disabled', 'disabled');
                        }
                        $('.product-info-stock-sku .stock span').html("Out of Stock");
                    }
                }
            },
            /**
             * Reset stock
             * @param $this
             * @private
             */
            _ResetStock: function ($this) {
                if (this.options.spConfig.productStockAlert['stock_status'] > 0) {
                    if ($this.parents('.product-item').length > 0) {
                        $this.parents('.product-item').find('.action.tocart').removeAttr('disabled');
                    } else {
                        $('#product-addtocart-button').removeAttr('disabled');
                        $('.container-child-product').html("");
                    }
                } else {
                    if ($this.parents('.product-item').length > 0) {
                        $this.parents('.product-item').find('.action.tocart').attr('disabled', 'disabled');
                    } else {
                        $('#product-addtocart-button').attr('disabled', 'disabled');
                        $('.container-child-product').html("");
                    }
                }
            },
        });

        return $.mage.configurable;
    }
});
