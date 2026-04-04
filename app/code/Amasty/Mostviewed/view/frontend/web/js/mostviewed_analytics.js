/**
 * Amasty related products widget analytics
 */

define([
    'jquery',
    'underscore',
    'mage/cookies'
], function ($, _) {
    'use strict';

    $.widget('am.relatedAnalytics', {
        options: {
            blockId: null,
            clickUrl: null,
            relatedProducts: null,
            viewUrl: null
        },
        selectors: {
            item: '.product-item',
            priceBox: '[data-role="priceBox"]',
            cartButton: 'data-amrelated-button'
        },
        classes: {
            clicked: '-amrelated-clicked'
        },
        handler: 'mousedown.amRelated touchstart.amRelated',
        clickCounterTimeout: 1000,

        /**
         * @private
         * @returns {void}
         */
        _create: function () {
            this.viewsCounter();
            this.clicksCounter();
        },

        /**
         * @private
         * @param {Number|Boolean} productId
         * @param {Boolean} toCart
         * @returns {void}
         */
        _ajaxHandler: function (productId, toCart) {
            $.ajax({
                url: this.options[productId ? 'clickUrl' : 'viewUrl'],
                data: {
                    product_id: productId,
                    block_id: this.options.blockId,
                    cart_click: toCart ? 1 : 0,
                    form_key: $.mage.cookies.get('form_key')
                },
                type: 'get'
            });
        },

        /**
         * @private
         * @returns {void}
         */
        _intersectionObserver: function () {
            var self = this,
                loadObserver,
                params = {
                    root: null,
                    rootMargin: '50px',
                    threshold: 0
                };

            loadObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        self._ajaxHandler(false, false);
                        loadObserver.disconnect();
                    }
                });
            }, params);

            loadObserver.observe(self.element[0]);
        },

        /**
         * @param {Object} event
         * @returns {void}
         */
        _clickCounterTimeout: function (event) {
            var self = this,
                timeout = _.debounce(function () {
                    $(event.currentTarget).removeClass(self.classes.clicked);
                }, self.clickCounterTimeout);

            $(event.currentTarget).addClass(self.classes.clicked);
            timeout();
        },

        /**
         * @returns {void}
         */
        clicksCounter: function () {
            var self = this,
                productId,
                toCart,
                isAddToCart,
                isLink;

            this.element.find(self.selectors.item).off(self.handler).on(self.handler, function (event) {
                productId = $(event.currentTarget).find(self.selectors.priceBox).data('product-id');
                toCart = $(event.target).attr(self.selectors.cartButton) === 'tocart';
                isAddToCart = toCart && event.button === 0;
                isLink = event.target.localName === 'a'
                    || ((event.target.localName === 'img' || event.target.localName === 'span')
                    && event.target.parentElement.localName === 'a');

                if (!(isLink || isAddToCart)) {
                    return;
                }

                if ($(event.currentTarget).hasClass(self.classes.clicked)) {
                    return;
                }

                if (!self.options.relatedProducts || self.options.relatedProducts.indexOf(productId) !== -1) {
                    self._ajaxHandler(productId, toCart);
                    self._clickCounterTimeout(event);
                }
            });
        },

        /**
         * @returns {void}
         */
        viewsCounter: function () {
            // eslint-disable-next-line no-unused-expressions
            'IntersectionObserver' in window ? this._intersectionObserver() : this._ajaxHandler(false, false);
        }
    });

    return $.am.relatedAnalytics;
});
