<?php
namespace Webkul\Marketplace\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class AddFflInfoToOrderEmail implements ObserverInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $transport = $observer->getData('transportObject') ?: $observer->getEvent()->getTransport();
        $order     = $transport->getOrder();

        $fflDealer   = $order->getData('ffl_dealer');
        $fflDealerId = $order->getData('selected_ffl_dealer_id');

        $hasFfl    = false;
        $hasNormal = false;

        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $sku     = $product ? $product->getSku() : '';

            if ($sku === 'FREE Everest Decal') {
                $this->logger->debug('Skipping product SKU=FREE Everest Decal for FFL calculation');
                continue;
            }

            if ($product && $product->getResource()->getAttribute('ffl_selection_required')) {
                $fflRequired = $product->getData('ffl_selection_required');

                $this->logger->debug(
                    'Checking product SKU=' . $sku .
                    ', ffl_selection_required=' . var_export($fflRequired, true)
                );

                if (!empty($fflRequired)) {
                    $hasFfl = true;
                } else {
                    $hasNormal = true;
                }
            } else {
                $hasNormal = true;
                $this->logger->debug(
                    'Product SKU=' . $sku .
                    ' does not have ffl_selection_required attribute.'
                );
            }
        }

        $transport->setData('ffl_dealer', $fflDealer);
        $transport->setData('ffl_dealer_id', $fflDealerId);
        $transport->setData('has_normal', $hasNormal ?? false);
        $transport->setData('has_ffl', $hasFfl ?? false);

    }
}
