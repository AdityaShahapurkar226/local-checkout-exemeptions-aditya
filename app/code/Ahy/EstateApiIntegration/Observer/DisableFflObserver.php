<?php
declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;

class DisableFflObserver implements ObserverInterface
{
    private Session $checkoutSession;
    private ProductRepositoryInterface $productRepository;
    private LoggerInterface $logger;

    public function __construct(
        Session $checkoutSession,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    public function execute(Observer $observer): void
    {
        $this->logger->info('=== DISABLE FFL OBSERVER ===');
        
        $quote = $this->checkoutSession->getQuote();

        foreach ($quote->getAllItems() as $item) {
            $json = $item->getData('orchid_restriction_level');
            
            $this->logger->info('Item: ' . $item->getSku() . ' | restriction: ' . ($json ?: 'NULL'));
            
            if (!$json) {
                continue;
            }

            $data = json_decode($json, true);
            
            if (isset($data['shipping_restriction']) && $data['shipping_restriction'] === 'AA') {
                $this->logger->info('>>> AA FOUND - Disabling FFL for product');
                
                try {
                    // Load product and disable FFL
                    $product = $this->productRepository->getById($item->getProductId());
                    
                    $this->logger->info('Product: ' . $product->getSku());
                    $this->logger->info('FFL before: ' . ($product->getData('ffl_selection_required') ?: 'NULL'));
                    
                    // Set FFL to false (0 = No)
                    $product->setData('ffl_selection_required', 0);
                    
                    // Save product
                    $this->productRepository->save($product);
                    
                    $this->logger->info('FFL after: ' . $product->getData('ffl_selection_required'));
                    $this->logger->info('>>> PRODUCT SAVED - FFL DISABLED');
                    
                } catch (\Exception $e) {
                    $this->logger->error('Error saving product: ' . $e->getMessage());
                }
            }
        }
        
        $this->logger->info('=== OBSERVER FINISHED ===');
    }
}