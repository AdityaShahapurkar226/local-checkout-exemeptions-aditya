<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Finder\Result;

class GetSimplePackId
{
    /**
     * @var int
     */
    private $lastId = 0;

    public function execute(): int
    {
        return ++$this->lastId;
    }
}
