<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\ConditionalDiscount\Command;

use Magento\Framework\Exception\CouldNotDeleteException;

interface RemoveByPackIdInterface
{
    /**
     * @param int $packId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function execute(int $packId): bool;
}
