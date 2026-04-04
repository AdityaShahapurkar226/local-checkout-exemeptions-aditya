<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Plugin\Marketplace\Block;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Webkul\Marketplace\Model\ProductFactory as MpProductModel;

class Collection extends \Webkul\Marketplace\Block\Collection
{
    /** @var \Webkul\MpAssignProduct\Helper\Data */
    protected $helper;
    
    /**
     * Initialization
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Stdlib\StringUtils $stringUtils
     * @param MpHelper $mpHelper
     * @param MpProductModel $mpProductModel
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Stdlib\StringUtils $stringUtils,
        MpHelper $mpHelper,
        MpProductModel $mpProductModel,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $postDataHelper,
            $urlHelper,
            $productCollectionFactory,
            $layerResolver,
            $categoryRepository,
            $stringUtils,
            $mpHelper,
            $mpProductModel,
            $data
        );
    }

    /**
     * Get product collection
     *
     * @return $collection
     */
    public function _getProductCollection()
    {
        if (!$this->_productlists) {
            $paramData = $this->getRequest()->getParams();
            $partner = $this->getProfileDetail();
            try {
                $sellerId = $partner->getSellerId();
            } catch (\Exception $e) {
                $sellerId = 0;
            }

            $productname = $this->getRequest()->getParam('name');
            $querydata = $this->mpProductModel->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'seller_id',
                            ['eq' => $sellerId]
                        )
                        ->addFieldToFilter(
                            'status',
                            ['eq' => 1]
                        )
                        ->addFieldToSelect('mageproduct_id')
                        ->setOrder('mageproduct_id');

            $layer = $this->getLayer();

            $origCategory = null;
            if (isset($paramData['c']) || isset($paramData['cat'])) {
                try {
                    if (isset($paramData['c'])) {
                        $catId = $paramData['c'];
                    }
                    if (isset($paramData['cat'])) {
                        $catId = $paramData['cat'];
                    }
                    $category = $this->_categoryRepository->get($catId);
                } catch (\Exception $e) {
                    $category = null;
                }

                if ($category) {
                    $origCategory = $layer->getCurrentCategory();
                    $layer->setCurrentCategory($category);
                }
            }
            $productIds = $querydata->getData();
            $checkAssignProduct = $this->helper->checkIfSellerHasAssignedProduct($sellerId);
            if ($checkAssignProduct) {
                $assignedProduct = $checkAssignProduct->getAllIds();
                // print_r($assignedProduct);
                $productIds = array_merge($productIds, $assignedProduct);
            }
            $collection = $layer->getProductCollection();
           
            $collection->addAttributeToSelect('*');
            $collection->addAttributeToFilter(
                'entity_id',
                ['in' => $productIds]
            );
            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            $this->_productlists = $collection;

            if ($origCategory) {
                $layer->setCurrentCategory($origCategory);
            }
            $toolbar = $this->getToolbarBlock();
            $this->configureProductToolbar($toolbar, $collection);

            $this->_eventManager->dispatch(
                'catalog_block_product_list_collection',
                ['collection' => $collection]
            );
        }
        $this->_productlists->getSize();

        return $this->_productlists;
    }
}
