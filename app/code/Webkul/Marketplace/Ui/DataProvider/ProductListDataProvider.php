<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Ui\DataProvider;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Marketplace\Helper\Data as HelperData;

/**
 * Class Product Collection data provider
 */
class ProductListDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{
    public const PRODUCT_STATUS = 'product_status';
    public const STATUS = 'is_approved';
    /**
     * Product collection
     *
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

   /**
    * Construct
    *
    * @param string $name
    * @param string $primaryFieldName
    * @param string $requestFieldName
    * @param ProductCollection $productCollection
    * @param CollectionFactory $collectionFactory
    * @param HelperData $helperData
    * @param \Magento\Framework\Registry $registry
    * @param array $addFieldStrategies
    * @param array $addFilterStrategies
    * @param array $meta
    * @param array $data
    */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ProductCollection $productCollection,
        CollectionFactory $collectionFactory,
        HelperData $helperData,
        \Magento\Framework\Registry $registry,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $productCollection,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );

        $sellerId = $helperData->getCustomerId();
        if (!$registry->registry('mp_flat_catalog_flag')) {
            $registry->register('mp_flat_catalog_flag', 1);
        }
        $marketplaceTable = $collectionFactory->create()->getTable('marketplace_product');
        $catalogInventoryStockItem = $collectionFactory->create()->getTable('cataloginventory_stock_item');
        $marketplaceProduct = $collectionFactory->create()
        ->addFieldToFilter('seller_id', $sellerId);
        $allIds = $marketplaceProduct->getAllIds();
        /** @var Collection $collection */
        $collectionData = $productCollection->create();
        
        $collectionData->addAttributeToSelect('status');
       
        $collectionData->getSelect()->join(
            $marketplaceTable.' as cgf',
            'e.entity_id = cgf.mageproduct_id',
            ["product_status" => "status",'is_approved']
        )->where("cgf.seller_id = ".$sellerId);
        $collectionData->getSelect()->join(
            $catalogInventoryStockItem.' as csi',
            'e.entity_id = csi.product_id',
            ["qty" => "qty"]
        )->where("csi.website_id = 0 OR csi.website_id = 1");
       
        $collectionData->setFlag('has_stock_status_filter', 1);
        
        $this->collection = $collectionData;
        // $helperData->logDataInLogger(json_encode($this->collection->getData()));
       
        // $this->addFieldStrategies = $addFieldStrategies;
        // $this->addFilterStrategies = $addFilterStrategies;
        // echo"<pre>";print_r($this->collection->getData());die;
    }

    /**
     * @inheritdoc
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() == self::PRODUCT_STATUS) {
            $this->getCollection()->getSelect()->where('cgf.status ='.$filter->getValue());
        } elseif ($filter->getField() == self::STATUS) {
            $this->getCollection()->getSelect()->where('cgf.is_approved ='.$filter->getValue());
        } else {
            parent::addFilter($filter);
        }
    }
}
