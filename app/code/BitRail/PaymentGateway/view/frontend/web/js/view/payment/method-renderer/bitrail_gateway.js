/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, Component, messageList, redirectOnSuccessAction, quote) {
        'use strict';

        var totals = quote.totals();
        var orderVerificationToken = '';

        return Component.extend({
            defaults: {
                template: 'BitRail_PaymentGateway/payment/form',
                transactionResult: ''
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'transactionResult'
                    ]);
                return this;
            },

            getCode: function() {
                return 'bitrail_gateway';
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'orderVerificationToken': orderVerificationToken
                    }
                };
            },

            orderPopup: function() {
                var component = this;

                function prepareOrderInformation(orderId) {
                    var orderInfo = {
                        'OrderID': orderId,
                        'Customer last name': window.checkoutConfig.quoteData.customer_lastname,
                        'Customer first name': window.checkoutConfig.quoteData.customer_firstname,
                        'Customer email': window.checkoutConfig.quoteData.customer_email
                    };
                    var shippingAddress = quote.shippingAddress();
                    if (shippingAddress) {
                        orderInfo['Shipping address'] = shippingAddress.firstname + ' ' +
                            shippingAddress.lastname + ', ' +
                            shippingAddress.street.join(' ') + ', ' +
                            shippingAddress.city + ', ' +
                            shippingAddress.region + ', ' +
                            shippingAddress.postcode + ', ' +
                            shippingAddress.countryId;
                        orderInfo['Shipping phone'] = shippingAddress.telephone;
                        orderInfo['Shipping total'] = totals.shipping_incl_tax.toFixed(2);
                    }
                    var billingAddress = quote.billingAddress();
                    if (billingAddress) {
                        orderInfo['Billing address'] =  billingAddress.firstname + ' ' +
                            billingAddress.lastname + ', ' +
                            billingAddress.street.join(' ') + ', ' +
                            billingAddress.city + ', ' +
                            billingAddress.region + ', ' +
                            billingAddress.postcode + ', ' +
                            billingAddress.countryId;
                        orderInfo['Billing phone'] = billingAddress.telephone;
                    }
                    $.each(window.checkoutConfig.totalsData.items, function (i, item) {
                        orderInfo['Item '+(i + 1)+' id'] = item.item_id + ' - ' + item.name;
                        orderInfo['Item '+(i + 1)+' price'] = Number(item.price).toFixed(2);
                        orderInfo['Item '+(i + 1)+' quantity'] = item.qty;
                        orderInfo['Item '+(i + 1)+' total'] = Number(item.row_total).toFixed(2);
                    });

                    return orderInfo;
                }

                function orderCallback(response) {
                    closePopup();

                    switch (response.status) {
                        case 'success':
                            orderVerificationToken = response.verification_token;

                            component.getPlaceOrderDeferredObject()
                                .fail(
                                    function () {
                                        component.isPlaceOrderActionAllowed(true);
                                    }
                                ).done(
                                    function () {
                                        redirectOnSuccessAction.execute();
                                        component.afterPlaceOrder();
                                    }
                                );

                            break;
                        case 'failed':
                            messageList.addErrorMessage({message: 'Payment failed. Please try again later or choose another payment method.'});

                            break;
                        case 'cancelled':
                        default:
                            console.log('Payment for order '+response.data.orderNumber+' cancelled');

                            break;
                    }
                }

                function closePopup() {
                    $('#ordersmodal').modal('closeModal');
                    $('#ordersmodal').remove();
                }

                $('body').trigger('processStart');

                $.get(
                    location.origin+'/bitrail_gateway_api/order/getinfo',
                    {
                        nonceCode: window.checkoutConfig.payment.bitrail_gateway.nonceCode
                    }
                )
                    .done(function (response) {
                        if (response.success && response.data.authToken) {
                            $('<iframe id="ordersmodal"></iframe>').modal({
                                type: 'popup',
                                title: response.data.paymentMethodTitle,
                                modalClass: 'order-payment-modal',
                                buttons: [{
                                    text: 'Cancel',
                                    class: '',
                                    click: closePopup
                                }]
                            }).modal('openModal');

                            window.BitRail.init(
                                response.data.authToken,
                                {
                                    api_url: window.checkoutConfig.payment.bitrail_gateway.apiUrl,
                                    parent_element: document.getElementById('ordersmodal'),
                                    frame_attributes: {style: null}
                                }
                            );
                            window.BitRail.order(
                                response.data.orderToken,
                                response.data.destinationVaultHandle,
                                totals.grand_total.toFixed(2),
                                'USD',
                                response.data.description,
                                prepareOrderInformation(response.data.orderNumber),
                                orderCallback
                            );
                        } else {
                            messageList.addErrorMessage({message: response.data.message});
                        }
                    })
                    .fail(function () {
                        messageList.addErrorMessage({message: 'Sorry, something went wrong. Please try again later or contact your store support.'});
                    })
                    .always(function () {
                        $('body').trigger('processStop');
                    });
            }
        });
    }
);