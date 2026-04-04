<?php

namespace Ahy\BarcodeLookup\Observer\Product;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Ahy\BarcodeLookup\Logger\Logger as BarcodeLookupApiLogger;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;

class LogBarcodeUpdateObserver implements ObserverInterface
{
    private $_barcodeLookupApiLogger;

    public function __construct(
        BarcodeLookupApiLogger $barcodeLookupApiLogger
    ) {
        $this->_barcodeLookupApiLogger = $barcodeLookupApiLogger;
    }

    public function execute(Observer $observer): void
    {
        try {
            /** @var Product $product */
            $product = $observer->getEvent()->getProduct();

            // Skip if not a product
            if (!$product instanceof Product) {
                return;
            }

            // Old vs new check
            $original = $product->getOrigData('barcode_last_update');
            $current  = $product->getData('barcode_last_update');

            if ($original !== $current) {
                /* $logData = [
                    'UPC' => $product->getData('upc') ?? 'N/A',
                    'Name' => $product->getName(),
                    'barcode_last_update' => $current,
                ];

                $this->_barcodeLookupApiLogger->info('[BarcodeLookup] barcode_last_update changed', $logData); */
            }
        } catch (\Throwable $e) {
            $this->_barcodeLookupApiLogger->error('[BarcodeLookup] Error in barcode_last_update observer: ' . $e->getMessage());
        }
    }
}
