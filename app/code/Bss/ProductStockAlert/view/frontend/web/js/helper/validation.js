define([
    'jquery'
], function ($) {
    'use strict';

    return {
        isEmailValid: function (e) {
            var _this = e,
                self = this,
                formElem = $(_this).closest('form.stockalert-form'),
                $email = formElem.find('input.stockalert_email.validate-email'),
                $emailVal = $email.val();
            formElem.find('.mage-error').each(function () {
                $(this).empty();
                $(this).remove();
            });
            if (!self.testRequired($emailVal)) {
                var $temp = '<div class="mage-error" generated="true">Email address is required.</div>';
                $($temp).insertAfter($email);
                $('#product_stock_alert_container').trigger('contentUpdated');
                return false;
            }
            if (!self.testEmail($emailVal)) {
                var $temp = '<div class="mage-error" generated="true">Please enter valid email address.</div>';
                $($temp).insertAfter($email);
                $('#product_stock_alert_container').trigger('contentUpdated');
                return false;
            }
            return true;
        },
        // Test empty value
        testRequired: function (value) {
            return !(value === '' || (value == null) || (value.length === 0) || /^\s+$/.test(value));
        },
        // Test email format
        testEmail: function (email) {
            return /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(email);
        }
    }
});
