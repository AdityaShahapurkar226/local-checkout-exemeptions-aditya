<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Analytic;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class GetRecommendationList
{
    /**
     * @var PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var IsPackBad
     */
    private $isPackBad;

    public function __construct(
        PackRepositoryInterface $packRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        IsPackBad $isPackBad
    ) {
        $this->packRepository = $packRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->isPackBad = $isPackBad;
    }

    /**
     * @return PackInterface[]
     */
    public function execute(): array
    {
        $badPacks = [];
        $allPacks = $this->packRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        /** @var PackInterface $pack */
        foreach ($allPacks as $pack) {
            if ($this->isPackBad->execute($pack)) {
                $badPacks[] = $pack;
            }
        }

        return $badPacks;
    }
}
