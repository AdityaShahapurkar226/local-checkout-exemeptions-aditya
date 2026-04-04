<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Analytic;

use Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\Sales\LoadMaxOrdersCount;

class GetZoneNumber
{
    public const MAX_ZONE = 5;
    public const MIN_ZONE = 1;

    /**
     * @var float|null
     */
    private $step;

    /**
     * @var LoadMaxOrdersCount
     */
    private $loadMaxOrdersCount;

    public function __construct(LoadMaxOrdersCount $loadMaxOrdersCount)
    {
        $this->loadMaxOrdersCount = $loadMaxOrdersCount;
    }

    public function execute(int $countOrders): int
    {
        if ($this->step === null) {
            $this->initStep();
        }

        if ($this->step && !$countOrders) {
            $zone = self::MIN_ZONE;
        } else {
            $zone = $this->step ? (int) ceil($countOrders / $this->step) : 0;
        }

        return $zone;
    }

    private function initStep(): void
    {
        $this->step = $this->loadMaxOrdersCount->execute() / self::MAX_ZONE;
    }
}
