/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global RegionUpdater */
define([
    'jquery',
    'mage/template',
    'jquery/ui',
    'mage/adminhtml/form'
], function ($, mageTemplate) {
    'use strict';

    $.widget('mage.fptAttribute', {
        /** @inheritdoc */
        _create: function () {
            var widget = this;
            
            if ($(this.options.bundlePriceType).val() === '0') {
                this.element.hide();
            }
            var options = this.options;
            $(".add-fpt-item").click(function(event){
                widget.addItem(event,options);
            });
            if (this.options.fptAttribute!=undefined) {
                $.each(this.options.fptAttribute.itemsData, function (index, element) {
                    widget.addItem(element,options);
                });
            }
        },

        /**
         * Add custom option.
         *
         * @param {jQuery.Event} event
         */
        addItem: function (event,options) {
            var data = {},
                currentElement = event.target || event.srcElement || event.currentTarget,
                tmpl;
            
            if (typeof currentElement !== 'undefined') {
                data['website_id'] = 0;
            } else {
                data = event;
            }
            data.index = $('body').find('[data-role="fpt-item-row"]').length;
            
            var progressTmpl = mageTemplate('#tax-row-template'),
            tmpl;
            tmpl = progressTmpl({
                data: data
            });
            
            $(tmpl).appendTo(('.fpt-item-container'));
            $(".delete-fpt-item-row").on("click",function(event){       
                var parent = $(event.target).closest('[data-role="fpt-item-row"]');
                parent.find('[data-role="delete-fpt-item"]').val(1);
                parent.addClass('ignore-validate').hide(); 
            });

            $(document).on("change",".select-country",function(event){
                var currentElement = event.target || event.srcElement || event.currentTarget,
                        parentElement = $(currentElement).closest('[data-role="fpt-item-row"]'),
                        updater;

                    data = data || {};
                    updater = new RegionUpdater(
                        parentElement.find('[data-role="select-country"]').attr('id'), null,
                        parentElement.find('[data-role="select-state"]').attr('id'),
                        options.fptAttribute.region, 'disable', true
                    );
                    updater.update();
                    //set selected state value if set
                    if (data.state) {
                        parentElement.find('[data-role="select-state"]').val(data.state);
                    }
            })
            
            //set selected website_id value if set
            if (data['website_id']) {
                $('body').find('[data-role="select-website"][id$="_' + (data.index) + '_website"]')
                    .val(data['website_id']);
            }

            //set selected country value if set
            if (data.country) {
                $('body').find('[data-role="select-country"][id$="_' + (data.index) + '_country"]')
                    .val(data.country).trigger('change', data);
            }
        }
    });

    return $.mage.fptAttribute;
});
