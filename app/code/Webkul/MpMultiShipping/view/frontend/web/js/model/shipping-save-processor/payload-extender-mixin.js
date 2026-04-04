define(['jquery', 'underscore', 'mage/utils/wrapper', 'Webkul_MpMultiShipping/js/model/multishipping'],
function ($, _, wrapper, multishipping) {
    'use strict';
    return function (payloadExtender) {
        return wrapper.wrap(payloadExtender, function (originalPayloadExtender, payload) {
            originalPayloadExtender(payload);
            _.extend(payload.addressInformation.extension_attributes,
                {
                    'selected_shipping': JSON.stringify(multishipping.selectedMethods()),
                    'multi_customship': multishipping.shippingAmount()
                }
            );
        });
    };
});
