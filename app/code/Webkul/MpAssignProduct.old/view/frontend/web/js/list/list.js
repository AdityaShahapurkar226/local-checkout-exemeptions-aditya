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
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    "jquery/ui",
    "mage/translate",
    ], function ($, confirmation, alert) {
        'use strict';
        $.widget('mage.list', {
            options: {},
            _create: function () {
                var self = this;
                var assignInfo = self.options.assignInfo;
                var label = self.options.label;
                var msg = self.options.msg;
                var minPrice = self.options.minPrice;
                $(document).ready(function () {
                    $(".products ol.product-items > li.product-item").on('click', '.action.tocart', function (e) {
                        var currentObj = this;
                        e.preventDefault();
                        var productLink = $(this).parents('.product-item').find(".product-item-link").attr("href");
                        var formData = $(this).parents('form[data-role="tocart-form"]').serializeArray();
                        var ajaxUrl = $(this).parents('form[data-role="tocart-form"]').attr('action');
                        if (assignInfo[productLink]['stock']) {
                            $(currentObj).addClass('disabled');
                            $(currentObj).children('span').html($.mage.__('Adding...'));
                            var jsonData = {};
                            $.each(formData, function (i,v) {
                                if (v.name == "product") {
                                    jsonData.product = v.value;
                                }
                                if (v.name == "form_key") {
                                    jsonData.form_key = v.value;
                                }
                            });
                            jsonData.mpassignproduct_id = assignInfo[productLink]["minPriceProduct"]['id'];
                            jsonData.qty = 1;
                            $.ajax({
                                url: ajaxUrl,
                                type: 'POST',
                                data: jsonData,
                                success: function (data) {
                                    if ("backUrl" in data) {
                                        location.reload();
                                    }
                                    $(currentObj).children('span').html($.mage.__('Added'));
                                    $(currentObj).removeClass('disabled');
                                    $(currentObj).children('span').html($.mage.__('Add to Cart'));
                                }
                            });
                        } else {
                            $(this).parents('form[data-role="tocart-form"]').submit();
                        }
                    });
                    $(".products ol.product-items > li.product-item").each(function () {
                        var productLink = $(this).find(".product-item-link").attr("href");
                        if (assignInfo[productLink]["minPrice"] != 0) {
                            $(this).find(".price-container .price").html(assignInfo[productLink]["minPrice"]);
                        }
                        if (assignInfo[productLink]["totalSellers"] > 0) {
                            $(this).find(".price-box").after('<a href="'+productLink+"#wk_list_header"+'">'+assignInfo[productLink]["msg"]+'</a>');
                        }
                    });
                    $(".products-grid.wishlist ol.product-items > li.product-item").each(function () {
                        var productLink = $(this).find(".product-item-link").attr("href");
                        if (assignInfo[productLink]['stock']) {
                            $(this).find(".price-box").after('<a href="'+productLink+'">'+msg+'</a>');
                            $(this).find(".action.tocart").remove();
                        }
                    });
                    $("#product-comparison > tbody > tr > td.info").each(function () {
                        var productLink = $(this).find(".product-item-name > a").attr("href");
                        if (assignInfo[productLink]["totalSellers"] > 0) {
                            $(this).find(".price-box").after('<a href="'+productLink+'">'+assignInfo[productLink]["msg"]+'</a>');
                        }
                    });
                });
            }
        });
        return $.mage.list;
    });
