<?php
namespace Ahy\EfflApiIntegration\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Sales\Model\Order;

class OrderFfl implements ArgumentInterface
{
    private $productRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * Get FFL label for a given order item
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    public function getFflLabel(\Magento\Sales\Model\Order\Item $item): string
    {
        try {
            $productId = $item->getProductId();
            $product = $this->productRepository->getById($productId);

            // Get the attribute label (works for dropdown/yes-no)
            $fflLabel = $product->getAttributeText('ffl_selection_required');

            return $fflLabel ?: 'No';
        } catch (\Exception $e) {
            return 'No';
        }
    }

    /**
     * Check if product is FFL
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    public function isFflProduct(\Magento\Sales\Model\Order\Item $item): bool
    {
        $label = $this->getFflLabel($item);
        return strtolower($label) === 'yes';
    }
}
