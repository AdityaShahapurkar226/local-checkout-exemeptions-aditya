<?php

namespace Ahy\EstateApiIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CopyAgeFieldsToOrder implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $quote = $observer->getQuote();
        $order = $observer->getOrder();

        // tinyint(1)
        $order->setAgeVerified((int)$quote->getAgeVerified());

        // int(11) nullable
        if ($quote->getAgeOfPurchaser()) {
            $order->setAgeOfPurchaser((int)$quote->getAgeOfPurchaser());
        }
    }
}
