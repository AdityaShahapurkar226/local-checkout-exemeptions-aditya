<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Analytics;

use Amasty\Mostviewed\Model\Analytics\Collector\CollectorInterface;

class Collector
{
    /**
     * @var CollectorInterface[]
     */
    private $collectors;

    public function __construct(
        array $collectors = []
    ) {
        $this->collectors = $collectors;
    }

    /**
     * Collect analytics action data
     */
    public function execute(): void
    {
        foreach ($this->collectors as $collector) {
            if ($collector instanceof CollectorInterface) {
                $collector->execute();
            }
        }
    }
}
