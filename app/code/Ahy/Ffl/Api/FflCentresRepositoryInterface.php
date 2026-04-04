<?php
/**
 * Copyright © Ahy Consulting  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Ffl\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface FflCentresRepositoryInterface
{

    /**
     * Save FflCentres
     * @param \Ahy\Ffl\Api\Data\FflCentresInterface $fflCentres
     * @return \Ahy\Ffl\Api\Data\FflCentresInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Ahy\Ffl\Api\Data\FflCentresInterface $fflCentres
    );

    /**
     * Retrieve FflCentres
     * @param string $fflcentresId
     * @return \Ahy\Ffl\Api\Data\FflCentresInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($fflcentresId);

    /**
     * Retrieve FflCentres matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ahy\Ffl\Api\Data\FflCentresSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete FflCentres
     * @param \Ahy\Ffl\Api\Data\FflCentresInterface $fflCentres
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Ahy\Ffl\Api\Data\FflCentresInterface $fflCentres
    );

    /**
     * Delete FflCentres by ID
     * @param string $fflcentresId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($fflcentresId);
}

