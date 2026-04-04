<?php
declare(strict_types=1);

namespace Ahy\ApplePay\Plugin\Checkout\Model;

use Magento\Checkout\Model\GuestPaymentInformationManagement;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Psr\Log\LoggerInterface;

class GuestPaymentInformationManagementPlugin
{
    private QuoteIdMaskFactory $quoteIdMaskFactory;
    private CartRepositoryInterface $cartRepository;
    private LoggerInterface $logger;

    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CartRepositoryInterface $cartRepository,
        LoggerInterface $logger
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
        $this->logger = $logger;
    }

    /**
     * Before plugin for savePaymentInformation
     *
     * @param GuestPaymentInformationManagement $subject
     * @param int|string $cartId
     * @param string $email
     * @param PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return array
     */
    public function beforeSavePaymentInformation(
        GuestPaymentInformationManagement $subject,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        $billingAddress = null
    ) {
        // Load the quote
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $quote = $this->cartRepository->getActive($quoteIdMask->getQuoteId());

        $this->logger->debug('Testing Pravin Logger 9.00: shippingAddress ');

        $shippingAddress = $quote->getShippingAddress();

        if ($shippingAddress) {
            $this->logger->debug('Testing Pravin Logger 9.01: shippingAddress ' . print_r($shippingAddress->getFirstname(), true));
        }

        // Hack for Braintree ApplePay shipping method
        if ($paymentMethod->getMethod() === 'braintree_applepay') {
            $additionalData = $paymentMethod->getAdditionalData();
            if (is_array($additionalData) && isset($additionalData['applepay_shipping_method'])) {
                $applePayShippingMethod = $additionalData['applepay_shipping_method'];

                // Handle accidental array
                if (is_array($applePayShippingMethod)) {
                    $applePayShippingMethod = reset($applePayShippingMethod);
                }

                if ($applePayShippingMethod && $shippingAddress) {
                    $this->logger->debug(
                        'Pravin Guest shipping address override: ' . $applePayShippingMethod
                    );

                    $shippingAddress->setShippingMethod($applePayShippingMethod);
                }
            }
        }

        // No need to change parameters, just return them as is
        return [$cartId, $email, $paymentMethod, $billingAddress];
    }
}
