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
namespace Webkul\MpMassUpload\Model;

use Magento\Framework\Archive;

class Zip extends Archive
{
    /**
     * Unzip Images.
     *
     * @param string $source
     * @param string $destination
     */
    public function unzipImages($source, $destination)
    {
        $zip = new \ZipArchive();
        $zip->open($source);
        $zip->extractTo($destination);
        $zip->close();
    }

    /**
     * Unzip Link Files.
     *
     * @param string $source
     * @param string $destination
     *
     * @return string
     */
    public function unzipLinks($source, $destination)
    {
        $zip = new \ZipArchive();
        $zip->open($source);
        $zip->extractTo($destination);
        $zip->close();
    }
}
