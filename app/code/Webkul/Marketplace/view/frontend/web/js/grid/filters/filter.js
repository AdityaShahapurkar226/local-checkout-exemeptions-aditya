/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

 define([
    'Magento_Ui/js/grid/filters/filters',
    'mageUtils'
], function (Filters,utils) {
    'use strict';
    /**
         * Extracts and formats preview of an element.
         *
         * @param {Object} elem - Element whose preview should be extracted.
         * @returns {Object} Formatted data.
         */
    function extractPreview(elem) {
        return {
            label: elem.label,
            preview: elem.getPreview(),
            elem: elem
        };
    }

    /**
     * Removes empty properties from the provided object.
     *
     * @param {Object} data - Object to be processed.
     * @returns {Object}
     */
    function removeEmpty(data) {
        var result = utils.mapRecursive(data, utils.removeEmptyValues.bind(utils));

        return utils.mapRecursive(result, function (value) {
            return _.isString(value) ? value.trim() : value;
        });
    }
    return Filters.extend({
        /**
         * Sets filters data to the applied state.
         *
         * @returns {Filters} Chainable.
         */
         apply: function () {
            this.set('applied', removeEmpty(this.filters));

            return this;
        },
        
    });
});
