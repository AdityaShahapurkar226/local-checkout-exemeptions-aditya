define([
    'Magento_Ui/js/form/components/fieldset'
], function (Element) {
    'use strict';

    return Element.extend({
        defaults: {
            visible: true
        },

        /**
         * Show element.
         *
         * @returns {Abstract} Chainable.
         */
        show: function () {
            this.visible(true);

            return this;
        },

        /**
         * Hide element.
         *
         * @returns {Abstract} Chainable.
         */
        hide: function () {
            this.visible(false);

            return this;
        }
    });
});
