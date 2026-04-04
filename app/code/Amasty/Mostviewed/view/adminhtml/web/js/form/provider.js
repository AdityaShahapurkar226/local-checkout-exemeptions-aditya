define([
    'jquery',
    'Magento_Ui/js/form/provider'
], function ($, Element) {
    'use strict';

    return Element.extend({
        /**
         * Saves currently available data.
         *
         * @param {Object} [options] - Addtitional request options.
         * @returns {Provider} Chainable.
         */
        save: function (options) {
            var data = this.get('data');

            /* delete unused data */
            delete data.parent_ids;
            delete data.child_products_container;

            var newParent = [];
            $(data.parent_products_container).each(function (i, item) {
                newParent[i] = {'entity_id' : item.entity_id} // remove other data
            });
            data.parent_products_container = newParent;

            this.client.save(data, options);

            return this;
        }
    });
});
