<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\Sales\Model\ResourceModel\Order\Grid\Collection;

use Amasty\Mostviewed\Model\ResourceModel\Pack\Sales\AggregatedByPackTable\JoinProcessor;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class JoinPackData
{
    /**
     * @var JoinProcessor
     */
    private $joinProcessor;

    public function __construct(JoinProcessor $joinProcessor)
    {
        $this->joinProcessor = $joinProcessor;
    }

    public function beforeLoad(Collection $subject): void
    {
        $this->joinProcessor->execute($subject);
    }
}
