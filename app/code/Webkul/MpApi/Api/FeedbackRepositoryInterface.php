<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpApi
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpApi\Api;

/**
 * FeedbackRepository CRUD Interface
 */
interface FeedbackRepositoryInterface
{
    /**
     * Retrieve feedback by id.
     *
     * @param int $id
     * @return \Webkul\MpAPi\Api\Data\FeedbackInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Save Feedback.
     *
     * @param \Webkul\MpAPi\Api\Data\FeedbackInterface $subject
     * @return \Webkul\MpAPi\Api\Data\FeedbackInterface
     */
    public function save(\Webkul\MpAPi\Api\Data\FeedbackInterface $subject);

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
     * @param \Webkul\MpAPi\Api\Data\FeedbackInterface $subject
     * @return boolean
     */
    public function delete(\Webkul\MpAPi\Api\Data\FeedbackInterface $subject);

    /**
     * Delete feedback by id.
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
