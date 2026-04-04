<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Api;

/**
 * @api
 */
interface ClickRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Mostviewed\Api\Data\ClickInterface $click
     * @return \Amasty\Mostviewed\Api\Data\ClickInterface
     */
    public function save(\Amasty\Mostviewed\Api\Data\ClickInterface $click);

    /**
     * Get by id
     *
     * @param int $id
     * @return \Amasty\Mostviewed\Api\Data\ClickInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete
     *
     * @param \Amasty\Mostviewed\Api\Data\ClickInterface $click
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Mostviewed\Api\Data\ClickInterface $click);

    /**
     * Delete by id
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     * @return int
     */
    public function getCountLoaded();

    /**
     * @return void
     */
    public function deleteLoaded();

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
