<?php
/**
 * Copyright © Ahy Consulting  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Ffl\Api\Data;

interface FflCentresSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get FflCentres list.
     * @return \Ahy\Ffl\Api\Data\FflCentresInterface[]
     */
    public function getItems();

    /**
     * Set CentreName list.
     * @param \Ahy\Ffl\Api\Data\FflCentresInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

