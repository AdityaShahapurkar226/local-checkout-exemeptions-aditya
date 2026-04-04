<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMassUpload\Api;

/**
 * MpMassUpload Profile CRUD interface.
 */
interface ProfilerRepositoryInterface
{
    /**
     * Retrieve MpMassUpload Profile Data By seller id.
     *
     * @api
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResults
     */
    public function getBySeller(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Run Profile.
     *
     * @param \Webkul\MpMassUpload\Api\Data\ProfilerDataInterface $profileData
     * @return \Webkul\MpMassUpload\Api\Data\ProfilerDataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function runProfile(\Webkul\MpMassUpload\Api\Data\ProfilerDataInterface $profileData);

    /**
     * Delete Profiler
     *
     * @param string $profilerIds
     * @return void
     */
    public function deleteProfiler($profilerIds);
}
