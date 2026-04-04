<?php

namespace Ahy\EstateApiIntegration\Plugin;

use Magento\Checkout\Model\Cart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Psr\Log\LoggerInterface;

class AddToCartPlugin
{
    protected $productRepository;
    protected $customerSession;
    protected $logger;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        CustomerSession $customerSession,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
    }

    /**
     * Plugin before adding product to cart
     *
     * @param Cart $subject
     * @param mixed $productInfo
     * @param null|mixed $requestInfo
     * @return array
     */
    public function beforeAddProduct(Cart $subject, $productInfo, $requestInfo = null)
    {
        try {
            $productId = is_numeric($productInfo) ? $productInfo : $productInfo->getId();
            $product = $this->productRepository->getById($productId);

            $upc = $product->getData('upc_number');
            if (!$upc && $product->getTypeId() === 'simple') {
                $parentIds = $product->getTypeInstance()->getParentIdsByChild($product->getId());
                if (!empty($parentIds)) {
                    $parentProduct = $this->productRepository->getById($parentIds[0]);
                    $upc = $parentProduct->getData('upc_number');
                }
            }

            if ($upc) {
                $this->customerSession->setData('estate_upc', $upc);
                $this->logger->info('[AddToCartPlugin] Stored UPC in session: ' . $upc);
            } else {
                $this->logger->warning('[AddToCartPlugin] UPC not found for product ID: ' . $productId);
            }
        } catch (\Exception $e) {
            $this->logger->error('[AddToCartPlugin] Error: ' . $e->getMessage());
        }

        return [$productInfo, $requestInfo];
    }
}
