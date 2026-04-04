<?php

namespace Ahy\EfflApiIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ahy\EfflApiIntegration\Logger\Logger as EfflLogger;

class SaveFflDealerToInvoice implements ObserverInterface
{
    private EfflLogger $logger;

    public function __construct(EfflLogger $logger)
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $invoice = $observer->getEvent()->getInvoice();

            if ($invoice && $invoice->getOrder()) {
                $order = $invoice->getOrder();

                $invoice->setData('ffl_dealer', $order->getData('ffl_dealer'));

                // fallback: use order entity_id if ffl_id is empty
                $fflId = $order->getData('selected_ffl_dealer_id') ?: $order->getId();
                $invoice->setData('selected_ffl_dealer_id', $fflId);

                $this->logger->info(sprintf(
                    'Copied FFL fields to invoice (invoice_id=%d, order_id=%d, ffl_id=%s)',
                    $invoice->getId(),
                    $order->getId(),
                    $fflId
                ));
            }
        } catch (\Throwable $e) {
            $this->logger->error('Error saving FFL dealer to invoice: ' . $e->getMessage());
        }
    }
}
