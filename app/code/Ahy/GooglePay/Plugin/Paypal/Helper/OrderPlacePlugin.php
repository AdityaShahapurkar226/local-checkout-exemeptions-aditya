<?php

namespace Ahy\GooglePay\Plugin\Paypal\Helper;

use Magento\Quote\Model\Quote;
use Ahy\GooglePay\Logger\GooglePayLogger;

class OrderPlacePlugin
{
    /**
     * @var GooglePayLogger
     */
    protected $logger;

    public function __construct(
        GooglePayLogger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * After plugin for execute()
     *
     * @param \PayPal\Braintree\Model\Paypal\Helper\OrderPlace $subject
     * @param void $result
     * @param Quote $quote
     * @param array $agreement
     * @return void
     */
    public function afterExecute(
        \PayPal\Braintree\Model\Paypal\Helper\OrderPlace $subject,
        $result,
        Quote $quote,
        array $agreement
    ) {
        try {
            $payment = $quote->getPayment();
            $method = $payment ? $payment->getMethod() : null;

            if ($method === 'braintree_googlepay') {
                $billingAddress = $quote->getBillingAddress();
                $shippingAddress = $quote->getShippingAddress();
                $additionalInfo = $payment ? $payment->getAdditionalInformation() : [];

                $this->logger->info('[Ahy_GooglePay] Google Pay used for order placement (OrderPlacePlugin)', [
                    'quote_id' => $quote->getId(),
                    'reserved_order_id' => $quote->getReservedOrderId(),
                    'customer_id' => $quote->getCustomerId(),
                    'customer_email' => $quote->getCustomerEmail(),
                    'grand_total' => $quote->getGrandTotal(),
                    'currency' => $quote->getQuoteCurrencyCode(),
                    'billing_name' => $billingAddress ? $billingAddress->getName() : null,
                    'shipping_name' => $shippingAddress ? $shippingAddress->getName() : null,
                    'store_id' => $quote->getStoreId(),
                    'store_code' => $quote->getStore()->getCode(),
                    'website_id' => $quote->getStore()->getWebsiteId(),
                    'payment_method' => $method,
                    'payment_additional_info' => $additionalInfo,
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Error in Ahy_GooglePay OrderPlacePlugin afterExecute plugin: ' . $e->getMessage());
        }

        return $result;
    }
}
