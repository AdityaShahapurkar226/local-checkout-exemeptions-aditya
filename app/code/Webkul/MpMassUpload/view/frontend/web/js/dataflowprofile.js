/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 /*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    "jquery/ui",
    'mage/calendar'
], function ($, $t, alert, confirm) {
    'use strict';
    $.widget('mage.dataflowprofile', {
        _create: function () {
            var self = this;
            $("#form-dataflow-profile").submit(function () {
                if ($(this).valid()) {
                    $(this).submit(function () {
                        return false;
                    });
                    return true;
                }
                else {
                    return false;
                }
            });
            $('body').on('click', '.mp-dataflow-profile-edit', function () {
                var $url=$(this).attr('data-url');
                confirm({
                    content: $t(" Are you sure you want to edit selected dataflow profile? "),
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
            $('#dataflow-profile-mass-delete').click(function (e) {
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
                        content: $t(" Are you sure you want to delete selected dataflow profile(s)? "),
                        actions: {
                            confirm: function () {
                                $('#form-dataflow-profile-delete').submit();
                            },
                            cancel: function () {
                                return false;
                            }
                        }
                    });
                }
            });

            $('#dataflow-profile-select-all').click(function (event) {
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
                $('#dataflow-profile-select-all').each(function () {
                    if (massEnable == 0) {
                        this.checked = false;
                    } else {
                        this.checked = true;
                    }
                });
            });

            $('.mp-dataflow-profile-delete').click(function () {
                var $url=$(this).attr('data-url');
                confirm({
                    content: $t(" Are you sure you want to delete selected dataflow profile? "),
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

            $('body').on('click', '.wk-fieldmap-row-add', function () {
                var obj = $('#wk-fieldmap-template').html();
                $('#wk-fieldmap-container').append(obj);
            });

            $('body').on('click', '.wk-fieldmap-row-delete', function () {
                $(this).parents('.field-row').remove();
            });

            $('body').on('change', '.wk-fieldmap-attr-select', function () {
                var value = $(this).val();
                $(this).parents('.field-row').find('.wk-fieldmap-attr-input').val(value);
            });
        }
    });
    return $.mage.dataflowprofile;
});
