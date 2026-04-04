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
    'Bss_ProductStockAlert/js/model/product/bundle',
    'Bss_ProductStockAlert/js/helper/data',
    'mage/template',
    'Bss_ProductStockAlert/js/components/stock-notice'
], function ($, _, ko, bundleModel, helper, mageTemplate) {
    'use strict';

    var bundleSlideMixin = {
        loaded: 1, // 0 => html has passed, just do show and hide, other vals => re-build from data
        loadedHtml: '',
        stockContainer: '#product_stock_alert_container',
        /**
         * Prepare data before slide down
         * @return {*}
         * @private
         */
        _create: function () {
            this._super();
            var bundleData = bundleModel(),
                self = this;
            if (bundleModel().length || _.size(bundleModel())) {
                return this;
            }
            var i = 1;
            var interval = setInterval(function () {
                i++;
                if (bundleModel().length || _.size(bundleModel())) {
                    clearInterval(interval);
                    if (self.options.slidedown === true) {
                        $(self.options.slideSelector).on('click', $.proxy(self._show, self));
                        $(self.options.slideBackSelector).on('click', $.proxy(self._hide, self));
                        self.options.autostart && self._show();
                    } else {
                        $(self.options.slideSelector).on('click', $.proxy(self._slide, self));
                        $(self.options.slideBackSelector).on('click', $.proxy(self._slideBack, self));
                        self.options.autostart && self._slide();
                    }
                }
                if (i === 100) {
                    // 5s load. If bundle data still not available,
                    // then stop interval
                    // this prevent infinite load
                    clearInterval(interval);
                }
            }, 50);
            return this;
        },
        /**
         * @inheritDoc
         * @private
         */
        _show: function () {
            var self = this;
            var bundleData = bundleModel();

            if (bundleModel().length || _.size(bundleModel())) {
                if (this.loaded === 0) {
                    // Element has loaded, just do show action
                    $(this.stockContainer).show();
                } else {
                    this._super();
                    // Element not available, so we must re-build it
                    if (this.loadedHtml === '') {
                        var templateId = '#bss-stock-notice-form';
                        var templateCancelid = '#bss-stock-notice-cancel-form';
                        var productData = bundleData.product_data;
                        var cleanDataResponse = _.omit(bundleData, 'product_data'),
                            htmlForm = '';

                        _.forEach(productData, function ($val, $key) {
                            var dataRenderer = helper.mergeObject(cleanDataResponse, productData[$key]),
                                hasEmail = dataRenderer.has_email,
                                classSelector = '#stock-notice-elem-bundle-' + $val.product_id;
                            if ($(classSelector).length) {
                                dataRenderer.show_title = 0; // Hide form title that show text 'Notify message'
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
                            }
                        });
                        if (htmlForm !== '') {
                            $(self.stockContainer).html(htmlForm).trigger('contentUpdated');
                            self.loadedHtml = htmlForm;
                        }
                    } else {
                        $(self.stockContainer).html(self.loadedHtml).trigger('contentUpdated');
                    }
                    this.loaded = 0
                }
            } else {
                return false;
            }
        },
        /**
         * @inheritDoc
         * @private
         */
        _hide: function () {
            this._super();
            $(this.stockContainer).hide();
            this.loaded = 0;
        }
    };

    /**
     * @inheritDoc
     */
    return function (bundleSlide) {
        $.widget('mage.slide', bundleSlide, bundleSlideMixin);

        return $.mage.slide;
    }
});
