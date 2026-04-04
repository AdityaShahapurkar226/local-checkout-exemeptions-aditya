<?php
namespace Ahy\PaymentIntegrations\Plugin\Quote;

use Ahy\GooglePay\Logger\GooglePayLogger;
use Ahy\Venmo\Logger\VenmoLogger;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\QuoteRepository;

class QuoteManagementPlugin
{
    /**
     * @var GooglePayLogger
     */
    private $googlepayLogger;

    /**
     * @var VenmoLogger
     */
    private $venmoLogger;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    public function __construct(
        GooglePayLogger $googlepayLogger,
        VenmoLogger $venmoLogger,
        QuoteRepository $quoteRepository
    ) {
        $this->googlepayLogger = $googlepayLogger;
        $this->venmoLogger = $venmoLogger;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * After plugin for placeOrder()
     *
     * @param QuoteManagement $subject
     * @param int $result Order ID returned by placeOrder()
     * @param int $cartId
     * @param PaymentInterface|null $paymentMethod
     * @return int
     */
    public function afterPlaceOrder(
        QuoteManagement $subject,
        $result,
        $cartId,
        PaymentInterface $paymentMethod = null
    ) {
        try {
            $quote = $this->quoteRepository->get($cartId);
            $payment = $quote->getPayment();
            $method = $payment ? $payment->getMethod() : null;

            if ($method === 'braintree_googlepay') {
                $billingAddress = $quote->getBillingAddress();
                $shippingAddress = $quote->getShippingAddress();
                $additionalInfo = $payment ? $payment->getAdditionalInformation() : [];

                $this->googlepayLogger->info('[Ahy_GooglePay] Google Pay used for order placement', [
                    'quote_id' => $quote->getId(),
                    'reserved_order_id' => $quote->getReservedOrderId(),
                    'order_id' => $result,
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
            } else if ($method === 'braintree_venmo') {
                $billingAddress = $quote->getBillingAddress();
                $shippingAddress = $quote->getShippingAddress();
                $additionalInfo = $payment ? $payment->getAdditionalInformation() : [];

                $this->venmoLogger->info('[Ahy_Venmo] Venmo used for order placement', [
                    'quote_id' => $quote->getId(),
                    'reserved_order_id' => $quote->getReservedOrderId(),
                    'order_id' => $result,
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
            $message = 'Error in Ahy_PaymentIntegrations QuoteManagementPlugin afterPlaceOrder plugin: ' . $e->getMessage();

            try {
                $this->googlepayLogger->error($message);
            } catch (\Exception $logException) {
                // fail silently to avoid masking the original issue
            }

            try {
                $this->venmoLogger->error($message);
            } catch (\Exception $logException) {
                // fail silently
            }
        }

        return $result;
    }
}
