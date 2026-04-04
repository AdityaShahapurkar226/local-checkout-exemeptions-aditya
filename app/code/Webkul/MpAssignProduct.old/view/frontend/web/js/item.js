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
'Magento_Ui/js/modal/alert',
"jquery/ui",
], function ($, alert) {
    'use strict';
    $.widget('mpassignproduct.item', {
        options: {},
        _create: function () {
            var self = this;
            $(document).ready(function () {
                var isConfig = self.options.isConfig;
                var img = self.options.defaultImage;
                var msg = self.options.msg;
                var is_new = self.options.is_new;

                var result = [];
                var count = self.options.count;
                var defaultCount = count;
                var blockHtml = self.options.blockHtml;
                var img = "";
                var error = false;
                var acceptedImageType = ["png", "jpg", "jpeg", "gif"];

                $(document).on('click', '.wk-ap-ap-img-del', function (event) {
                    $(".wk-ap-ap-img-box img").attr("src", img);
                    $("#del").val(1)
                });
                $(document).on('change', '.wk-ap-ap-img-box img', function (event) {
                    $("#del").val(0)
                });
                $(document).on('click', '.wk-associate-chkbox', function (event) {
                    if ($(this).prop('checked')) {
                        $(this).parent().parent().find(".wk-associate-price").addClass("required-entry");
                        $(this).parent().parent().find(".wk-associate-price").addClass("validate-zero-or-greater");
                        $(this).parent().parent().find(".wk-associate-qty").addClass("required-entry");
                        $(this).parent().parent().find(".wk-associate-qty").addClass("validate-number");
                    } else {
                        $(this).parent().parent().find(".wk-associate-price").removeClass("required-entry");
                        $(this).parent().parent().find(".wk-associate-price").removeClass("validate-zero-or-greater");
                        $(this).parent().parent().find(".wk-associate-price").removeClass("mage-error");
                        $(this).parent().parent().find(".wk-associate-qty").removeClass("required-entry");
                        $(this).parent().parent().find(".wk-associate-qty").removeClass("validate-number");
                        $(this).parent().parent().find(".wk-associate-qty").removeClass("mage-error");
                    }
                });
                $(document).on('submit', '#wk_mpassignproduct_form', function (event) {
                    if (isConfig == 1) {
                        var count = 0;
                        $(".wk-associated-table tbody tr").each(function () {
                            if ($(this).find(".wk-associate-chkbox").prop('checked')) {
                                count++;
                            }
                        });
                        if (count == 0) {
                            alert({
                                title: 'Warning',
                                content: "<div class='wk-ap-warning-content'>"+msg+"</div>",
                                actions: {
                                    always: function (){}
                                }
                            });
                            return false;
                        } else {
                            if ($('#wk_mpassignproduct_form').valid()!==false) {
                                $('.wk-product-save-btn').prop('disabled',true);
                            }
                        }
                    }
                });
                $("body").on("change", ".wk-showcase-img", function () {
                    var imageName = $(this).val();
                    var result = imageName.split(".");
                    var length = result.length;
                    var currentThis = $(this);
                    var ext = result[length-1];
                    ext = ext.toLowerCase();
                    if (acceptedImageType.indexOf(ext)!=-1) {
                        if (this.files && this.files[0]) {
                            var reader = new FileReader();
                            reader.onload = function (e) {
                                var img = "<img src='"+e.target.result+"'>"
                                currentThis.prev().remove();
                                currentThis.before(img);
                                currentThis.parent().find(".wk-base-image-block").addClass("wk-display-inline-block");
                                error = false;
                                count++;
                                currentThis.parent().find(".wk-delete-item").attr("data-id", count);
                            }
                            reader.readAsDataURL(this.files[0]);
                        }
                    } else {
                        alert("Invalid Image Format \njpeg, jpg, gif, png are accepted");
                        currentThis.val('');
                    }
                });

                $("body").on("click", ".wk-add-showcase-btn", function () {
                    if (!error) {
                        error = true;
                        $(".wk-showcase-container").append(blockHtml);
                    } else {
                        alert("Please select image first");
                    }
                });
                $("body").on("click", ".wk-default-block", function () {
                    $(this).next().trigger("click");
                });

                $("body").on("click", ".wk-delete-item", function () {
                    var id = $(this).attr("data-id");
                    if (id > 0) {
                        if (id <= defaultCount) {
                            result.push(id);
                        }
                    } else {
                        error = false;
                    }
                    $(this).parent().remove();
                    $("#delete_ids").val(result.join());
                });

                $("body").on("click", ".wk-product-save-btn", function () {
                    if (error) {
                        alert("Please select image");
                        return false;
                    }
                    var total = 0;
                    $(".wk-base-image-input").each(function () {
                        total++;
                    });
                    $("#total").val(total);
                    var counter = 1;
                    $(".wk-base-image-input").each(function () {
                        var prop = $(this).prop("checked");
                        if (prop) {
                            $("#base_image").val(counter);
                        }
                        counter++;
                    });
                    if ($('#wk_mpassignproduct_form').valid()!==false) {
                        $(this).attr('disabled', 'disabled');
                        $('#wk_mpassignproduct_form').submit();
                    }
                });
                $("body").on("click", ".wk-base-image-input", function () {
                    var prop = $(this).prop("checked");
                    $(".wk-base-image-input").prop("checked", false);
                    if (prop) {
                        $(this).prop("checked", true);
                    } else {
                        $(this).prop("checked", false);
                    }
                });

                $("body").on("click", ".wk-mass-associate", function () {
                    var prop = $(this).prop("checked");
                    if (prop) {
                        $(".wk-associate-chkbox").prop("checked", false);
                    } else {
                        $(".wk-associate-chkbox").prop("checked", true);
                    }
                    $(".wk-associate-chkbox").trigger("click");
                });
                $('document').ready(function () {
                    var k=0;
                    $('.wk-associate-chkbox').each(function () {
                        if (!$(this).prop("checked")) {
                            k=1;
                        }
                        if (k == 0) {
                            $(".wk-mass-associate").prop("checked", true);
                        }
                    });

                    $('.wk-associate-chkbox').change(function () {
                        var k=0;
                        $('.wk-associate-chkbox').each(function () {
                            if (!$(this).prop("checked")) {
                                k=1;
                            }
                            if (k == 0) {
                                $(".wk-mass-associate").prop("checked", true);
                            } else {
                                $(".wk-mass-associate").prop("checked", false);
                            }
                        });
                    });
                });
            });
        }
    });
    return $.mpassignproduct.item;
});
