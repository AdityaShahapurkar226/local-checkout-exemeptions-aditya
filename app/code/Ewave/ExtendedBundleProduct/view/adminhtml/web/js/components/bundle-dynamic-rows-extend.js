define([
    'underscore'
], function (_) {
    'use strict';

    var mixin = {
        /**
         * Initialize elements from grid
         *
         * @param {Array} data
         *
         * @returns {Object} Chainable.
         */
        initElements: function (data) {
            if (!_.isArray(data)) {
                data = [data];
            }

            return this._super(data);
        }
    };

    return function (target) {
        return target.extend(mixin);
    }
});
