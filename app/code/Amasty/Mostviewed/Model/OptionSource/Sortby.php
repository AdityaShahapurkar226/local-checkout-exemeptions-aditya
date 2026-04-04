<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\OptionSource;

use Magento\Framework\Module\Manager;
use Magento\Framework\Data\OptionSourceInterface;

class Sortby implements OptionSourceInterface
{
    public const RANDOM = 'random';

    public const NAME = 'name';

    public const PRICE_ASC = 'price_asc';

    public const PRICE_DESC = 'price_desc';

    public const NEWEST = 'newest';

    public const BESTSELLERS = 'bestsellers';

    public const MOST_VIEWED = 'most_viewed';

    public const REVIEWS_COUNT = 'reviews_count';

    public const TOP_RATED = 'rating_summary';

    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        Manager $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    public function toOptionArray(): array
    {
        $options = [
            ['value' => self::RANDOM, 'label' => __('Random')],
            ['value' => self::NAME, 'label' => __('Name')],
            ['value' => self::PRICE_DESC, 'label' => __('Price: high to low')],
            ['value' => self::PRICE_ASC, 'label' => __('Price: low to high')],
            ['value' => self::NEWEST, 'label' => __('Newest')]
        ];

        if ($this->moduleManager->isEnabled('Amasty_Sorting')) {
            $options[] = ['value' => self::BESTSELLERS, 'label' => __('Best Sellers')];
            $options[] = ['value' => self::MOST_VIEWED, 'label' => __('Most Viewed')];
            $options[] = ['value' => self::REVIEWS_COUNT, 'label' => __('Reviews Count')];
            $options[] = ['value' => self::TOP_RATED, 'label' => __('Top Rated')];
        }

        return $options;
    }
}
