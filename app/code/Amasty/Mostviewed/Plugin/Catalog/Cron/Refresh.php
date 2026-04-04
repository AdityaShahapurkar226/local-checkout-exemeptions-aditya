<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\Catalog\Cron;

use Amasty\Mostviewed\Model\ResourceModel\Pack as ResourcePack;
use Magento\Catalog\Cron\RefreshSpecialPrices;
use Magento\Framework\App\ResourceConnection;
use Amasty\Mostviewed\Api\Data\PackInterface as Pack;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\CacheContext;

class Refresh
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        ResourceConnection $resource,
        CacheContext $cacheContext,
        \Psr\Log\LoggerInterface $logger,
        ManagerInterface $eventManager
    ) {
        $this->resource = $resource;
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
        $this->logger = $logger;
    }

    /**
     * @param RefreshSpecialPrices $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterExecute(RefreshSpecialPrices $subject, $result)
    {
        $connection = $this->_getConnection();
        try {

            $select = $connection->select()->from(
                ['main_table' => $this->resource->getTableName(ResourcePack::PACK_TABLE)],
                ['pack_id']
            )->where(
                '!((ISNULL(main_table.' . Pack::DATE_FROM . ') || main_table.' . Pack::DATE_FROM . ' <= NOW()) AND ' .
                '(ISNULL(main_table.' . Pack::DATE_TO . ') || main_table.' . Pack::DATE_TO . ' >= NOW()))'
            );

            $selectData = $connection->fetchCol($select);
            if (!empty($selectData)) {
                $this->cacheContext->registerEntities(\Amasty\Mostviewed\Model\Pack::CACHE_TAG, $selectData);
                $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
            }
        } catch (\Exception $ex) {
            $this->logger->critical($ex);
        }

        return $result;
    }

    /**
     * Retrieve write connection instance
     *
     * @return bool|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getConnection()
    {
        if (null === $this->connection) {
            $this->connection = $this->resource->getConnection();
        }
        return $this->connection;
    }
}
