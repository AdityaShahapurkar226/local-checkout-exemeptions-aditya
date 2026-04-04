<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpAssignProduct
 * @author Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpAssignProduct\Plugin\Catalog\Model;

class Collection
{
    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $helper;
    /**
     *
     * @var [type]
     */
    protected $_productStoreId;

    /**
     * @var \Webkul\MpAssignProduct\Model\AssociatesFactory
     */
    protected $associatesFactory;

    /** @var StoreManagerInterface */
    protected $_storeManager;

    /** @var ResourceConnection  */
    protected $resource;

    /** @var $_conn */
    protected $_conn;

    /** @var RequestInterface */
    protected $request;
    
    /**
     * Initialization
     *
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Webkul\MpAssignProduct\Model\AssociatesFactory $associatesFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Webkul\MpAssignProduct\Model\AssociatesFactory $associatesFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->associatesFactory = $associatesFactory;
        $this->_storeManager = $storeManager;
        $this->resource = $resource;
        $this->_conn = $resource->getConnection();
        $this->request = $request;
    }

   /**
    * Plugin for get product collection
    *
    * @param \Magento\Catalog\Model\ResourceModel\Category\Collection $subject
    * @param [type] $items
    * @param [type] $countRegular
    * @param [type] $countAnchor
    * @param [type] $result
    * @return void
    */
    public function afterLoadProductCount(
        \Magento\Catalog\Model\ResourceModel\Category\Collection $subject,
        $items,
        $countRegular,
        $countAnchor,
        $result
    ) {
        $assignProductsIds = $this->helper->getCollection()->getAllIds();
        $associateProductIds = $this->associatesFactory->create()->getCollection()->getAllIds();
        $assignProductsIds = array_merge($assignProductsIds, $associateProductIds);
        $actionName = $this->request->getFullActionName();
        if (!empty($assignProductsIds) && $actionName != 'marketplace_seller_collection') {
            
            $anchor = [];
            $regular = [];
            $websiteId = $this->_storeManager->getStore($this->getProductStoreId())->getWebsiteId();

            foreach ($items as $item) {
                if ($item->getIsAnchor()) {
                    $anchor[$item->getId()] = $item;
                } else {
                    $regular[$item->getId()] = $item;
                }
            }

            if ($countRegular) {
                // Retrieve regular categories product counts
                $regularIds = array_keys($regular);
                
                if (!empty($regularIds)) {
                    
                    $select = $this->_conn->select();
                    $categoryTable = $this->resource->getTableName('catalog_category_product');
                    $categoryWebsiteTable = $this->resource->getTableName('catalog_product_website');
                    $select->from(
                        ['main_table' => $categoryTable],
                        ['category_id', new \Zend_Db_Expr('COUNT(main_table.product_id)')]
                    )->where(
                        $this->_conn->quoteInto('main_table.category_id IN(?)', $regularIds).
                        'AND main_table.product_id NOT IN (\'' . implode("', '", $assignProductsIds) . "' )"
                    )->group(
                        'main_table.category_id'
                    );
                    if ($websiteId) {
                        $select->join(
                            ['w' => $categoryWebsiteTable],
                            'main_table.product_id = w.product_id',
                            []
                        )->where(
                            'w.website_id = ?',
                            $websiteId
                        );
                    }
                    $counts = $this->_conn->fetchPairs($select);
                    foreach ($regular as $item) {
                        if (isset($counts[$item->getId()])) {
                            $item->setProductCount($counts[$item->getId()]);
                        } else {
                            $item->setProductCount(0);
                        }
                    }
                }
            }

            if ($countAnchor) {
                $categoryTable = $this->resource->getTableName('catalog_category_product');
                $categoryWebsiteTable = $this->resource->getTableName('catalog_product_website');
                $categoryEntityTable = $this->resource->getTableName('catalog_category_entity');
                // Retrieve Anchor categories product counts
                foreach ($anchor as $item) {
                    if ($allChildren = $item->getAllChildren()) {
                        $bind = ['entity_id' => $item->getId(), 'c_path' => $item->getPath() . '/%'];
                        $select = $this->_conn->select();
                        $select->from(
                            ['main_table' => $categoryTable],
                            new \Zend_Db_Expr('COUNT(DISTINCT main_table.product_id)')
                        )->joinInner(
                            ['e' => $categoryEntityTable],
                            'main_table.category_id=e.entity_id',
                            []
                        )->where(
                            '(e.entity_id = :entity_id OR e.path LIKE :c_path) AND
                            main_table.product_id NOT IN (\'' . implode("', '", $assignProductsIds) . "' )"
                        );
                        if ($websiteId) {
                            $select->join(
                                ['w' => $categoryWebsiteTable],
                                'main_table.product_id = w.product_id',
                                []
                            )->where(
                                'w.website_id = ?',
                                $websiteId
                            );
                        }
                        $item->setProductCount((int)$this->_conn->fetchOne($select, $bind));
                    } else {
                        $item->setProductCount(0);
                    }
                }
            }
            return $this;
        }
        return $result;
    }
    /**
     * Get id of the store that we should count products on
     *
     * @return int
     */
    public function getProductStoreId()
    {
        if ($this->_productStoreId === null) {
            $this->_productStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }
        return $this->_productStoreId;
    }
}
