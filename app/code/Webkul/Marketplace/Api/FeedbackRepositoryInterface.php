<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_Marketplace
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Api;

/**
 * FeedbackRepository CRUD Interface
 */
interface FeedbackRepositoryInterface
{
    /**
     * Retrieve feedback by id.
     *
     * @param int $id
     * @return \Webkul\Marketplace\Model\Feedback
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Save Feedback.
     *
     * @param \Webkul\Marketplace\Model\Feedback $subject
     * @return \Webkul\Marketplace\Model\Feedback
     */
    public function save(\Webkul\Marketplace\Model\Feedback $subject);

    /**
     * Retrieve all feedbacks.
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $creteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $creteria);

    /**
     * Delete feedback.
     *
     * @param \Webkul\Marketplace\Model\Feedback $subject
     * @return boolean
     */
    public function delete(\Webkul\Marketplace\Model\Feedback $subject);

    /**
     * Delete feedback by id.
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
