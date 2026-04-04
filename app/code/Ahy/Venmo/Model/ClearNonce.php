<?php
declare(strict_types=1);

namespace Ahy\Venmo\Model;

use Ahy\Venmo\Api\ClearNonceInterface;
use Magento\Checkout\Model\Session;
use Ahy\Venmo\Logger\VenmoLogger;

class ClearNonce implements ClearNonceInterface
{
    protected Session $sessionCheckout;
    protected VenmoLogger $logger;

    public function __construct(
        Session $sessionCheckout,
        VenmoLogger $logger
    ) {
        $this->sessionCheckout = $sessionCheckout;
        $this->logger = $logger;
    }

    public function clearNonce(): bool
    {
        $quote = $this->sessionCheckout->getQuote();
        $payment = $quote->getPayment();

        if ($payment->getAdditionalInformation('payment_method_nonce')) {
            $this->logger->info('Clearing Venmo nonce for quote ID: ' . $quote->getId());

            $payment->setAdditionalInformation('payment_method_nonce', null);
            $quote->save();

            return true; // Nonce cleared successfully
        }

        $this->logger->warning('No Venmo nonce found for quote ID: ' . $quote->getId());
        return false; // No nonce to clear
    }
}
