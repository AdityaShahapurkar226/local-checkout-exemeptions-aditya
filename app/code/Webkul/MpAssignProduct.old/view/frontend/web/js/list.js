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
'mage/translate',
'Magento_Ui/js/modal/confirm',
'Magento_Ui/js/modal/alert',
"jquery/ui",
], function ($, $t, confirmation, alert) {
    'use strict';
    $.widget('mpassignproduct.list', {
        options: {
            deleteAllData: $t("Are you sure, you want to delete these product(s)?")
        },
        _create: function () {
            var self = this;
            $(document).ready(function () {
                var isConfig = self.options.isConfig;
                var editTitle = self.options.editTitle;
                var editAction = self.options.editAction;
                var deleteTitle = self.options.deleteTitle;
                var deleteAction = self.options.deleteAction;
                var msg = self.options.msg;
                var confirmationMsg = self.options.deleteAllData;
                $(document).on('click', '.wk-ap-edit-item', function (event) {
                    var assignId = $(this).attr("data-id");
                    confirmation({
                        title: 'Confirmation',
                        content: "<div class='wk-ap-warning-content'>"+editTitle+"</div>",
                        actions: {
                            confirm: function () {
                                var url = editAction;
                                window.location.href = url+"id/"+assignId;
                            },
                            cancel: function (){},
                            always: function (){}
                        }
                    });
                });
                $(document).on('click', '.wk-ap-delete-item', function (event) {
                    var assignId = $(this).attr("data-id");
                    confirmation({
                        title: 'Confirmation',
                        content: "<div class='wk-ap-warning-content'>"+deleteTitle+"</div>",
                        actions: {
                            confirm: function () {
                                var url = deleteAction;
                                window.location.href = url+"id/"+assignId;
                            },
                            cancel: function (){},
                            always: function (){}
                        }
                    });
                });
                $('body').delegate('.wk-ap-del', 'click', function (event) {
                    var flag = 0;
                    $(".wk-ap-del-chkbox").each(function () {
                        if ($(this).is(':checked')) {
                            flag = 1;
                        }
                    });
                    if (flag == 0) {
                        alert({
                            title: 'Warning',
                            content: "<div class='wk-ap-warning-content'>"+msg+"</div>",
                            actions: {
                                always: function (){}
                            }
                        });
                        return false;
                    } else {
                        var dicision = confirmation({
                            content: confirmationMsg,
                            actions: {
                                confirm: function () {
                                    $('#wk_mpassignproduct_delete_form').submit();
                                },
                            }
                        });    
                    }
                });
                $('body').delegate('#mpselecctall', 'click', function (event) {
                    if (this.checked) {
                        $('.wk-ap-del-chkbox').each(function () {
                            this.checked = true;
                        });
                    } else {
                        $('.wk-ap-del-chkbox').each(function () {
                            this.checked = false;
                        });
                    }
                });
            });
        }
    });
    return $.mpassignproduct.list;
});