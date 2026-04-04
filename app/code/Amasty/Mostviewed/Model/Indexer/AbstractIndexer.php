<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Indexer;

use Amasty\Mostviewed\Model\ResourceModel\RuleIndex;
use Amasty\Mostviewed\Model\ResourceModel\RuleIndexFactory;
use Amasty\Mostviewed\Api\GroupRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Amasty\Mostviewed\Api\Data\GroupInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\ActionInterface;

abstract class AbstractIndexer implements ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var RuleIndex
     */
    private $resourceIndex;

    /**
     * @var GroupRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var int
     */
    protected $batchCount;

    /**
     * @var int
     */
    protected $batchCacheCount;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    public function __construct(
        RuleIndexFactory $resourceIndexFactory,
        GroupRepositoryInterface $ruleRepository,
        ProductCollectionFactory $productCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CacheContext $cacheContext,
        ManagerInterface $eventManager,
        $batchCount = 1000,
        $batchCacheCount = 100
    ) {
        $this->resourceIndex = $resourceIndexFactory->create();
        $this->ruleRepository = $ruleRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->cacheContext = $cacheContext;
        $this->batchCount = $batchCount;
        $this->batchCacheCount = $batchCacheCount;
        $this->eventManager = $eventManager;
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->doReindex();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     *
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->doReindex($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     *
     * @return void
     */
    public function executeRow($id)
    {
        $ids = [$id];
        $this->doReindex($ids);
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     *
     * @return void
     * @api
     */
    public function execute($ids)
    {
        $this->doReindex($ids);
    }

    /**
     * @param string $relation
     * @param array $ids
     */
    protected function clean($relation, $ids = [])
    {
        $this->getIndexResource()->cleanEmptyData();
        if (empty($ids)) {
            $this->getIndexResource()->cleanAllIndex($relation);
        } else {
            $this->cleanList($relation, $ids);
        }
    }

    /**
     * @return RuleIndex
     */
    protected function getIndexResource()
    {
        return $this->resourceIndex;
    }

    /**
     * @param $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    private function loadRules($searchCriteria)
    {
        return $this->ruleRepository->getList($searchCriteria);
    }

    /**
     * @param array $ids
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    protected function getRules($ids = [])
    {
        if (!empty($ids)) {
            $this->searchCriteriaBuilder->addFilter(GroupInterface::GROUP_ID, $ids, 'in');
        }
        $this->searchCriteriaBuilder->addFilter(GroupInterface::STATUS, 1);

        return $this->loadRules($this->searchCriteriaBuilder->create());
    }

    /**
     * @param string $cacheTag
     * @param array $ids
     */
    protected function registerEntities($cacheTag, $ids)
    {
        $this->cacheContext->registerEntities($cacheTag, $ids);
        if ($this->cacheContext->getSize() > $this->batchCacheCount) {
            $this->cleanCache();
            $this->cacheContext->flush();
        }
    }

    /**
     *
     */
    protected function cleanCache()
    {
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
    }

    /**
     * @param string $relation
     * @param array $ids
     *
     * @return void
     */
    abstract protected function cleanList($relation, $ids);

    /**
     * @param array $ids
     *
     * @return void
     */
    abstract protected function doReindex($ids = []);
}
