<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\ViewModel\Pack\Analytic;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\Pack\Analytic\GetRecommendationList;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class RecommendationList implements ArgumentInterface
{

    /**
     * @var GetRecommendationList
     */
    private $getRecommendationList;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        GetRecommendationList $getRecommendationList,
        UrlInterface $urlBuilder
    ) {
        $this->getRecommendationList = $getRecommendationList;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return PackInterface[]
     */
    public function getList(): array
    {
        return $this->getRecommendationList->execute();
    }

    public function isPackNew(PackInterface $pack): bool
    {
        return strtotime($pack->getCreatedAt()) > strtotime('-1 month');
    }

    public function getPackUrl(int $packId): string
    {
        return $this->urlBuilder->getUrl('amasty_mostviewed/pack/edit', ['id' => $packId]);
    }
}
