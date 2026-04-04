<?php
namespace Ahy\EstateApiIntegration\ViewModel;

use Magento\Checkout\Model\Session;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CartProducts implements ArgumentInterface
{
    private Session $checkoutSession;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        Session $checkoutSession,
        ProductRepositoryInterface $productRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
    }

    public function getCartProductIds(): array
    {
        $productIds = [];

        foreach ($this->checkoutSession->getQuote()->getAllVisibleItems() as $item) {
            try {
                $product = $this->productRepository->getById($item->getProductId());
                $productIds[] = $product->getId();
            } catch (\Exception $e) {
                continue; // or log the error
            }
        }

        return $productIds;
    }
}
