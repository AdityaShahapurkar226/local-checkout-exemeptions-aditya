/* global Option */
define([
    'jquery',
    'underscore',
    'mage/template',
    'Magento_Catalog/js/price-utils',
    'mage/translate',
    'jquery/ui',
    'priceBundle',
    'mage/validation'
], function ($, _, mageTemplate, utils, $t) {
    'use strict';

    $.widget('mage.bundleConfigurable', {
        options: {
            superSelector: '.super-attribute-select',
            selectSimpleProduct: '[name="bundle_selected_configurable_option"]',
            priceHolderSelector: '#bundleSummary .price-box',
            state: {},
            spConfig: {},
            priceTemplate: '<span class="price"><%- data.formatted %></span>',
            productAddToForm: '#product_addtocart_form',
            bundleItem: '.bundle-item',
            bundleOption: null,
            selectionId: null,
            configurableOptions: {},
            tierPriceTemplateSelector: '#tier-prices-template',
            tierPriceBlockSelector: '[data-role="tier-price-block"]',
            tierPriceTemplate: ''
        },

        _create: function () {
            // Initial setting of various option values
            this._initializeOptions();

            // Change events to check select reloads
            this._setupChangeEvents();

            // Fill state
            this._fillState();

            // Initial setting of images
            this._initializeImages();

            // Setup child and prev/next settings
            this._setChildSettings();

            // Setup/configure values to inputs
            this._configureForValues();
        },

        /**
         * Initialize tax configuration, initial settings, and options values.
         * @private
         */
        _initializeOptions: function () {
            this.options.tierPriceTemplate = $(this.options.tierPriceTemplateSelector).html();
            this.options.settings = (this.options.containerId) ? $(this.options.containerId).find(this.options.superSelector) : $(this.options.superSelector);

            this.options.values = this.options.spConfig.defaultValues || {};
            this.inputSimpleProduct = this.element.find(this.options.selectSimpleProduct);
            if (this.options.inputType === 'select' && $('#bundle-option-' + this.options.bundleOption).length) {
                this.bundleField = $('#bundle-option-' + this.options.bundleOption + ' option[value="' + this.options.selectionId + '"]');
            } else {
                this.bundleField = $('#bundle-option-' + this.options.bundleOption + '-' + this.options.selectionId);
            }
            this.lockedProducts = this.getLockedProducts();
        },

        _initializeImages: function () {
            var option;
            if (this.bundleField.length) {
                if (this.options.inputType === 'select') {
                    option = this.bundleField.closest('select');
                } else {
                    option = this.bundleField;
                }
            } else {
                option = $('input#bundle-option-' + this.options.bundleOption);
            }

            $('[data-image*="' + option.attr('id') + '"]').each(function (i, item) {
                $(item).attr('data-original', $(item).attr('src'));
            });
        },

        /**
         * Set up .on('change') events for each option element to configure the option.
         * @private
         */
        _setupChangeEvents: function () {
            $.each(this.options.settings, $.proxy(function (index, element) {
                $(element).on('change', this, this._configure);
            }, this));
        },

        /**
         * Iterate through the option settings and set each option's element configuration,
         * attribute identifier. Set the state based on the attribute identifier.
         * @private
         */
        _fillState: function () {
            $.each(this.options.settings, $.proxy(function (index, element) {
                var attributeId = element.id.replace(/[a-z]*/, '').match(/[^-]*/g)[0];
                if (attributeId && this.options.spConfig.attributes[attributeId]) {
                    element.config = this.options.spConfig.attributes[attributeId];
                    this.removeLockedProducts(element.config);
                    element.attributeId = attributeId;
                    this.options.state[attributeId] = false;
                }
            }, this));
        },

        /**
         * Set each option's child settings, and next/prev option setting. Fill (initialize)
         * an option's list of selections as needed or disable an option's setting.
         * @private
         */
        _setChildSettings: function () {
            var childSettings = [],
                settings = this.options.settings,
                index = settings.length,
                option;

            while (index--) {
                option = settings[index];

                !index ? this._fillSelect(option) : (option.disabled = true);

                _.extend(option, {
                    childSettings: childSettings.slice(),
                    prevSetting: settings[index - 1],
                    nextSetting: settings[index + 1]
                });

                childSettings.push(option);
            }
        },

        /**
         * Setup for all configurable option settings. Set the value of the option and configure
         * the option, which sets its state, and initializes the option's choices, etc.
         * @private
         */
        _configureForValues: function () {
            if (this.options.values) {
                this.options.settings.each($.proxy(function (index, element) {
                    var attributeId = element.attributeId;
                    element.value = (typeof (this.options.values[attributeId]) === 'undefined') ? '' : this.options.values[attributeId];
                    this._configureElement(element);
                }, this));
            }
        },

        /**
         * Event handler for configuring an option.
         * @private
         * @param event Event triggered to configure an option.
         */
        _configure: function (event) {
            event.data._configureElement(this);
        },

        /**
         * Configure an option, initializing it's state and enabling related options, which
         * populates the related option's selection and resets child option selections.
         * @private
         * @param element The element associated with a configurable option.
         */
        _configureElement: function (element) {
            this.simpleProduct = this._getSimpleProductId(element);

            if (element.value) {
                this.options.state[element.config.id] = element.value;
                if (element.nextSetting) {
                    element.nextSetting.disabled = false;
                    this._fillSelect(element.nextSetting);
                    this._resetChildren(element.nextSetting);
                } else {
                    this.inputSimpleProduct.val(element.selectedOptions[0].config.allowedProducts[0]);
                }
            } else {
                this._resetChildren(element);
            }

            this._reloadPrice();
            this._reloadImage();
            this._displayTierPriceBlock(this.simpleProduct);
        },

        /**
         * For a given option element, reset all of its selectable options. Clear any selected
         * index, disable the option choice, and reset the option's state if necessary.
         * @private
         * @param element The element associated with a configurable option.
         */
        _resetChildren: function (element) {
            if (element.childSettings) {
                _.each(element.childSettings, function (set) {
                    set.selectedIndex = 0;
                    set.disabled = true;
                });

                if (element.config) {
                    this.options.state[element.config.id] = false;
                }
            }
        },

        /**
         * Populates an option's selectable choices.
         * @private
         * @param element Element associated with a configurable option.
         */
        _fillSelect: function (element) {
            var attributeId = element.id.replace(/[a-z]*/, '').match(/[^-]*/g)[0],
                options = this._getAttributeOptions(attributeId),
                prevConfig,
                index,
                i,
                allowedProducts,
                j;
            this._clearSelect(element);
            element.options[0] = new Option('', '');
            element.options[0].innerHTML = this.options.spConfig.chooseText;

            prevConfig = false;
            if (element.prevSetting) {
                prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
            }
            if (options) {
                index = 1;
                for (i = 0; i < options.length; i++) {
                    allowedProducts = [];
                    if (prevConfig) {
                        for (j = 0; j < options[i].products.length; j++) {
                            // prevConfig.config can be undefined
                            if (prevConfig.config &&
                                prevConfig.config.allowedProducts &&
                                prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                allowedProducts.push(options[i].products[j]);
                            }
                        }
                    } else {
                        allowedProducts = options[i].products.slice(0);
                    }
                    if (allowedProducts.length > 0) {
                        options[i].allowedProducts = allowedProducts;
                        element.options[index] = new Option(this._getOptionLabel(options[i]), options[i].id);
                        if (typeof options[i].price !== 'undefined') {
                            element.options[index].setAttribute('price', options[i].prices);
                        }
                        element.options[index].config = options[i];
                        index++;
                    }
                }

                if (this.isRequireItem()) {
                    this.setOptionValidation();
                }
            }
        },

        /**
         * Generate the label associated with a configurable option. This includes the option's
         * label or value and the option's price.
         * @private
         * @param option A single choice among a group of choices for a configurable option.
         * @param selOption Current selected option.
         * @return {String} The option label with option value and price (e.g. Black +1.99)
         */
        _getOptionLabel: function (option, selOption) {
            return option.label;
        },

        /**
         * Removes an option's selections.
         * @private
         * @param element The element associated with a configurable option.
         */
        _clearSelect: function (element) {
            for (var i = element.options.length - 1; i >= 0; i--) {
                element.remove(i);
            }
        },

        /**
         * Retrieve the attribute options associated with a specific attribute Id.
         * @private
         * @param attributeId The id of the attribute whose configurable options are sought.
         * @return {Object} Object containing the attribute options.
         */
        _getAttributeOptions: function (attributeId) {
            if (this.options.spConfig.attributes[attributeId]) {
                return this.options.spConfig.attributes[attributeId].options;
            }
        },

        /**
         * Is required configurable item of bundle product
         * @returns {*}
         */
        isRequireItem: function () {
            if (this.options.inputType === 'select' && $('#bundle-option-' + this.options.bundleOption).length) {
                return this.bundleField.is(':selected');
            }
            return this.bundleField.is(':checked');
        },

        /**
         * Set option validation after fill select
         */
        setOptionValidation: function () {
            if ($(this.options.productAddToForm).data('mageValidation') !== undefined) {
                $(this.element).find(this.options.superSelector).each(function () {
                    $(this).rules('add', { 'required': true });
                });
            }
        },

        /**
         * Reload the price of the configurable item incorporating the prices of all of the
         * configurable product's option selections.
         */
        _reloadPrice: function (element) {
            var prices,
                optionConfig = $(this.options.productAddToForm).priceBundle('option', 'optionConfig'),
                optionSelections,
                priceFormat,
                priceTemplate,
                bundleItemPrice,
                $priceHolder;

            if (optionConfig.isFixedPrice) {
                return;
            }
            prices = this._getPrices();
            optionSelections = optionConfig.options[this.options.bundleOption].selections;
            priceFormat = $(this.options.productAddToForm).priceBundle('option', 'priceFormat') || {};
            priceTemplate = mageTemplate(this.options.priceTemplate);
            bundleItemPrice = {};
            $priceHolder = $('[data-price-type]', this.bundleField.closest(this.options.bundleItem));

            if (!_.isEmpty(prices)) {
                bundleItemPrice.formatted = utils.formatPrice(prices.finalPrice.amount, priceFormat);
                optionSelections[this.options.selectionId].prices = prices;
                if (prices.tierPrices) {
                    optionSelections[this.options.selectionId].tierPrice = prices.tierPrices;
                }
                if (this.options.inputType === 'select') {
                    $priceHolder = $('[data-role="bundle-price-option-' + this.options.bundleOption + '-' + this.options.selectionId + '"] [data-price-type]');
                }
                if (this.options.inputType === 'select' && $('#bundle-option-' + this.options.bundleOption).length) {
                    this.bundleField.closest('select').trigger('change');
                } else {
                    this.bundleField.trigger('change');
                }
            } else {
                bundleItemPrice.formatted = utils.formatPrice($priceHolder.data('priceAmount'), priceFormat);
            }

            $priceHolder.html(priceTemplate({
                data: bundleItemPrice
            }));
        },

        /**
         * Get product various prices
         * @returns {{}}
         * @private
         */
        _getPrices: function () {
            var prices = {},
                elements = _.toArray(this.options.settings),
                hasProductPrice = false;

            _.each(elements, function (element) {
                var selected = element.options[element.selectedIndex],
                    config = selected && selected.config,
                    priceValue = {};

                if (config && config.allowedProducts.length === 1 && !hasProductPrice) {
                    priceValue = this._calculatePrice(config);
                    hasProductPrice = true;
                }

                if (!_.isEmpty(priceValue)) {
                    prices = priceValue;
                }
            }, this);

            return prices;
        },

        /**
         * Returns prices for configured products
         *
         * @param {*} config - Products configuration
         * @returns {*}
         * @private
         */
        _calculatePrice: function (config) {
            var displayPrices = $(this.options.priceHolderSelector).priceBox('option').prices,
                newPrices = this.options.spConfig.optionPrices[_.first(config.allowedProducts)];

            _.each(displayPrices, function (price, code) {
                if (newPrices[code]) {
                    displayPrices[code].amount = newPrices[code].amount - displayPrices[code].amount;
                }
            });

            if (newPrices.tierPrices && newPrices.tierPrices.length) {
                displayPrices.tierPrices = newPrices.tierPrices;
            }

            return displayPrices;
        },

        /**
         * Returns Simple product Id
         *  depending on current selected option.
         *
         * @private
         * @param {HTMLElement} element
         * @returns {String|undefined}
         */
        _getSimpleProductId: function (element) {
            var allOptions = element.config.options,
                value = element.value,
                config;

            if (element.prevSetting && !element.value) {
                value = allOptions[0].id;
            }

            config = _.filter(allOptions, function (option) {
                return option.id === value;
            });
            config = _.first(config);

            return _.isEmpty(config) ? undefined : _.first(config.allowedProducts);
        },
        /**
         * Returns locked products
         * @returns {Array}
         */
        getLockedProducts: function () {
            var lock = [];
            _.each(this.options.configurableOptions, function (value, key) {
                if (!value.selected) {
                    lock.push(value.value);
                }
            });
            return lock;
        },
        /**
         * Removes locked products from original configuration
         * @param config
         */
        removeLockedProducts: function (config) {
            var self = this;
            if (this.lockedProducts.length) {
                _.each(config.options, function (option) {
                    option.products = _.difference(option.products, self.lockedProducts);
                });
            }
        },

        _reloadImage: function (element) {
            var option,
                index = _.findKey(this.options.spConfig.index, this.options.state),
                images,
                $optionImage;

            if (this.bundleField.length) {
                if (this.options.inputType === 'select') {
                    option = this.bundleField.closest('select');
                    $optionImage = $('[data-image="' + option.attr('id') + '-' + option.val() + '"]');
                } else {
                    option = this.bundleField;
                    $optionImage = $('[data-image="' + option.attr('id') + '"]');
                }
            } else {
                option = $('input#bundle-option-' + this.options.bundleOption);
                $optionImage = $('[data-image="' + option.attr('id') + '-' + option.val() + '"]');
            }

            if (index && $(element).val()) {
                images = this.options.spConfig.images[index];
                if (images[0] && images[0]['thumb']) {
                    if (this.options.inputType === 'select' && typeof option.val() === 'object') {
                        $.each(option.val(), function (i, item) {
                            $('[data-image="' + option.attr('id') + '-' + item + '"]').attr('src', images[0]['thumb']);
                        });
                    } else {
                        $optionImage.attr('src', images[0]['thumb']);
                    }
                } else {
                    console.warn('Configurable Bundle: option image unavailable');
                }
            } else {
                $optionImage.attr('src', $optionImage.data('original'));
            }
        },

        /**
         * Show or hide tier price block
         *
         * @param {*} optionId
         * @private
         */
        _displayTierPriceBlock: function (optionId) {
            var options, tierPriceHtml, tierPrices;

            if (typeof optionId != 'undefined' &&
                this.options.spConfig.optionPrices[optionId].tierPrices.length
            ) {
                options = this.options.spConfig.optionPrices[optionId];

                if (this.options.tierPriceTemplate) {
                    tierPrices = this.getFixedSavePercent(options.tierPrices, options.finalPrice.amount);

                    tierPriceHtml = mageTemplate(this.options.tierPriceTemplate, {
                        'tierPrices': tierPrices,
                        '$t': $t,
                        'currencyFormat': this.options.spConfig.currencyFormat,
                        'priceUtils': utils
                    });
                    $(this.options.tierPriceBlockSelector).html(tierPriceHtml).show();
                }
            } else {
                $(this.options.tierPriceBlockSelector).hide();
            }
        },

        /**
         * Calculates savings percentage according to the given tier price amount
         * and related product price amount.
         */
        getFixedSavePercent: function (tierPrices, amount) {
            return tierPrices.map(function (item) {
                if (!item.percentage_value) {
                    item.percentage_value = 100 - (item.prices.finalPrice.amount * 100 / amount);
                    return item;
                }
                return item;
            });
        }
    });

    return $.mage.bundleConfigurable;
});
