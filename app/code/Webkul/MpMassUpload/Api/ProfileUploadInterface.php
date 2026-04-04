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
 * ProfileUploadInterface CRUD Interface
 */
interface ProfileUploadInterface
{
    
    /**
     *  Save File Upload
     *
     * @param \Webkul\MpMassUpload\Api\Data\ProfileUploadDataInterface $profileData
     * @return \Webkul\MpMassUpload\Api\Data\ProfileUploadDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveProfile(\Webkul\MpMassUpload\Api\Data\ProfileUploadDataInterface $profileData);
}
