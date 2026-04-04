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
 * FileUploaderInterface CRUD Interface
 */
interface FileUploaderInterface
{
    
    /**
     *  Save File Upload
     *
     * @param \Webkul\MpMassUpload\Api\Data\FileUploaderRequestInterface $fileData
     * @return \Webkul\MpMassUpload\Api\Data\FileUploaderRequestInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fileUpload(\Webkul\MpMassUpload\Api\Data\FileUploaderRequestInterface $fileData);
}
