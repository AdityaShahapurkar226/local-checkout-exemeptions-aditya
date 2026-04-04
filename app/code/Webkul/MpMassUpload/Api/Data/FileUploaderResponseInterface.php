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
interface FileUploaderResponseInterface
{
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
     * @return $this
     */
    public function setFile($file);

    /**
     * Get File name
     *
     * @return string
     */
    public function getName();

    /**
     * Set File name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

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
     * @return $this
     */
    public function setPath($path);

    /**
     * Get Temp File Name
     *
     * @return string
     */
    public function getTmpName();

    /**
     * Set Temp File Name
     *
     * @param string $tmpname
     * @return $this
     */
    public function setTmpName($tmpname);

    /**
     * Get File Url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Set Temp File url
     *
     * @param string $url
     * @return $this
     */
    public function setUrl($url);
}
