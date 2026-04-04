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
"jquery/ui",
], function ($) {
    'use strict';
    $.widget('mpmassupload.profile', {
        options: {},
        _create: function () {
            var self = this;
            $(document).ready(function () {
                var skipCount = 0;
                var isMultiple = self.options.isMultiple;
                var importUrl = self.options.importUrl;
                var finishUrl = self.options.finishUrl;
                var total = self.options.productCount;
                var id = self.options.profileId;
                var sellerId = self.options.sellerId;
                var completeLabel = self.options.completeLabel;
                var deleteLabel = self.options.deleteLabel;
                var noProductImportLabel = self.options.noProductImportLabel;
                if (total > 0) {
                    var postData = self.options.postData;
                    importProduct(1, postData);
                }
                function importProduct(count, postData)
                {
                    $.ajax({
                        type: 'post',
                        url:importUrl,
                        async: true,
                        dataType: 'json',
                        data : postData,
                        success:function (data) {
                            if (data['error'] == 1) {
                                $(".fieldset").append(data['msg']);
                                skipCount++;
                            } else if (data['error'] == 2) {
                                $(".fieldset").append(data['msg']);
                                location.href = self.options.sellerGroupUrl;
                                return;
                            } else {
                                if (data['config_error'] == 1) {
                                    $(".fieldset").append(data['msg']);
                                }
                            }
                            var width = (100/total)*count;
                            $(".wk-mu-progress-bar-current").animate({width: width+"%"},'slow', function () {
                                if (total == 1 && skipCount ==1) {
                                    $(".fieldset").append('<div class="wk-mu-success wk-mu-box">'+noProductImportLabel+'</div>');
                                    $(".wk-mu-info-bar").addClass("wk-no-padding");
                                    $(".wk-mu-importing-loader").remove();
                                    $(".wk-mu-info-bar-content").text(completeLabel);
                                } else {
                                    if (count == total) {
                                        finishImporting(count, skipCount);
                                        $(".wk-mu-info-bar-content").text(deleteLabel);
                                    } else {
                                        count++;
                                        $(".wk-current").text(count);
                                        postData = data['next_row_data'];
                                        importProduct(count, postData);
                                    }
                                }
                            });
                        }
                    });
                }
                function finishImporting(count, skipCount)
                {
                    $.ajax({
                        type: 'get',
                        url:finishUrl,
                        async: true,
                        dataType: 'json',
                        data : { row:count, id:id, skip:skipCount },
                        success:function (data) {
                            $(".fieldset").append(data['msg']);
                            $(".wk-mu-info-bar").addClass("wk-no-padding");
                            $(".wk-mu-info-bar").text(completeLabel);
                        }
                    });
                }
            });
        }
    });
    return $.mpmassupload.profile;
});
