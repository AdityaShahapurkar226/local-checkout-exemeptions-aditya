<?php
namespace Ahy\EfflApiIntegration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class CopyFflToOrder implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        // Get FFL dealer data from quote
        $fflDealerData = $quote->getFflDealer(); // string summary
        $selectedDealerId = $quote->getData('selected_ffl_dealer_id') ?? null;

        if ($fflDealerData) {
            $order->setFflDealer($fflDealerData);
        }
        if ($selectedDealerId) {
            $order->setData('selected_ffl_dealer_id', $selectedDealerId);
        }

        return $this;
    }
}
