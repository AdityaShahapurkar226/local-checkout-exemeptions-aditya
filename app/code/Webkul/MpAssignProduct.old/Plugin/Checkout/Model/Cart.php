<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Plugin\Checkout\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Cart
{
    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $helper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository
    ) {
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
        $this->productRepository = $productRepository;
    }

    /**
     * Plugin for addProduct
     *
     * @param \Magento\Checkout\Model\Cart $subject
     * @param int|Product $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     */
    public function beforeAddProduct(
        \Magento\Checkout\Model\Cart $subject,
        $productInfo,
        $requestInfo = null
    ) {
        if (isset($requestInfo['mpassignproduct_id'])) {
            return [$productInfo, $requestInfo];
        }
        $product = $this->_getProduct($productInfo);
        if ($product === null) {
            return [$productInfo, $requestInfo];
        }
        $productId = $product->getId();
        $itemsCollection = $this->helper->getCollection()
                              ->addFieldToFilter('assign_product_id', $productId)->getFirstItem();
        $requestInfo['mpassignproduct_id'] = $itemsCollection->getId();
        return [$productInfo, $requestInfo];
    }

    /**
     * Get product object based on requested product information
     *
     * @param   Product|int|string $productInfo
     * @return  Product
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getProduct($productInfo)
    {
        $product = null;
        if ($productInfo instanceof Product) {
            $product = $productInfo;
        } elseif (is_int($productInfo) || is_string($productInfo)) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productInfo, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $product = null;
            }
        }
        return $product;
    }
}
