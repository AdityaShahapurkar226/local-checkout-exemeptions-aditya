/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    "jquery/ui",
    ], function ($) {
        'use strict';
        $.widget('mage.profile', {
            options: {},
            _create: function () {
                var self = this;
                console.log('dfvg');
                $(document).ready(function () {
                    var skipCount = 0;
                    var importUrl = self.options.importUrl;
                    var finishUrl = self.options.finishUrl;
                    var total = self.options.productCount;
                    var id = self.options.profileId;
                    var deleteLabel = self.options.deleteLabel;
                    var completeLabel = self.options.completeLabel;
                    var noProductImportLabel = self.options.noProductImportLabel;
                    if (total > 0) {
                        var postData = self.options.postData;
                        importProduct(1, postData);
                    }
                    if(total == 0) { finishImporting(0, 0); }
                    function importProduct(count, postData)
                    {
                        $.ajax({
                            type: 'post',
                            url:importUrl,
                            async: true,
                            dataType: 'json',
                            data : postData,
                            success:function (data) {
                                console.log(data);
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
                                            finishImporting(count, skipCount, postData);
                                            $(".wk-mu-info-bar-content").text(deleteLabel);
                                        } else {
                                            count++;
                                            $(".wk-current").text(count);
                                            postData['row'] = data['next_row_data'];
                                            importProduct(count, postData);
                                        }
                                    }
                                });
                            }
                        });
                    }
                    function finishImporting(count, skipCount, postData)
                    {
                        $.ajax({
                            type: 'post',
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
        return $.mage.profile;
    });
    