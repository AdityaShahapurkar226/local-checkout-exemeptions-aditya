<?php

namespace Ahy\EfflApiIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class SaveFflDealerToOrder implements ObserverInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Transfer FFL fields from quote to order
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $observer->getEvent()->getQuote();

            /** @var \Magento\Sales\Model\Order $order */
            $order = $observer->getEvent()->getOrder();

            if ($quote && $order) {
                $order->setData('ffl_dealer', $quote->getData('ffl_dealer'));
                $order->setData('selected_ffl_dealer_id', $quote->getData('selected_ffl_dealer_id'));
            }
        } catch (\Exception $e) {
            $this->logger->error('Error saving FFL dealer to order: ' . $e->getMessage());
        }
    }
}
