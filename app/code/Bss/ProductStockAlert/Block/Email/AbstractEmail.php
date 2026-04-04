<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Block\Email;

use Magento\Catalog\Model\Product;

/**
 * Class AbstractEmail
 * @package Bss\ProductStockAlert\Block\Email
 */
abstract class AbstractEmail extends \Magento\Framework\View\Element\Template
{
    /**
     * Product collection array
     *
     * @var array
     */
    protected $products = [];

    /**
     * @var array
     */
    protected $productDataArr = [];

    /**
     * Current Store scope object
     *
     * @var \Magento\Store\Model\Store
     */
    protected $store;

    /**
     * @var \Magento\Framework\Filter\Input\MaliciousCode
     */
    protected $maliciousCode;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageBuilder;

    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * AbstractEmail constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Filter\Input\MaliciousCode $maliciousCode,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->maliciousCode = $maliciousCode;
        $this->imageBuilder = $imageBuilder;
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    /**
     * Filter malicious code before insert content to email
     *
     * @param  string|array $content
     * @return string|array
     */
    public function getFilteredContent($content)
    {
        return $this->maliciousCode->filter($content);
    }

    /**
     * Set Store scope
     *
     * @param int|string|\Magento\Store\Model\Website|\Magento\Store\Model\Store $store
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setStore($store)
    {
        if ($store instanceof \Magento\Store\Model\Website) {
            $store = $store->getDefaultStore();
        }
        if (!$store instanceof \Magento\Store\Model\Store) {
            $store = $this->_storeManager->getStore($store);
        }

        $this->store = $store;

        return $this;
    }

    /**
     * Retrieve current store object
     *
     * @return \Magento\Store\Model\Store
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        if ($this->getData('store')) {
            return $this->getData('store');
        }

        if ($this->store === null) {
            $this->store = $this->_storeManager->getStore();
        }
        return $this->store;
    }

    /**
     * Reset product collection
     *
     * @return void
     */
    public function reset()
    {
        $this->products = [];
    }

    /**
     * Add product to collection
     *
     * @param array $productData
     * @return void
     */
    public function addProductData($productData)
    {
        $this->productDataArr = $productData;
    }

    /**
     * Retrieve product collection array
     *
     * @return array
     */
    public function getProductData()
    {
        if ($this->getData('product_data')) {
            return $this->getData('product_data');
        }
        return $this->productDataArr;
    }

    /**
     * Get store url params
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getUrlParams()
    {
        return ['_scope' => $this->getStore(), '_scope_to_url' => true];
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
            ->setImageId($imageId)
            ->setAttributes($attributes)
            ->create();
    }

    /**
     * @param $productId
     * @return Product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct($productId)
    {
        if (isset($this->products[$productId])) {
            return $this->products[$productId];
        }
        $product = $this->productRepository->getById($productId);
        $this->products[$product->getId()] = $product;
        return $product;
    }

    /**
     * @param array $productData
     * @param bool $child
     * @return Product
     */
    public function getProductFromData($productData, $child = true)
    {
        // Get parent
        if (isset($productData['child_id']) &&
            isset($productData['parent_id']) &&
            isset($productData['has_child'])) {
            if (!$child &&
                $productData['has_child'] &&
                $productData['parent_id'] != $productData['child_id']) {
                return $this->getProduct($productData['parent_id']);
            }
            return $this->getProduct($productData['child_id']);
        }
        return null;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function hasProductUrl($product)
    {
        if (!$product) {
            return false;
        }
        if ($product->isVisibleInSiteVisibility()) {
            return true;
        } else {
            if ($product->hasUrlDataObject()) {
                $data = $product->getUrlDataObject();
                if (in_array($data->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Retrieve URL to item Product
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductUrl($product)
    {
        if (!$product) {
            return '';
        }
        $paramName = \Magento\Store\Api\StoreResolverInterface::PARAM_NAME;
        $query = '___from_store='.$this->getStore()->getCode().'&'.$paramName.'='.
            $this->getStore()->getCode().
            '&uenc='.$this->helper->returnUrlEncode()->encode($product->getUrlModel()->getUrl($product));
        $finalRedirect = $this->getStore()->getBaseUrl().'stores/store/switch/?'.$query;
        return $finalRedirect;
    }
}
