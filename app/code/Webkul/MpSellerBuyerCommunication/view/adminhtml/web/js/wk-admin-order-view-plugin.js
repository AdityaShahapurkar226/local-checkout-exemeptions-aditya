/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MpSellerBuyerCommunication
 * @author      Webkul
 * @copyright   Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */
define([
    "jquery",
    'mage/translate',
    "mage/template",
    "Magento_Ui/js/modal/modal",
    "mage/adminhtml/wysiwyg/tiny_mce/setup"
], function ($, $t, mageTemplate, modal) {
    'use strict';
    $.widget('mage.wkAdminOrderViewPage', {
        _create: function () {
            var self = this;
            var askDataForm = $(self.options.formValidator);
            askDataForm.mage('validation', {});

            var options_ques = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: 'Contact Customer',
                buttons: [{
                        text: 'Reset',
                        class:'',
                        click: function () {
                            // $('#qa-ques-form input,#qa-ques-form textarea').removeClass('mage-error');
                            $('#ask-form')[0].reset();
                        } //handler on button click
                    },{
                        text: 'Submit',
                        class: 'wk-seller-buyer-askbtn clickask',
                        click: function () {
                            // -----save question

                        } //handler on button click
                    }
                ]
            };

            var popup = modal(options_ques, $('#wk-mp-ask-data'));

            $('.askque').click(function () {
                $('#ask-form input,#ask-form textarea').removeClass('mage-error');
                $('#ask-form div.mage-error').remove();
                $('#ask-form')[0].reset();
                $('.italic-info').remove();
                $('#wk-mp-ask-data').modal('openModal');
                var responseTime = $('<span>').addClass('italic-info').text(self.options.averageResponse+'-mins response time, ');
                var responseRate = $('<span>').addClass('italic-info').text(self.options.responseRate+'% response rate');
                // $('.modal-header').append(responseTime);
                // $('.modal-header').append(responseRate);
            });

            $('.field-tooltip-action').hover(
                function () {
                  $('.field-tooltip-content').show();
                },
                function () {
                $('.field-tooltip-content').hide();
                }
            );
            $('.wk-close').click(function () {
                $('.page-wrapper').css('opacity','1');
                $('#resetbtn').trigger('click');
                $('#wk-mp-ask-data').hide();
                $('#ask-form .validation-failed').each(function () {
                    $(this).removeClass('validation-failed');
                });
                $('#ask-form .validation-advice').each(function () {
                    $(this).remove();
                });
            });
            var i=2;
            $(document).find('.product_images').on('click',function () {
                if ($('body').find('.wk_imagevalidate').length >= 1) {
                    $('body').find('.wk_imagevalidate').siblings('.remove_attch').css("display", "inline-block");
                }
                var progressTmpl = mageTemplate(self.options.attachmentTemplate),
                    tmpl;
                tmpl = progressTmpl({
                    fields: {
                        index: i
                    }
                });
                $('#otherimages').append(tmpl);
                i++;
            });

            $(document).on('click','.remove_attch',function (e) {
                e.preventDefault();
                if ($('body').find('.wk_imagevalidate').length > 1) {
                    $(this).closest('div').remove();
                    i--;
                }
            });


            $("body").on('change',".wk_imagevalidate",function () {
                var validExtensions = ['jpeg', 'jpg', 'png', 'gif', 'zip', 'doc', 'pdf', 'rar'];
                var ext = $(this).val().split('.').pop().toLowerCase();
                if ($.inArray(ext, validExtensions) == -1 && ext != '') {
                    $(this).val('');
                    alert('Invalid extension! allowed extension are '+validExtensions.join(', '));
                }
            });

            var files;var it = 0;
            // Add events
            $(document).on('change','input[type=file]', prepareUpload);

            // Grabed the files and set them to the variable
            function prepareUpload(event)
            {
                files = event.target.files;
                prepareFormData(files);
            }

            var data = new FormData();
            function prepareFormData()
            {
                $.each(files, function (key, value) {
                    data.append('attachment_'+it, value);
                    it++;
                });
            }

            $(".ask_que").mouseover(function () {
                $(".wk-seller-response-container").show();
            }).mouseout(function () {
                $(".wk-seller-response-container").hide();
            });

            $('.wk-seller-buyer-askbtn').on('click', function () {
                if (askDataForm.valid()!=false) {
                    if ($('#queryquestion_ifr').length) {
                        var desc = $('#queryquestion_ifr').contents().find('body').text();
                        $('#queryquestion-error').remove();
                        if (desc === "" || desc === null) {
                            $('#queryquestion-error').remove();
                            $('#queryquestion').parent().find('.file').before('<div class="mage-error" generated="true" id="queryquestion-error">This is a required field.</div>');
                            return false;
                        }
                    }
                    var thisthis = $(this);
                    if (thisthis.hasClass("clickask")) {
                        if (self.options.captchenable == '1') {
                            var total = parseInt($('#wk-mp-captchalable1').text()) + parseInt($('#wk-mp-captchalable2').text());
                            var wk_mp_captcha = $('#wk-mp-captcha').val();
                            if (total != wk_mp_captcha) {
                                $('#wk-mp-captchalable1').text(Math.floor((Math.random()*10)+1));
                                $('#wk-mp-captchalable2').text(Math.floor((Math.random()*100)+1));
                                $('#wk-mp-captcha').val('');
                                $('#wk-mp-captcha').addClass('mage-error');
                                $(this).addClass('mage-error');
                                $('#ask_form .errormail').text(self.options.varificationMsg).slideDown('slow').delay(2000).slideUp('slow');
                            } else {
                                thisthis.removeClass('clickask');
                                $('#wk-mp-ask-data').addClass('mail-procss');
                                $.ajax({
                                    url:self.options.targetAjaxUrl,
                                    data:$('#ask-form').serialize(),
                                    type:'post',
                                    dataType:'json',
                                
                                    success:function (d) {
                                        if ($.isNumeric(d)) {
                                            $.ajax({
                                                url:self.options.saveAjaxFileUrl+'id/'+d+'/itr/'+it,
                                                data:  data,
                                                contentType: false,
                                                cache: false,
                                                processData:false,
                                                type:'POST',
                                                dataType:'json',
                                                success:function (d) {
                                                    thisthis.addClass('clickask');
                                                    $('#wk-mp-ask-data').removeClass('mail-procss')
                                                    if (d=="true") {
                                                        alert(' Message has been sent. ');
                                                    } else {
                                                        alert(' Mail Send but image/files is invalid ');
                                                    }
                                                    $('#resetbtn,.wk-close').trigger('click');
                                                    window.location.reload();
                                                }
                                            });
                                        }
                                    }
                                });
                            }
                        } else {
                            thisthis.removeClass('clickask');
                            $('#wk-mp-ask-data').addClass('mail-procss');
                            var form = $('#ask-form')[0];
                            var formData = new FormData(form);
                            
                            formData.append('files',data);
                            $.ajax({
                                url: self.options.targetAjaxUrl,
                                data:formData,
                                type:'post',
                                dataType:'json',
                                contentType: false,
                                cache: false,
                                processData:false,
                                success:function (d) {
                                    if ($.isNumeric(d)) {
                                        
                                        thisthis.addClass('clickask');
                                        $('#wk-mp-ask-data').removeClass('mail-procss')
                                        if ($.isNumeric(d)) {
                                            alert(' Message has been sent. ');
                                        } else {
                                            alert(' Mail Send but image/files is invalid ');
                                        }
                                        $('#resetbtn,.wk-close').trigger('click');
                                        window.location.reload();
                                    }
                                }
                            });
                        }
                    }
                    return false;
                }
            });

            var wysiwygDescription = new wysiwygSetup("queryquestion", {
                "width" : "100%",
                "height" : "200px",
                "plugins" : [{"name":"image"}],
                "add_images" :1,
                "tinymce4" : {
                    "toolbar":"formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap","plugins":"advlist autolink lists link charmap media image noneditable table contextmenu paste code help table",
                },
                files_browser_window_url: self.options.wysiwygConfig
            });
            wysiwygDescription.setup("exact");
        }
    });
    return $.mage.wkAdminOrderViewPage;
});
