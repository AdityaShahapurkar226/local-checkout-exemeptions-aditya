define([
    'underscore',
    'Magento_Ui/js/form/element/abstract',
], function (_, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            valueUpdate: 'afterkeydown',
        },
        initialize: function () {
            this._super();
            this.value(this.normalizePrice());
            return this;
        },
        onUpdate: function () {
            this._super();
            this.value(this.normalizePrice());
            return this;
        },
        normalizePrice: function () {
            var value = this.value();
            var regexp = /\d+.+\d/;
            var result = regexp.exec(value);
            return result[0];
        }
    });
});