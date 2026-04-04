<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Analytics\Collector\Utils;

use Amasty\Mostviewed\Api\AnalyticRepositoryInterface;
use Amasty\Mostviewed\Api\Data\AnalyticInterface;
use Amasty\Mostviewed\Model\Analytics\Analytic;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;

class GetAnalyticsItems
{
    /**
     * @var AnalyticRepositoryInterface
     */
    private $analyticRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        AnalyticRepositoryInterface $analyticRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->analyticRepository = $analyticRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Retrieve analytics items by type
     *
     * @param string $type
     * @return array
     */
    public function execute(string $type): array
    {
        $analyticsItems = [];

        foreach ($this->loadAnalyticsItems($type) as $analyticsItem) {
            /** @var Analytic $analyticsItem */
            $analyticsItems[$analyticsItem->getBlockId()] = $analyticsItem;
        }

        return $analyticsItems;
    }

    private function loadAnalyticsItems(string $type): array
    {
        try {
            $result = $this->analyticRepository->getList(
                $this->searchCriteriaBuilder
                    ->addFilter(AnalyticInterface::TYPE, $type)
                    ->create()
            )->getItems();
        } catch (NoSuchEntityException $e) {
            $result = [];
        }

        return $result;
    }
}
