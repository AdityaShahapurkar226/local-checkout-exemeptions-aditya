/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_OneStepCheckout
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*global define*/
define(
    [
        'jquery',
        "underscore",
        'Webkul_OneStepCheckout/js/view/shipping',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-address/form-popup-state',
        'Magento_Checkout/js/model/shipping-rate-service',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/action/set-shipping-information',
        'Webkul_OneStepCheckout/js/action/get-totals',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Webkul_MpMultiShipping/js/model/multishipping',
        'Magento_Checkout/js/checkout-data',
        'uiRegistry',
        'mage/translate',
        'Magento_Catalog/js/price-utils'
    ],
    function (
        $,
        _,
        Component,
        ko,
        customer,
        addressList,
        addressConverter,
        quote,
        createShippingAddress,
        selectShippingAddress,
        shippingRatesValidator,
        formPopUpState,
        shippingRateService,
        shippingService,
        selectShippingMethodAction,
        rateRegistry,
        setShippingInformationAction,
        getTotals,
        stepNavigator,
        modal,
        checkoutDataResolver,
        multishippingModel,
        checkoutData,
        registry,
        $t,
        // gstUpdater,
        priceUtils
    ) {
        'use strict';
        var popUp = null;
        return Component.extend({
            defaults: {
                template: 'Webkul_MpMultiShipping/onestep-shipping'
            },
            selectedSellerMethod: ko.observableArray([]),
            totalSellerAmount: ko.observable(0),
            totalBaseSellerAmount: ko.observable(0),
            totalSeller: ko.observable(0),
            totalSelectedSeller: ko.observable(0),
            selectedMethods: ko.observableArray([]),

            initObservable: function () {
                this._super();
                quote.shippingMethod.subscribe(function (newSelected) {
                    if (newSelected.method_code === 'mpmultishipping') {
                        // setShippingInformationAction()
                    }
                }, this);

                return this;
            },

            totalSellerUpdate: function (model, data) {
                if (data.carrier_code != 'mpmultishipping') {
                    model.selectedSellerMethod([]);
                    model.totalSelectedSeller(0);
                    model.totalSellerAmount(0);
                    model.totalBaseSellerAmount(0);
                    $.each($(".seller-method-table input[type='radio']"), function () {
                        $(this).prop("checked", false);
                    });
                }
                if (window.location.hash !="#payment") {
                    selectShippingMethodAction(data);
                    checkoutData.setSelectedShippingRate(data.carrier_code);
                }
                return true;
            },
            /**
             * @param {Object} shippingMethod
             * @return {Boolean}
             */
            selectSellerShippingMethod: function (model, multishipping, sellerdata, price, base_amount, selectedMethod, methodName) {
                var productWiseEnabled = model.productWiseEnabled();
                var oldsellerid = 0;
                $(".selected-methods").remove();
                if (model.selectedSellerMethod().length == 0) {
                    model.selectedSellerMethod.push({sellerid: sellerdata.seller_id, itemid: sellerdata.item_ids, price: price, baseamount:base_amount , code: selectedMethod, method: methodName});
                    model.totalSelectedSeller(1);
                } else {
                    var isAdmin = false;
                    $.each(model.selectedSellerMethod(), function (index, value) {
                        if (productWiseEnabled) {
                            if (sellerdata.item_ids == value.itemid ) {
                                oldsellerid = sellerdata.seller_id;
                            }
                        } else {
                            if (sellerdata.seller_id == value.sellerid) {
                                oldsellerid = sellerdata.seller_id;
                            }
                        }
                        if (sellerdata.seller_id == 0 && value.sellerid == 0) {
                            isAdmin = true;
                        }
                    });

                    if (oldsellerid) {
                        $.each(model.selectedSellerMethod(), function (index, value) {
                            if (productWiseEnabled) {
                                if (_.isEqual(sellerdata.item_ids, value.itemid)) {
                                    model.selectedSellerMethod()[index].price = price;
                                    model.selectedSellerMethod()[index].baseamount = base_amount;
                                }
                            } else {
                                if (_.isEqual(value.sellerid, oldsellerid)) {
                                    model.selectedSellerMethod()[index].price = price;
                                    model.selectedSellerMethod()[index].baseamount = base_amount;
                                    model.selectedSellerMethod()[index].method = methodName;
                                }
                            }
                        });
                    } else {
                        model.selectedSellerMethod.push({sellerid: sellerdata.seller_id, itemid: sellerdata.item_ids, price: price, baseamount:base_amount , code: selectedMethod, method: methodName});
                        model.totalSelectedSeller(model.totalSelectedSeller()+1);
                    }
                }
                if (model.selectedSellerMethod().length > 0) {
                    model.totalSellerAmount(0);
                    model.totalBaseSellerAmount(0);
                    $.each(model.selectedSellerMethod(), function (index, value) {
                        model.totalSellerAmount(model.totalSellerAmount() + parseFloat(value.price))
                        model.totalBaseSellerAmount(model.totalBaseSellerAmount() + parseFloat(value.baseamount));
                    });
                }
                multishippingModel.selectedMethods(model.selectedSellerMethod());
                multishippingModel.shippingAmount(model.totalBaseSellerAmount());
                var shippingMethod = {};
                shippingMethod.carrier_code = 'mpmultishipping';
                shippingMethod.method_code = 'mpmultishipping';
                setShippingInformationAction()
                // selectShippingMethodAction(shippingMethod);
                // checkoutData.setSelectedShippingRate(shippingMethod['carrier_code'] + '_' + shippingMethod['method_code']);

               return true;
            },

            getFormattedPrice: function (price) {
                $('.one-step-multi').attr('disabled', false);
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            },

            /**
             * checks if product wise shipping is enabled from admin config
             */
            productWiseEnabled: function () {

                if (window.productWiseShippingEnabled) {
                    return true;
                }
                return false;
            },

            /**
             * @return {Boolean}
             */
            validateShippingInformation: function () {
                var shippingAddress,
                    addressData,
                    loginFormSelector = 'form[data-role=email-with-possible-login]',
                    emailValidationResult = customer.isLoggedIn(),
                    field,
                    country = registry.get(this.parentName + '.shippingAddress.shipping-address-fieldset.country_id'),
                    countryIndexedOptions = country.indexedOptions,
                    option = countryIndexedOptions[quote.shippingAddress().countryId],
                    messageContainer = registry.get('checkout.errors').messageContainer;

                if (quote.shippingMethod().carrier_code == 'mpmultishipping') {
                    if (quote.shippingMethod().sellerShipping.length == 0) {
                        this.errorValidationMessage('Please specify a shipping method.');
                        return false;
                    }
                    if (quote.shippingMethod().sellerShipping && quote.shippingMethod().sellerShipping.length != this.totalSelectedSeller()) {
                        this.errorValidationMessage('Please specify a shipping method for each seller.');
                        return false;
                    }
                }
                if (!quote.shippingMethod()) {
                    this.errorValidationMessage('Please specify a shipping method.');

                    return false;
                }

                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }

                if (this.isFormInline) {
                    this.source.set('params.invalid', false);
                    this.source.trigger('shippingAddress.data.validate');

                    if (this.source.get('shippingAddress.custom_attributes')) {
                        this.source.trigger('shippingAddress.custom_attributes.data.validate');
                    }

                    if (this.source.get('params.invalid') ||
                        !quote.shippingMethod().method_code ||
                        !quote.shippingMethod().carrier_code ||
                        !emailValidationResult
                    ) {
                        return false;
                    }

                    shippingAddress = quote.shippingAddress();
                    addressData = addressConverter.formAddressDataToQuoteAddress(
                        this.source.get('shippingAddress')
                    );

                    //Copy form data to quote shipping address object
                    for (var field in addressData) {
                        if (addressData.hasOwnProperty(field) &&
                            shippingAddress.hasOwnProperty(field) &&
                            typeof addressData[field] != 'function' &&
                            _.isEqual(shippingAddress[field], addressData[field])
                        ) {
                            shippingAddress[field] = addressData[field];
                        } else if (typeof addressData[field] != 'function' &&
                            !_.isEqual(shippingAddress[field], addressData[field])) {
                            shippingAddress = addressData;
                            break;
                        }
                    }

                    if (customer.isLoggedIn()) {
                        shippingAddress.save_in_address_book = 1;
                    }
                    selectShippingAddress(shippingAddress);
                }

                if (!emailValidationResult) {
                    $(loginFormSelector + ' input[name=username]').focus();

                    return false;
                }

                return true;
            }
        });
    }
);
