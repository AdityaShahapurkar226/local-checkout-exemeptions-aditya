define([
    'Magento_SalesRule/js/action/set-coupon-code',
    'Magento_SalesRule/js/model/payment/discount-messages',
    'mage/translate'
], function (action, messageContainer, $t) {
    if (typeof action.registerSuccessCallback === 'function') {
        var message = $t('Your coupon was successfully applied. Coupon discount will not be applied to products from bundle pack.');

        action.registerSuccessCallback(function (response) {
            if (response && window.checkoutConfig.applied_bundle_packs) {
                messageContainer.addSuccessMessage({
                    'message': message
                });
            }
        });
    }
});
