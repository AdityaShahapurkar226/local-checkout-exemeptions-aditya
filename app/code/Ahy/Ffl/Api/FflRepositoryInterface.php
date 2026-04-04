<?php
/**
 * Copyright © Ahy Consulting  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Ffl\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface FflRepositoryInterface
{

    /**
     * Save Ffl
     * @param \Ahy\Ffl\Api\Data\FflInterface $ffl
     * @return \Ahy\Ffl\Api\Data\FflInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Ahy\Ffl\Api\Data\FflInterface $ffl);

    /**
     * Retrieve Ffl
     * @param string $entityId
     * @return \Ahy\Ffl\Api\Data\FflInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($entityId);

    /**
     * Retrieve Ffl matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ahy\Ffl\Api\Data\FflSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Ffl
     * @param \Ahy\Ffl\Api\Data\FflInterface $ffl
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Ahy\Ffl\Api\Data\FflInterface $ffl);

    /**
     * Delete Ffl by ID
     * @param string $entityId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($entityId);
}

