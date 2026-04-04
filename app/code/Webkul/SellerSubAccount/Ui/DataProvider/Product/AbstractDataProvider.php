<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Ui\DataProvider\Product;

use Magento\Catalog\Ui\DataProvider\Product\Related\AbstractDataProvider as CatalogAbstractDataProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Webkul\SellerSubAccount\Helper\Data as HelperData;
use Webkul\Marketplace\Model\Product as SellerProduct;

/**
 * Class AbstractDataProvider
 */
abstract class AbstractDataProvider extends CatalogAbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    public $request;

    /**
     * @var ProductRepositoryInterface
     */
    public $productRepository;

    /**
     * @var StoreRepositoryInterface
     */
    public $storeRepository;

    /**
     * @var ProductLinkRepositoryInterface
     */
    public $productLinkRepository;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @var HelperData
     */
    public $_helper;

    /**
     * @var SellerProduct
     */
    public $_sellerProduct;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     * @param StoreRepositoryInterface $storeRepository
     * @param ProductLinkRepositoryInterface $productLinkRepository
     * @param SellerProduct $sellerProduct
     * @param HelperData $helper
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        StoreRepositoryInterface $storeRepository,
        ProductLinkRepositoryInterface $productLinkRepository,
        SellerProduct $sellerProduct,
        HelperData $helper,
        $addFieldStrategies,
        $addFilterStrategies,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $request,
            $productRepository,
            $storeRepository,
            $productLinkRepository,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );

        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->storeRepository = $storeRepository;
        $this->productLinkRepository = $productLinkRepository;
        $this->_helper = $helper;
        $this->_sellerProduct = $sellerProduct;
    }

    /**
     * Get Collection
     *
     * @return void
     */
    public function getCollection()
    {
        $sellerId = $this->_helper->getCustomerId();
        $subAccount = $this->_helper->getCurrentSubAccount();
        if ($subAccount->getId()) {
            $sellerId = $this->_helper->getSubAccountSellerId();
        }
        $marketplaceProduct = $this->_sellerProduct
        ->getCollection()
        ->addFieldToFilter('seller_id', $sellerId);
        $allIds = $marketplaceProduct->getAllIds();
        /** @var Collection $collection */
        $collection = parent::getCollection();
        $collection->addAttributeToSelect('status');
        $collection->addFieldToFilter('entity_id', ['in' => $allIds]);

        if ($this->getStore()) {
            $collection->setStore($this->getStore());
        }

        if (!$this->getProduct()) {
            return $collection;
        }

        $collection->addAttributeToFilter(
            $collection->getIdFieldName(),
            ['nin' => [$this->getProduct()->getId()]]
        );

        return $this->addCollectionFilters($collection);
    }
}
