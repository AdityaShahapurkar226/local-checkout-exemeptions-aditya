<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Block\Widget;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\Customer\GroupValidator;
use Magento\Customer\Api\Data\GroupInterface as CustomerGroupInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Catalog\Model\Product;
use Amasty\Mostviewed\Model\Pack;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Amasty\Mostviewed\Model\ResourceModel\Product\CollectionFactory;
use Magento\Widget\Block\BlockInterface;
use Amasty\Mostviewed\Model\ResourceModel\Product\Collection as ProductCollection;

class PackList extends \Magento\Catalog\Block\Product\AbstractProduct implements IdentityInterface, BlockInterface
{
    /**
     * @var array
     */
    private $bundles = null;

    /**
     * @var ProductCollection|null
     */
    private $collection = null;

    /**
     * @var string
     */
    protected $_template = 'Amasty_Mostviewed::bundle/list.phtml';

    /**
     * @var \Amasty\Mostviewed\Api\PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Product\Visibility
     */
    private $catalogProductVisibility;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    private $stockHelper;

    /**
     * @var GroupValidator
     */
    private $groupValidator;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Amasty\Mostviewed\Api\PackRepositoryInterface $packRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        GroupValidator $groupValidator,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->packRepository = $packRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionFactory = $collectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->stockHelper = $stockHelper;
        $this->groupValidator = $groupValidator;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getBundles()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'amrelated.bundle.page.pager'
            )->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @param Product $parentProduct
     *
     * @return string
     */
    public function renderBundle(Product $parentProduct)
    {
        $layout = $this->getLayout();
        $block = $layout->getBlock('amrelated.bundle.page.wrapper');
        if (!$block) {
            $block = $layout->createBlock(
                \Amasty\Mostviewed\Block\Product\BundlePackWrapper::class,
                'amrelated.bundle.page.wrapper',
                [ 'data' => [] ]
            );
        }

        $bundles = [];

        foreach ($this->getBundles() as $bundle) {
            if ($parentIds = $bundle->getParentIds()) {
                foreach ($parentIds as $parentId) {
                    if ($parentId == $parentProduct->getId()) {
                        $bundles[] = $bundle;
                    }
                }
            }
        }

        $html = '';

        if ($bundles) {
            $html .= $block->setBundles($bundles)
                ->setProduct($parentProduct)
                ->toHtml();
        }

        return $html;
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
        $identities = [];
        if (!$this->getBundles()) {
            return $identities;
        }

        foreach ($this->getBundles() as $bundle) {
            $identities[] = Pack::CACHE_TAG . '_' . $bundle->getPackId();
            foreach (explode(',', $bundle->getProductIds()) as $productId) {
                $identities[] = Product::CACHE_TAG . '_' . $productId;
            }
        }

        return $identities;
    }

    /**
     * @return array
     */
    public function getBundles()
    {
        if ($this->bundles === null) {
            /** @var PackInterface $pack */
            $bundles = $this->packRepository->getPacksByStore((int) $this->_storeManager->getStore()->getId());
            foreach ($bundles as $key => $pack) {
                if (!$this->groupValidator->validate($pack)) {
                    unset($bundles[$key]);
                } else {
                    $this->bundles[$pack->getPackId()] = $pack;
                }
            }

            if ($this->bundles) {
                $this->generateProductCollection(array_keys($this->bundles));
            }
        }

        return $this->bundles;
    }

    /**
     * @param array $bundleIds
     */
    private function generateProductCollection($bundleIds)
    {
        /** @var ProductCollection $products */
        $collection = $this->collectionFactory->create()
            ->applyBundleFilter($bundleIds);
        $collection->addAttributeToSelect(
            'required_options'
        )->addStoreFilter();

        $this->_addProductAttributesAndPrices($collection);
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $this->stockHelper->addIsInStockFilterToCollection($collection);
        $collection->getSelect()->group('e.entity_id');
        $this->collection = $collection;
    }

    /**
     * @param string|array $productIds
     *
     * @return array
     */
    public function getProductItems($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = explode(',', $productIds);
        }

        /** @var ProductCollection $products */
        $collection = $this->collectionFactory->create()
            ->addIdFilter($productIds);

        $collection->addAttributeToSelect(
            'required_options'
        )->addStoreFilter();

        $this->_addProductAttributesAndPrices($collection);
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $this->stockHelper->addIsInStockFilterToCollection($collection);

        $productItems = [];
        foreach ($collection as $product) {
            $product->setDoNotUseCategoryId(true);
            $productItems[$product->getId()] = $product;
        }

        return $productItems;
    }

    /**
     * @return ProductCollection|null
     */
    public function getCollection()
    {
        return $this->collection;
    }
}
