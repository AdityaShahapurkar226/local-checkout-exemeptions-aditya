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
interface GroupRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Mostviewed\Api\Data\GroupInterface $group
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function save(\Amasty\Mostviewed\Api\Data\GroupInterface $group);

    /**
     * Get by id
     *
     * @param int $groupId
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($groupId);

    /**
     * Delete
     *
     * @param \Amasty\Mostviewed\Api\Data\GroupInterface $group
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Mostviewed\Api\Data\GroupInterface $group);

    /**
     * Delete by id
     *
     * @param int $groupId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($groupId);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param $id
     *
     * @return $this
     */
    public function duplicate($id);

    /**
     * @param int $entityId
     * @param string $position
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     */
    public function getGroupByIdAndPosition($entityId, $position);

    /**
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getNew();

    /**
     * @param \Amasty\Mostviewed\Api\Data\GroupInterface $group
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface|false
     */
    public function validateGroup($group);
}
