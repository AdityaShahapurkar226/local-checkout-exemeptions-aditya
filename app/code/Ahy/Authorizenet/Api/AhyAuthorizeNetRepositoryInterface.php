<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Authorizenet\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface AhyAuthorizeNetRepositoryInterface
{

    /**
     * Save AhyAuthorizeNet
     * @param \Ahy\Authorizenet\Api\Data\AhyAuthorizeNetInterface $ahyAuthorizeNet
     * @return \Ahy\Authorizenet\Api\Data\AhyAuthorizeNetInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Ahy\Authorizenet\Api\Data\AhyAuthorizeNetInterface $ahyAuthorizeNet
    );

    /**
     * Retrieve AhyAuthorizeNet
     * @param string $ahyauthorizenetId
     * @return \Ahy\Authorizenet\Api\Data\AhyAuthorizeNetInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($ahyauthorizenetId);

    /**
     * Retrieve AhyAuthorizeNet matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Ahy\Authorizenet\Api\Data\AhyAuthorizeNetSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete AhyAuthorizeNet
     * @param \Ahy\Authorizenet\Api\Data\AhyAuthorizeNetInterface $ahyAuthorizeNet
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Ahy\Authorizenet\Api\Data\AhyAuthorizeNetInterface $ahyAuthorizeNet
    );

    /**
     * Delete AhyAuthorizeNet by ID
     * @param string $ahyauthorizenetId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($ahyauthorizenetId);
}

