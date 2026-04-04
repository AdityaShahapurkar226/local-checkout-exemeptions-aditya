/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    "underscore",
    "Magento_Catalog/js/price-utils",
    "jquery/ui",
    "mage/translate"
    ], function ($, _, utils) {
        'use strict';
        $.widget('mpassignproduct.view', {
            options: {},
            _create: function () {
                var self = this;
                $(document).ready(function () {
                    var isConfig = self.options.isConfig;
                    var productId = self.options.productId;
                    var formKey = self.options.formKey;
                    var ajaxUrl = self.options.url;
                    var dir = self.options.dir;
                    var defaultUrl = self.options.defaultUrl;
                    var sortOrder = self.options.sortOrder;
                    var btnHtml = self.options.btnHtml;
                    var jsonResult = self.options.jsonResult;
                    var symbol = self.options.symbol;
                    var itemWidth = self.options.itemWidth;
                    var superAttribute = {};
                    var customOptions = {};
                    var jsonData = {};
                    var min_price = self.options.min;
                    var assoc_qty = self.options.qty;
                    var minPriceProduct = self.options.minPriceProduct;
                    var showMinimumPrice = self.options.showMinimumPrice;
                    var minPriceToShow = self.options.minPriceToShow;
                    var mainConfigChilds = self.options.mainConfigChilds;
                    var customOptionsAvail = self.options.customOptionsAvail;
                    if (isConfig == 1) {
                        resetData(symbol);
                    }
                    $(document).on('click', '.wk-ap-add-to-cart', function (event) {
                        if (isConfig == 1) {
                            var associateId = $(this).attr("data-associate-id");
                            var formData = $('#product_addtocart_form').serializeArray();
                            var data = {};
                            $.each(formData, function ( key, value ) {
                                var atr = value.name;
                                var val = value.value;
                                if (atr.indexOf('super_attribute') === 0) {
                                    atr = atr.replace(/[^\d.]/g, '');

                                    superAttribute[atr] = val;
                                }
                            });
                            jsonData.super_attribute = superAttribute;
                            jsonData.associate_id = associateId;
                        }
                        var assignId = $(this).attr("data-id");
                        var assignProductId = $(this).attr("assign-product-id");
                        var qty = $(this).parent().find(".qty").val();
                        ajaxUrl = $(this).attr("addtocart-url");
                        jsonData.mpassignproduct_id = assignId;
                        jsonData.product = assignProductId;
                        var formData = $('#product_addtocart_form').serializeArray();
                        $.each(formData,function (i,v) {
                            if (v.name == 'form_key') {
                                formKey = v.value;
                            }
                        });
                        jsonData.form_key = formKey;
                        jsonData.qty = qty;
                        $(".wk-loading-mask").removeClass("wk-display-none");
                        $.ajax({
                            url: ajaxUrl,
                            type: 'POST',
                            data: jsonData,
                            success: function (data) {
                                if (data.backUrl) {
                                    location.reload();
                                }
                                $(".wk-loading-mask").addClass("wk-display-none");
                                ascrollto('maincontent');
                            }
                        });
                    });
                    $(document).on('change', '#list_sorter', function (event) {
                        var marker = "#wk_list_header";
                        var val = $(this).val();
                        if (val != "rating") {
                            val = "price";
                        }
                        var url = defaultUrl+"?list_order="+val+"&list_dir="+dir+marker;
                        location.href = url;
                    });
                    $(document).on('click', '#list_dir_asc', function (event) {
                        event.preventDefault();
                        var marker = "#wk_list_header";
                        var dir = "asc";
                        var url = defaultUrl+"?list_order="+sortOrder+"&list_dir="+dir+marker;
                        location.href = url;
                    });
                    $(document).on('click', '#list_dir_desc', function (event) {
                        event.preventDefault();
                        var marker = "#wk_list_header";
                        var dir = "desc";
                        var url = defaultUrl+"?list_order="+sortOrder+"&list_dir="+dir+marker;
                        location.href = url;
                    });
                    $('#product-options-wrapper .super-attribute-select').change(function () {
                       
                        resetData(symbol);
                        var flag = 1;
                        setTimeout(function () {
                            $("#product_addtocart_form input[type='hidden']").each(function () {
                                $('#product-options-wrapper .super-attribute-select').each(function () {
                                    if ($(this).val() == "") {
                                        flag = 0;
                                    }
                                });
                                var name = $(this).attr("name");
                                if (name == "selected_configurable_option") {
                                    var productId = $(this).val();
                                    
                                    if (productId != "" && flag ==1) {
                                        if (typeof jsonResult[productId] != "undefined") {
                                            $(".wk-table-product-list tbody tr").each(function () {
                                                var id = $(this).attr("data-id");
                                                var productUrl = $(this).attr("product-url");
                                                if (id) {
                                                    if (typeof jsonResult[productId][id] != "undefined") {
                                                        $(this).find(".wk-ap-product-price").html(symbol+jsonResult[productId][id]['price']);
                                                        var qty = jsonResult[productId][id]['qty'];
                                                        if (qty <= 0) {
                                                            var avl = $.mage.__("OUT OF STOCK");
                                                        } else {
                                                            var avl = $.mage.__("IN STOCK");
                                                            $(this).find(".wk-ap-action-col").html(btnHtml);
                                                            $(this).find(".wk-ap-add-to-cart").attr('data-id', id);
                                                            $(this).find(".wk-ap-add-to-cart").attr('addtocart-url', productUrl);
                                                            $(this).find(".wk-ap-add-to-cart").attr('data-associate-id', jsonResult[productId][id]['id']);
                                                        }
                                                        $(this).find(".wk-ap-product-avl").html(avl);
                                                    }
                                                } else {
                                                    var configChild = getConfigChild(mainConfigChilds, productId, 'getRow');

                                                    $(this).find(".wk-ap-product-price").html(utils.formatPrice(configChild.price));
                                                    var qty = configChild.stock;
                                                    if (qty <= 0) {
                                                        var avl = $.mage.__("OUT OF STOCK");
                                                    } else {
                                                        var avl = $.mage.__("IN STOCK");
                                                        $(this).find(".wk-ap-action-col").html(btnHtml);
                                                        $(this).find(".wk-ap-add-to-cart").attr('data-id', '');
                                                        $(this).find(".wk-ap-add-to-cart").attr('data-associate-id', '');
                                                    }
                                                    $(this).find(".wk-ap-product-avl").html(avl);
                                                }
                                            });
                                        }
                                    }
                                }
                            });
                        }, 0);
                    });

                    $("body").on("click", ".wk-ap-product-showcase-gallery-item img", function () {
                        $(".wk-ap-product-showcase-gallery-item").removeClass("wk-ap-active");
                        $(this).parent().addClass("wk-ap-active");
                        var src = $(this).attr("src");
                        $(".wk-ap-product-showcase-main img").attr("src", src);
                    });
                    $("body").on("click", ".wk-gallery-right", function () {
                        var currentObject = $(this);
                        var count = $(this).parent().find(".wk-ap-product-showcase-gallery-wrap").attr("data-count");
                        if (count > 5) {
                            var left = $(this).parent().find(".wk-ap-product-showcase-gallery-wrap").css("left");
                            left = left.replace('px', '');
                            left = parseFloat(left);
                            count = count-5;
                            var total = itemWidth*count;
                            var final = left+total;
                            if (final > 0) {
                                $(this).parent().find(".wk-ap-product-showcase-gallery-wrap").animate({ left: '-='+itemWidth+'px' }, 'slow', function () {
                                    checkRight(currentObject, itemWidth);
                                });
                            } else {
                                $(this).parent().find(".wk-ap-product-showcase-gallery-wrap").animate({ left: '-'+total+'px' });
                            }
                        }
                    });
                    $("body").on("click", ".wk-gallery-left", function () {
                        var currentObject = $(this);
                        var left = $(this).parent().find(".wk-ap-product-showcase-gallery-wrap").css("left");
                        left = left.replace('px', '');
                        left = parseFloat(left);
                        if (left >= 0) {
                            $(this).parent().find(".wk-ap-product-showcase-gallery-wrap").animate({ left: '0px' });
                        } else {
                            $(this).parent().find(".wk-ap-product-showcase-gallery-wrap").animate({left: '+='+itemWidth+'px' }, 'slow', function () {
                                checkLeft(currentObject, itemWidth);
                            });
                        }
                    });
                    $("body").on("click", ".wk-ap-product-image", function () {
                        var display = $(this).parent().find(".wk-ap-product-image-content").css("display");
                        $(".wk-ap-product-image-content").hide();
                        if (display == "none") {
                            $(this).parent().find(".wk-ap-product-image-content").show();
                        }
                        setTimeout(function () {
                            $(".wk-ap-product-image-content").trigger("click");
                        }, 100);
                    });
                    $("body").on("click",".mp-assign", function () {
                        $(".wk-ap-product-image-content").hide();
                    });
                    $("body").on("click", ".swatch-option", function () {
                        var optionId = $(this).data('option-id');
                        resetData(symbol);
                        var selected_options = {};
                        $(this).closest(".swatch-attribute").data('option-selected',optionId);
                        $('div.swatch-attribute').each(function (k,v) {
                            var attribute_id    = $(this).data('attribute-id');
                            var option_selected = $(this).data('option-selected');
                            if (!attribute_id || !option_selected) {
                                return;
                            }
                            selected_options[attribute_id] = optionId.toString();
                        });

                        var product_id_index = jQuery('[data-role=swatch-options]').data('mageSwatchRenderer').options.jsonConfig.index;
                        var found_ids = [];
                        jQuery.each(product_id_index, function (product_id, attributes) {
                            var productIsSelected = function (attributes, selected_options) {
                                return _.isEqual(attributes, selected_options);
                            }
                            if (productIsSelected(attributes, selected_options)) {
                                $(".wk-table-product-list tbody tr").each(function () {
                                    var id = $(this).attr("data-id");
                                    var productUrl = $(this).attr("product-url");
                                    if (id) {
                                        if (typeof jsonResult[product_id] != "undefined") {
                                            $(this).find(".wk-ap-product-price").html(symbol+jsonResult[product_id][id]['price']);
                                            var qty = jsonResult[product_id][id]['qty'];
                                            if (qty <= 0) {
                                                var avl = $.mage.__("OUT OF STOCK");
                                            } else {
                                                var avl = $.mage.__("IN STOCK");
                                                $(this).find(".wk-ap-action-col").html(btnHtml);
                                                $(this).find(".wk-ap-add-to-cart").attr('data-id', id);
                                                $(this).find(".wk-ap-add-to-cart").attr('addtocart-url', productUrl);
                                                $(this).find(".wk-ap-add-to-cart").attr('data-associate-id', jsonResult[product_id][id]['id']);
                                            }
                                            $(this).find(".wk-ap-product-avl").html(avl);
                                        }
                                    } else {
                                        var configChild = getConfigChild(mainConfigChilds, product_id, 'getRow');
                                        $(this).find(".wk-ap-product-price").html(utils.formatPrice(configChild.price));
                                        var qty = configChild.stock;
                                        if (qty <= 0) {
                                            var avl = $.mage.__("OUT OF STOCK");
                                        } else {
                                            var avl = $.mage.__("IN STOCK");
                                            $(this).find(".wk-ap-action-col").html(btnHtml);
                                            $(this).find(".wk-ap-add-to-cart").attr('data-id', '');
                                            $(this).find(".wk-ap-add-to-cart").attr('data-associate-id', '');
                                        }
                                        $(this).find(".wk-ap-product-avl").html(avl);
                                    }
                                });
                            }
                        });
                    });
                    $("body").on("click", ".swatch-attribute", function () {
                        resetData(symbol);
                        var selected_options = {};
                        jQuery('div.swatch-attribute').each(function (k,v) {
                            var attribute_id    = jQuery(v).data('attribute-id');
                            var option_selected = jQuery(v).data('option-selected');
                            if (!attribute_id || !option_selected) {
                                return;
                            }
                            selected_options[attribute_id] = option_selected.toString();

                        });

                        var product_id_index = jQuery('[data-role=swatch-options]').data('mageSwatchRenderer').options.jsonConfig.index;
                        var found_ids = [];
                        
                        jQuery.each(product_id_index, function (product_id, attributes) {
                            var productIsSelected = function (attributes, selected_options) {
                                return _.isEqual(attributes, selected_options);
                            }
                            if (productIsSelected(attributes, selected_options)) {
                                // found_ids.push(product_id);
                                $(".wk-table-product-list tbody tr").each(function () {
                                    var id = $(this).attr("data-id");
                                    var productUrl = $(this).attr("product-url");
                                    if (id) {
                                        if (typeof jsonResult[product_id] != "undefined") {
                                            $(this).find(".wk-ap-product-price").html(symbol+jsonResult[product_id][id]['price']);
                                            var qty = jsonResult[product_id][id]['qty'];
                                            if (qty <= 0) {
                                                var avl = $.mage.__("OUT OF STOCK");
                                            } else {
                                                var avl = $.mage.__("IN STOCK");
                                                $(this).find(".wk-ap-action-col").html(btnHtml);
                                                $(this).find(".wk-ap-add-to-cart").attr('data-id', id);
                                                $(this).find(".wk-ap-add-to-cart").attr('addtocart-url', productUrl);
                                                $(this).find(".wk-ap-add-to-cart").attr('data-associate-id', jsonResult[product_id][id]['id']);
                                            }
                                            $(this).find(".wk-ap-product-avl").html(avl);
                                        }
                                    } else {
                                        var configChild = getConfigChild(mainConfigChilds, product_id, 'getRow');
                                        $(this).find(".wk-ap-product-price").html(utils.formatPrice(configChild.price));
                                        var qty = configChild.stock;
                                        if (qty <= 0) {
                                            var avl = $.mage.__("OUT OF STOCK");
                                        } else {
                                            var avl = $.mage.__("IN STOCK");
                                            $(this).find(".wk-ap-action-col").html(btnHtml);
                                            $(this).find(".wk-ap-add-to-cart").attr('data-id', '');
                                            $(this).find(".wk-ap-add-to-cart").attr('data-associate-id', '');
                                        }
                                        $(this).find(".wk-ap-product-avl").html(avl);
                                    }
                                });
                            }
                        });
                    });
                });
                function resetData(symbol)
                {
                    var min_price = self.options.min;
                    var qty = self.options.qty;
                    var mainConfigChilds = self.options.mainConfigChilds;
                    $(".wk-table-product-list tbody tr").each(function () {
                        var id = $(this).attr("data-id");
                        if (id) {
                            $(this).find(".wk-ap-action-col").html('');
                            $(this).find(".wk-ap-product-price").html(symbol+min_price[id]);
                            if (qty[id]) {
                                $(this).find(".wk-ap-product-avl").html($.mage.__("IN STOCK"));
                            } else {
                                $(this).find(".wk-ap-product-avl").html($.mage.__("OUT OF STOCK"));
                            }
                        } else {
                            var configChild = getConfigChild(mainConfigChilds, '', 'getMinRow');
                            $(this).find(".wk-ap-action-col").html('');
                            $(this).find(".wk-ap-product-price").html(utils.formatPrice(configChild.price));
                            if (configChild.stock) {
                                $(this).find(".wk-ap-product-avl").html($.mage.__("IN STOCK"));
                            } else {
                                $(this).find(".wk-ap-product-avl").html($.mage.__("OUT OF STOCK"));
                            }
                        }
                    });
                }

                function checkLeft(currentObject, itemWidth)
                {
                    var left = currentObject.parent().find(".wk-ap-product-showcase-gallery-wrap").css("left");
                    left = left.replace('px', '');
                    left = parseFloat(left);
                    if (left >= 0) {
                        currentObject.parent().find(".wk-ap-product-showcase-gallery-wrap").animate({ left: '0px' });
                    }
                }

                function checkRight(currentObject, itemWidth)
                {
                    var count = currentObject.parent().find(".wk-ap-product-showcase-gallery-wrap").attr("data-count");
                    var left = currentObject.parent().find(".wk-ap-product-showcase-gallery-wrap").css("left");
                    left = left.replace('px', '');
                    left = parseFloat(left);
                    count = count-5;
                    var total = itemWidth*count;
                    var final = left+total;
                    if (final <= 0) {
                        currentObject.parent().find(".wk-ap-product-showcase-gallery-wrap").animate({ left: '-'+total+'px' });
                    }
                }
                function ascrollto(id)
                {
                    var etop = $('#' + id).offset().top;
                    $('html, body').animate({
                      scrollTop: etop-30
                    }, 10);
                }
                function getConfigChild(array, id, action)
                {
                    var returnData;
                    if (action == 'getRow') {
                        $.each(array, function (i, v) {
                            if (v.id == id) {
                                returnData = v;
                                return false;
                            }
                        });
                    }
                    if (action == 'getMinRow') {
                        var min = '';
                        $.each(array, function (i, v) {
                            if (min == '') {
                                min = parseFloat(v.price);
                                returnData = v;
                            }
                            if (parseFloat(v.price) < min) {
                                min = parseFloat(v.price);
                                returnData = v;
                            }
                        });
                    }
                    return returnData;
                }
            }
        });
        return $.mpassignproduct.view;
    });
