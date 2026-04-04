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

namespace Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\FrontGrid;

use Magento\Framework\Api\Search\SearchResultInterface as ApiSearchResultInterface;
use Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\Collection as SubAccountCollection;
use Magento\Framework\Search\AggregationInterface as SearchAggregationInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb as ResourceModelAbstractDb;

use Webkul\SellerSubAccount\Helper\Data as HelperData;

/**
 * Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\Grid\Collection Class
 * Collection for displaying grid of SellerSubAccount SubAccount.
 */
class Collection extends SubAccountCollection implements ApiSearchResultInterface
{
    /**
     * @var SearchAggregationInterface
     */
    public $aggregations;

    /**
     * @var HelperData
     */
    public $helperData;

    /**
     * @param EntityFactoryInterface                               $entityFactoryInterface
     * @param LoggerInterface                                      $loggerInterface
     * @param FetchStrategyInterface                               $fetchStrategyInterface
     * @param EventManagerInterface                                $eventManagerInterface
     * @param StoreManagerInterface                                $storeManagerInterface
     * @param HelperData                                           $helperData
     * @param mixed|null                                           $mainTable
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $eventPrefix
     * @param mixed                                                $eventObject
     * @param mixed                                                $resourceModel
     * @param string                                               $model
     * @param null                                                 $connection
     * @param ResourceModelAbstractDb|null                         $resource
     *
     */
    public function __construct(
        EntityFactoryInterface $entityFactoryInterface,
        LoggerInterface $loggerInterface,
        FetchStrategyInterface $fetchStrategyInterface,
        EventManagerInterface $eventManagerInterface,
        StoreManagerInterface $storeManagerInterface,
        HelperData $helperData,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
        ResourceModelAbstractDb $resource = null
    ) {
        $this->helperData = $helperData;
        parent::__construct(
            $entityFactoryInterface,
            $loggerInterface,
            $fetchStrategyInterface,
            $eventManagerInterface,
            $storeManagerInterface,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * Get Aggregations
     *
     * @return SearchAggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * Set Aggregations
     *
     * @param SearchAggregationInterface $aggregationsData
     *
     * @return $this
     */
    public function setAggregations($aggregationsData)
    {
        $this->aggregations = $aggregationsData;
    }
    
    /**
     * Retrieve all ids for collection
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol(
            $this->_getAllIdsSelect($limit, $offset),
            $this->_bindParams
        );
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return $this
     */
    public function setSearchCriteria(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
    ) {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     *
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    public function _renderFiltersBefore()
    {
        $joinTable = $this->getTable('customer_grid_flat');
        $this->getSelect()->join(
            $joinTable.' as cgf',
            'main_table.customer_id = cgf.entity_id',
            [
                'name' => 'name',
                'email' => 'email',
                'customer_created_at' => 'created_at'
            ]
        );
        $sellerId = $this->helperData->getCustomerId();
        $this->getSelect()->where("main_table.seller_id = ".$sellerId);
        parent::_renderFiltersBefore();
    }
}
