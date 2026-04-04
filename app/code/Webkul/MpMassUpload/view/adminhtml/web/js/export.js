/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
"jquery",
'Magento_Ui/js/modal/alert',
"jquery/ui",
], function ($, alert) {
    'use strict';
    $.widget('mpmassupload.export', {
        options: {},
        _create: function () {
            var self = this;
            $(document).ready(function () {
                var defaultUrl = self.options.defaultUrl;
                var btnHtml = self.options.btnHtml;
                var sellerWarning = self.options.sellerWarning;
                var productTypeWarning = self.options.productTypeWarning;
                $("#product_type").after(btnHtml);
                if (self.options.showCustomAttributeField) {
                    $(".field-custom_attributes").show();
                } else {
                    $(".field-custom_attributes").hide();
                }
                
                $('#export-product').on('click', function () {
                    var sellerId = $("#seller_id").val();
                    var id = $("#product_type").val();
                    var customAttr = $("#custom_attributes").val();
                    if (sellerId == "") {
                        alert({
                            title: 'Warning',
                            content: sellerWarning,
                            actions: {
                                always: function (){}
                            }
                        });
                    } else if (id == "") {
                        alert({
                            title: 'Warning',
                            content: productTypeWarning,
                            actions: {
                                always: function (){}
                            }
                        });
                    } else {
                        var url = defaultUrl+"id/"+id+"/seller_id/"+sellerId+"/custom_attributes/"+customAttr;
                        window.open(url);
                    }
                });
                
            });
        }
    });
    return $.mpmassupload.export;
});