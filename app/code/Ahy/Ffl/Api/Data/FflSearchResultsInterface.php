<?php
/**
 * Copyright © Ahy Consulting  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Ffl\Api\Data;

interface FflSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Ffl list.
     * @return \Ahy\Ffl\Api\Data\FflInterface[]
     */
    public function getItems();

    /**
     * Set title list.
     * @param \Ahy\Ffl\Api\Data\FflInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

