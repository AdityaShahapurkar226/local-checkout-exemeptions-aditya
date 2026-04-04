<?php
declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class ResetRestrictionOnRemove implements ObserverInterface
{
    private CheckoutSession $checkoutSession;
    private CartRepositoryInterface $cartRepository;
    private LoggerInterface $logger;

    public function __construct(
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $cartRepository,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository   = $cartRepository;
        $this->logger           = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $quote = $this->checkoutSession->getQuote();

            if (!$quote || !$quote->getId()) {
                return;
            }
            $quote->setData('orchid_restriction_level', null);

            $this->cartRepository->save($quote);
        } catch (\Throwable $e) {
            $this->logger->error('[Orchid] ResetRestrictionOnRemove Error: ' . $e->getMessage());
        }
    }
}
