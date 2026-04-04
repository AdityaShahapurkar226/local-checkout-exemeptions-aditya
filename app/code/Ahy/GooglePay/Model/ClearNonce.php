<?php
declare(strict_types=1);

namespace Ahy\GooglePay\Model;

use Ahy\GooglePay\Api\ClearNonceInterface;
use Magento\Checkout\Model\Session;
use Ahy\GooglePay\Logger\GooglePayLogger;

class ClearNonce implements ClearNonceInterface
{
    /**
     * @var Session
     */
    protected $sessionCheckout;

    /**
     * @var GooglePayLogger
     */
    protected $logger;

    public function __construct(
        Session $sessionCheckout,
        GooglePayLogger $logger
    ) {
        $this->sessionCheckout = $sessionCheckout;
        $this->logger = $logger;
    }

    /**
     * Clear the Google Pay nonce from the session
     *
     * @return bool
     */
    public function clearNonce(): bool
    {
        $payment = $this->sessionCheckout->getQuote()->getPayment();

        if ($payment->getAdditionalInformation('payment_method_nonce')) {
            $this->logger->info('Clearing Google Pay nonce for quote ID: ' . $this->sessionCheckout->getQuoteId());

            $payment->setAdditionalInformation('payment_method_nonce', null);
            $this->sessionCheckout->getQuote()->save();

            return true; // Nonce cleared successfully
        }

        $this->logger->warning('No Google Pay nonce found for quote ID: ' . $this->sessionCheckout->getQuoteId());
        return false; // No nonce to clear
    }
}
