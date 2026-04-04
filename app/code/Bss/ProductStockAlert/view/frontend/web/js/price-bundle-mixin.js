define([
    'jquery',
    'underscore',
    'ko',
    'Bss_ProductStockAlert/js/model/product/bundle',
    'Bss_ProductStockAlert/js/helper/data',
    'mage/template',
], function ($, _, ko, bundleModel, helper, mageTemplate) {
    'use strict';

    var priceBundleMixin = {
        stockContainer: '#product_stock_alert_container',
        selectedProductIds: {},
        /**
         * Bundle option change
         * @param event
         * @private
         */
        _onBundleOptionChanged: function (event) {
            this._super(event);
            var bundleData = bundleModel();
            var productData = bundleData.product_data;
            var bundleOptions = this.options.optionConfig.options;
            var templateId = '#bss-stock-notice-form';
            var templateCancelid = '#bss-stock-notice-cancel-form';
            var cleanDataResponse = _.omit(bundleData, 'product_data'),
                htmlForm = '';
            var self = this;

            _.forEach(productData, function ($val, $key) {
                var optionId = parseInt($val.option_id);
                var productId = parseInt($val.product_id);
                var selecttionId = parseInt($val.selection_id);
                var optionType = $val.option_type;

                if (!_.isUndefined(bundleOptions[optionId])) { // If select or multi select has options
                    if (optionType == 'select') {
                        if (selecttionId == $(event.target).val()) { // If selected option is not salable

                            /**
                             * If notice-form already loaded.
                             * That mean, user selects out-of-stock product
                             * next he selects another in-stock product
                             * finally he re-select that out-of-stock product
                             * Then we will show it instead of load new form
                             */
                            if (_.isUndefined(self.selectedProductIds[productId])) { // Did not load case
                                var dataRenderer = helper.mergeObject(cleanDataResponse, productData[$key]),
                                    hasEmail = dataRenderer.has_email,
                                    classSelector = '#stock-notice-elem-bundle-select-' + $val.option_id;

                                dataRenderer.show_title = 0; // Hide form title that show text 'Notify message'
                                dataRenderer.option_id = $val.option_id;
                                dataRenderer.option_type = $val.option_type;

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
                                $(self.stockContainer).append(htmlForm).trigger('contentUpdated');
                                self.selectedProductIds[$val.product_id] = true;
                            } else { // Already loaded form, just show it
                                $('.block-stockalert[data-option="' + $val.option_id + '"]').show();
                                $('#stock-notice-elem-bundle-select-' + $val.option_id).show();
                                self.selectedProductIds[$val.product_id] = true;
                            }
                        } else { // If product is salable, we hide the notice form
                            if (_.size(self.selectedProductIds)) {
                                if ($('.block-stockalert[data-option="' + $val.option_id + '"]').length) {
                                    $('.block-stockalert[data-option="' + $val.option_id + '"]').hide();
                                    $('#stock-notice-elem-bundle-select-' + $val.option_id).hide();
                                    self.selectedProductIds[$val.product_id] = false;
                                }
                            }
                        }
                    } else if (optionType == 'multi') {
                        // Multi do nothing
                    }
                }
            });
        }
    };

    return function (priceBundle) {
        $.widget('mage.priceBundle', priceBundle, priceBundleMixin);

        return $.mage.priceBundle;
    }
});
