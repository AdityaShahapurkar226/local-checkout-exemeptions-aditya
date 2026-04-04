<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Analytics\Collector\Click;

use Amasty\Mostviewed\Api\AnalyticRepositoryInterface;
use Amasty\Mostviewed\Model\Analytics\Analytic;
use Amasty\Mostviewed\Model\Analytics\Click;
use Amasty\Mostviewed\Model\Analytics\Collector\Utils\GetActionSelect;
use Amasty\Mostviewed\Model\Analytics\Collector\Utils\GetAnalyticsItems;
use Amasty\Mostviewed\Model\Analytics\Collector\Utils\GetGroupIds;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class Cart
{
    public const ACTION_TYPE = 'click';
    public const ANALYTICS_TYPE = 'click_cart';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AnalyticRepositoryInterface
     */
    private $analyticRepository;

    /**
     * @var GetActionSelect
     */
    private $getActionSelect;

    /**
     * @var GetGroupIds
     */
    private $getGroupIds;

    /**
     * @var GetAnalyticsItems
     */
    private $getAnalyticsItems;

    public function __construct(
        AnalyticRepositoryInterface $analyticRepository,
        ResourceConnection $resourceConnection,
        GetActionSelect $getActionSelect,
        GetGroupIds $getGroupIds,
        GetAnalyticsItems $getAnalyticsItems
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->analyticRepository = $analyticRepository;
        $this->getActionSelect = $getActionSelect;
        $this->getGroupIds = $getGroupIds;
        $this->getAnalyticsItems = $getAnalyticsItems;
    }

    /**
     * Collect click cart analytics actions
     */
    public function execute(): void
    {
        $connection = $this->resourceConnection->getConnection();
        $actionSelect = $this->getActionSelect->execute(self::ACTION_TYPE);
        $analyticsItems = $this->getAnalyticsItems->execute(self::ANALYTICS_TYPE);

        foreach ($this->getGroupIds->execute() as $groupId) {
            /** @var Analytic $item */
            $item = $analyticsItems[$groupId] ?? $this->analyticRepository->getNew();

            $actionSelect
                ->where('click_type =?', Click::CLICK_TYPE_CART)
                ->where('id > ?', (int) $item->getVersionId())
                ->having('block_id = ?', $groupId);
            if ($statistics = $connection->fetchRow($actionSelect)) {
                $item
                    ->setBlockId($groupId)
                    ->setCounter($item->getCounter() + $statistics['counter'])
                    ->setType(self::ANALYTICS_TYPE)
                    ->setVersionId($statistics['version_id']);
                $this->analyticRepository->save($item);
            }
            $actionSelect->reset(Select::WHERE);
            $actionSelect->reset(Select::HAVING);
        }
    }
}
