<?php

namespace Ahy\EfflApiIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ahy\EfflApiIntegration\Logger\Logger as EfflLogger;

class SaveFflDealerToCreditMemo implements ObserverInterface
{
    private EfflLogger $logger;

    public function __construct(EfflLogger $logger)
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $creditmemo = $observer->getEvent()->getCreditmemo();

            if ($creditmemo && $creditmemo->getOrder()) {
                $order = $creditmemo->getOrder();

                $creditmemo->setData('ffl_dealer', $order->getData('ffl_dealer'));

                // fallback: use order entity_id if ffl_id is empty
                $fflId = $order->getData('selected_ffl_dealer_id') ?: $order->getId();
                $creditmemo->setData('selected_ffl_dealer_id', $fflId);

                $this->logger->info(sprintf(
                    'Copied FFL fields to credit memo (creditmemo_id=%d, order_id=%d, ffl_id=%s)',
                    $creditmemo->getId(),
                    $order->getId(),
                    $fflId
                ));
            }
        } catch (\Throwable $e) {
            $this->logger->error('Error saving FFL dealer to credit memo: ' . $e->getMessage());
        }
    }
}
