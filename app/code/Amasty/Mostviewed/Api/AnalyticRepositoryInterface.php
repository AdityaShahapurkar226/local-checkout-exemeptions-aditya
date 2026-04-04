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
interface AnalyticRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Mostviewed\Api\Data\AnalyticInterface $analytic
     * @return \Amasty\Mostviewed\Api\Data\AnalyticInterface
     */
    public function save(\Amasty\Mostviewed\Api\Data\AnalyticInterface $analytic);

    /**
     * Get by id
     *
     * @param int $id
     * @return \Amasty\Mostviewed\Api\Data\AnalyticInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * @return \Amasty\Mostviewed\Model\Analytics\Analytic
     */
    public function getNew();

    /**
     * Delete
     *
     * @param \Amasty\Mostviewed\Api\Data\AnalyticInterface $analytic
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Mostviewed\Api\Data\AnalyticInterface $analytic);

    /**
     * Delete by id
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
