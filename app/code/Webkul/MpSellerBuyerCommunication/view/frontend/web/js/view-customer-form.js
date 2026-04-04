/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MpSellerBuyerCommunication
 * @author      Webkul
 * @copyright   Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "mage/template"
], function ($,$t, alert, mageTemplate) {
    'use strict';
    $.widget('mage.viewCustomerForm', {
        _create: function () {
            var self = this;
            var i=1;
            $('.wk-mp-btn').on('click', function (event) {
                event.preventDefault();
                $('.attach_count').val(i);
                var validateForm = $('#wk-customercommunication-save-form');
                if (validateForm.valid()!=false) {
                    $('#wk-customercommunication-save-form').submit();
                    $(this).attr('disabled','disabled');
                } else {
                    return false;
                }


            });

            $("body").on('change',".wk_imagevalidate",function () {
                var validExtensions = ['jpeg', 'jpg', 'png', 'gif', 'zip', 'doc', 'pdf', 'rar', 'xls', 'xlsx', 'csv'];
                var ext = $(this).val().split('.').pop().toLowerCase();
                if ($.inArray(ext, validExtensions) == -1) {
                    $(this).val('');
                    alert({content : $t('Invalid extension! allowed extension are '+validExtensions.join(', '))});
                }
            });


            $(document).find('.new_attachment').on('click',function (event) {
                event.preventDefault();
                i++;
                var progressTmpl = mageTemplate(self.options.attachmentTemplate),
                    tmpl;
                tmpl = progressTmpl({
                    fields: {
                        index: i
                    }
                });
                $('#otherimages').append(tmpl);
            });

            $(document).on('click','.remove_attch',function (e) {
                e.preventDefault();
                $(this).closest('div').remove();
                i--;
            });
        }
    });
    return $.mage.viewCustomerForm;
});
