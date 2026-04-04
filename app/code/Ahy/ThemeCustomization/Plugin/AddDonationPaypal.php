<?php
namespace Ahy\ThemeCustomization\Plugin;

class AddDonationPaypal
{
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quote;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var Quote
     */
    protected $helperDonation;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    const AMOUNT_SUBTOTAL = 'subtotal';

    /**
     * constructor.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param Quote $helperDonation
     */
    public function __construct(
        \Magento\Quote\Model\Quote $quote,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->quote            = $quote;
        $this->logger           = $logger;
        $this->_checkoutSession = $checkoutSession;
        $this->_registry        = $registry;
        $this->cart             = $cart;
        $this->priceCurrency    = $priceCurrency;
    }


    /**
     * Get shipping, tax, subtotal and discount amounts all together
     *
     * @param \Magento\Paypal\Model\Cart $cart
     * @param array $result
     *
     * @return array
     */
    public function afterGetAmounts($cart, $result)
    {
        $quote            = $this->_checkoutSession->getQuote();
        $paymentMethod    = $quote->getPayment()->getMethod();
        $paypalMethodList = $this->getPaypalMethodList();

        if (in_array($paymentMethod, $paypalMethodList)) {
            $donationPrice = $this->_getDonationAmount($quote);

            if ($donationPrice > 0) {
                $result[self::AMOUNT_SUBTOTAL] = $result[self::AMOUNT_SUBTOTAL] + $donationPrice;
            }
        }

        return $result;
    }

    /**
     * Get shipping, tax, subtotal and discount amounts all together
     *
     * @param \Magento\Checkout\Model\Cart $cart
     *
     * @return array
     */
    public function beforeGetAllItems($cart)
    {
        $paypalTest = $this->_registry->registry('is_paypal_items') ? $this->_registry->registry('is_paypal_items') : 0;
        $quote = $this->_checkoutSession->getQuote();
        $paymentMethod = $quote->getPayment()->getMethod();

        // Check if the payment method is empty, null, or not set
        if (empty($paymentMethod) || $paymentMethod === 'authnetahypayment') {
            $paymentMethod = 'paypal_express'; // Set it to 'paypal_express'
            $quote->getPayment()->setMethod($paymentMethod); // Save it in the quote
            $quote->save();
        }

        $paypalMethodList = $this->getPaypalMethodList();

        if ($paypalTest < 1 && in_array($paymentMethod, $paypalMethodList)) {
            if (method_exists($cart, 'addCustomItem')) {
                $donationPrice = $this->_getDonationAmount($quote);

                if ($donationPrice > 0) {
                    $cart->addCustomItem(__("Donation"), 1, $donationPrice);
                    $reg = $this->_registry->registry('is_paypal_items');
                    $current = $reg + 1;
                    $this->_registry->unregister('is_paypal_items');
                    $this->_registry->register('is_paypal_items', $current);
                }
            }
        }
    }


    

    /**
     * @param $quote
     *
     * @return float
     */
    protected function _getDonationAmount($quote)
    {
        $store             = $quote->getStore();
        $donationBasePrice = $quote->getData('donation_amount');

        return $this->priceCurrency->convert($donationBasePrice, $store);
    }

    /**
     * Get paypal method list
     *
     * @return array
     */
    protected function getPaypalMethodList()
    {
        return [
            'payflowpro',
            'payflow_link',
            'payflow_advanced',
            'braintree_paypal',
            'paypal_express_bml',
            'payflow_express_bml',
            'payflow_express',
            'paypal_express'
        ];
    }
}
