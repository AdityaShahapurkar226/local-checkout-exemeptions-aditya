<?php
namespace Ahy\FlxPoint\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ahy\FlxPoint\Logger\Logger as FlxPointApiLogger;
use Ahy\ThemeCustomization\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;

class UpdateSkuOnOrder implements ObserverInterface
{
    /**
     * @var FlxPointApiLogger
     */
    private $logger;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param FlxPointApiLogger $logger
     * @param Data $helper
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        FlxPointApiLogger $logger,
        Data $helper,
        ProductRepositoryInterface $productRepository
    )
    {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->productRepository = $productRepository;
    }
    
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            foreach ($order->getAllVisibleItems() as $item) {
                $product = $item->getProduct();
                // Get the SKU of the product
                $sku = $item->getSku();
                // Retrieve the product ID based on the SKU
                $productId = $this->getProductIdBySku($sku);
                // Check if the custom attribute 'Flxpoint_Sku' is set for the product
                if ($productId) {
                    // Check if the custom attribute 'Flxpoint_Sku' is set for the product
                    $flxpointSku = $this->helper->getProductAttribute($productId, 'flxpoint_sku');
                    if ($flxpointSku != '' && $flxpointSku != null) {
                        // Get the value of custom attribute 'Flxpoint_Sku'
                        $customSku = $flxpointSku;
                        // Replace the SKU with the custom attribute value
                        $item->setSku($customSku);
                    } else {
                        $this->logger->info("The custom attribute 'Flxpoint_Sku' was not found in the product: " . $product->getName());
                        // Log a message to inform that there is no custom attribute 'Flxpoint_Sku'.
                        $message = sprintf("The order sku %s does not have the 'Flxpoint_Sku' attribute.", $sku) . "\n";
                        $message .= sprintf("The order id %s does not have the 'Flxpoint_Sku' attribute.", $productId);
                        $this->logger->info($message);
                    }
                } else {
                    $this->logger->info("Product with SKU $sku does not exist.");
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('An error occurred: ' . $e->getMessage());
        }
        return $this;
    }

    /**
     * Get product ID by SKU
     *
     * @param string $sku
     * @return int|null
     */
    private function getProductIdBySku(string $sku): ?int
    {
        try {
            $product = $this->productRepository->get($sku);
            return $product->getId();
        } catch (\Exception $e) {
            return null;
        }
    }
}
