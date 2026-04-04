<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Analytics;

use Amasty\Mostviewed\Model\ResourceModel\Analytics\Click as ClickResource;
use Amasty\Mostviewed\Model\ResourceModel\Analytics\View as ViewResource;
use Amasty\Mostviewed\Model\ResourceModel\Analytics\Analytic as AnalyticResource;

class ClearAnalytic
{
    /**
     * @var ClickResource
     */
    private $clickResource;

    /**
     * @var ViewResource
     */
    private $viewResource;

    /**
     * @var AnalyticResource
     */
    private $analyticResource;

    public function __construct(
        ClickResource $clickResource,
        ViewResource $viewResource,
        AnalyticResource $analyticResource
    ) {
        $this->clickResource = $clickResource;
        $this->viewResource = $viewResource;
        $this->analyticResource = $analyticResource;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $this->clickResource->getConnection()->truncateTable($this->clickResource->getMainTable());
        $this->viewResource->getConnection()->truncateTable($this->viewResource->getMainTable());
        $this->analyticResource->getConnection()->update($this->analyticResource->getMainTable(), ['version_id' => 0]);
    }
}
