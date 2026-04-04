define(['jquery', 'ko'], function ($, ko) {
    'use strict';

    return {
        selectedMethods: ko.observableArray([]),
        shippingAmount: ko.observable(),
    }
});
