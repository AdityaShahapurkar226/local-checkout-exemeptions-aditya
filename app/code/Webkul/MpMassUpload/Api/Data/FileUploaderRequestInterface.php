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
namespace Webkul\MpMassUpload\Api\Data;

/**
 * Interface FileUploaderResponseInterface
 * @api
 * @since 100.0.2
 */
interface FileUploaderRequestInterface
{
    public const FILE = 'file';
    
    public const PATH = 'path';

    /**
     * Get File
     *
     * @return string
     */
    public function getFile();

    /**
     * Set File
     *
     * @param int $file
     * @return \Webkul\MpMassUpload\Api\Data\FileUploaderRequestInterface
     */
    public function setFile($file);

    /**
     * Get File Path
     *
     * @return string
     */
    public function getPath();

    /**
     * Set File Path
     *
     * @param string $path
     * @return \Webkul\MpMassUpload\Api\Data\FileUploaderRequestInterface
     */
    public function setPath($path);
}
