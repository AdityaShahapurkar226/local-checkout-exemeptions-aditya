/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 /*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm'
], function ($, $t, alert, confirm) {
    'use strict';
    $.widget('mage.subAccountJs', {
        _create: function () {
            var self = this;

            $('body').on('click', '.mp-sub-account-edit', function () {
                var $url=$(this).attr('data-url');
                confirm({
                    content: $t(" Are you sure you want to edit this sub account? "),
                    actions: {
                        confirm: function () {
                            window.location = $url;
                        },
                        cancel: function () {
                            return false;
                        }
                    }
                });
            });
            $('#sub-account-mass-delete').click(function (e) {
                var flag =0;
                $('.mpcheckbox').each(function () {
                    if (this.checked === true) {
                        flag =1;
                    }
                });
                if (flag === 0) {
                    alert({content : $t(' No Checkbox is checked ')});
                    return false;
                } else {
                    confirm({
                        content: $t(" Are you sure you want to delete these sub account(s)? "),
                        actions: {
                            confirm: function () {
                                $('#form-sub-account-delete').submit();
                            },
                            cancel: function () {
                                return false;
                            }
                        }
                    });
                }
            });
            $('#sub-account-select-all').click(function (event) {
                if (this.checked) {
                    $('.mpcheckbox').each(function () {
                        this.checked = true;
                    });
                } else {
                    $('.mpcheckbox').each(function () {
                        this.checked = false;
                    });
                }
            });

            $('.mpcheckbox').click(function (event) {
                var massEnable = 1;
                $('.mpcheckbox').each(function () {
                    if (this.checked == false) {
                        massEnable = 0;
                        return false;
                    }
                });
                $('#sub-account-select-all').each(function () {
                    if (massEnable == 0) {
                        this.checked = false;
                    } else {
                        this.checked = true;
                    }
                });
            });

            $('.mp-sub-account-delete').click(function () {
                var $url=$(this).attr('data-url');
                confirm({
                    content: $t(" Are you sure you want to delete this sub account? "),
                    actions: {
                        confirm: function () {
                            window.location = $url;
                        },
                        cancel: function () {
                            return false;
                        }
                    }
                });
            });
        }
    });
    return $.mage.subAccountJs;
});
