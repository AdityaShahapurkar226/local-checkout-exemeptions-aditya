<?php

namespace Ahy\ApplePay\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ahy\ApplePay\Logger\ApplePayLogger;

class OrderPlaceAfter implements ObserverInterface
{
    private ApplePayLogger $applePayLogger;

    public function __construct(ApplePayLogger $applePayLogger)
    {
        $this->applePayLogger = $applePayLogger;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        try {
            $payment = $order->getPayment();
            $method = $payment ? $payment->getMethod() : null;

            if ($method === 'braintree_applepay') {
                $this->applePayLogger->debug('[Ahy_ApplePay] Apple Pay used for order placement (Observer)', [
                    'order_id'            => $order->getIncrementId(),
                    'entity_id'           => $order->getId(),
                    'customer_id'         => $order->getCustomerId(),
                    'customer_email'      => $order->getCustomerEmail(),
                    'grand_total'         => $order->getGrandTotal(),
                    'currency'            => $order->getOrderCurrencyCode(),
                    'billing_name'        => $order->getBillingAddress()->getName(),
                    'shipping_name'       => $order->getShippingAddress() ? $order->getShippingAddress()->getName() : null,
                    'store_id'            => $order->getStoreId(),
                    'store_code'          => $order->getStore()->getCode(),
                    'website_id'          => $order->getStore()->getWebsiteId(),
                    'payment_method'      => $method,
                    'payment_additional'  => $payment->getAdditionalInformation() ?? [],
                ]);
            }
        } catch (\Exception $e) {
            $this->applePayLogger->error(
                '[Ahy_ApplePay] Error in OrderPlaceAfter observer: ' . $e->getMessage()
            );
        }

        return $this;
    }
}
