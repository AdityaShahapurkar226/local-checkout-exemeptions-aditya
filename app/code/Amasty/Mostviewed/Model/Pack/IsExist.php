<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack;

use Amasty\Mostviewed\Model\ResourceModel\Pack\IsExist as IsExistResource;
use Psr\Log\LoggerInterface;
use Zend_Db_Exception;

class IsExist
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var IsExistResource
     */
    private $isExist;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(IsExistResource $isExist, LoggerInterface $logger)
    {
        $this->isExist = $isExist;
        $this->logger = $logger;
    }

    public function execute(int $id): bool
    {
        if (!isset($this->cache[$id])) {
            try {
                $this->cache[$id] = $this->isExist->execute($id);
            } catch (Zend_Db_Exception $e) {
                $this->logger->error($e->getMessage());
                $this->cache[$id] = false;
            }
        }

        return $this->cache[$id];
    }
}
