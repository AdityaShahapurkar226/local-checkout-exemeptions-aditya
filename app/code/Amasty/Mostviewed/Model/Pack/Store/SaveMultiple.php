<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Store;

use Amasty\Mostviewed\Model\ResourceModel\Pack\Store\DeleteByPackId;
use Amasty\Mostviewed\Model\ResourceModel\Pack\Store\InsertMultiple;
use Zend_Db_Exception;

class SaveMultiple
{
    /**
     * @var InsertMultiple
     */
    private $insertMultiple;

    /**
     * @var DeleteByPackId
     */
    private $deleteByPackId;

    public function __construct(
        InsertMultiple $insertMultiple,
        DeleteByPackId $deleteByPackId
    ) {
        $this->insertMultiple = $insertMultiple;
        $this->deleteByPackId = $deleteByPackId;
    }

    /**
     * @param int $packId
     * @param array $stores
     * @return void
     * @throws Zend_Db_Exception
     */
    public function execute(int $packId, array $stores): void
    {
        $data = [];
        foreach ($stores as $storeId) {
            $data[] = [
                Table::PACK_COLUMN => $packId,
                Table::STORE_COLUMN => (int) $storeId
            ];
        }
        $this->deleteByPackId->execute($packId);
        $this->insertMultiple->execute($data);
    }
}
