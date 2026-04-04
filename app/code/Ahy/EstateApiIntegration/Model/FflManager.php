<?php

declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Psr\Log\LoggerInterface;

class FflManager
{
    private ProductRepositoryInterface $productRepository;
    private Cart $cart;
    private LoggerInterface $logger;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        Cart $cart,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->logger = $logger;
    }

    /**
     * Set the FFL attribute for all products in the cart
     *
     * @param bool $required
     */
    public function setFflRequired(bool $required): void
    {
        $items = $this->cart->getQuote()->getAllItems();

        foreach ($items as $item) {
            try {
                $product = $this->productRepository->getById($item->getProductId());
                $product->setData('ffl_selection_required', $required ? 1 : 0);
                $this->productRepository->save($product);
            } catch (\Exception $e) {
                $this->logger->error(sprintf(
                    'Failed to update FFL for product ID %d: %s',
                    $item->getProductId(),
                    $e->getMessage()
                ));
            }
        }
    }
}
