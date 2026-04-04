<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpMassUpload
 * @author Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\MpMassUpload\Model;

use \Magento\Framework\DataObject\IdentityInterface;
use \Webkul\MpMassUpload\Api\Data\FileUploaderRequestInterface;
use \Magento\Framework\Model\AbstractModel;

/**
 * FileUploaderRequest Class Getter Setter
 */
class FileUploaderRequest extends AbstractModel implements IdentityInterface, FileUploaderRequestInterface
{

    /**
     * Load No-Route Indexer.
     *
     * @return $this
     */
    public function noRouteReasons()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return []
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }
    
    /**
     * Set File
     *
     * @param string $file
     * @return Webkul\MpMassUpload\Api\Data\FileUploaderRequestInterface[]
     */
    public function setFile($file)
    {
        return $this->setData(self::FILE, $file);
    }

    /**
     * Get File
     *
     * @return string
     */
    public function getFile()
    {
        return parent::getData(self::FILE);
    }
    /**
     * Set File Path
     *
     * @param string $path
     * @return \Webkul\MpMassUpload\Api\Data\FileUploaderRequestInterface
     */
    public function setPath($path)
    {
        return $this->setData(self::PATH, $path);
    }

    /**
     * Get Path
     *
     * @return string
     */
    public function getPath()
    {
        return parent::getData(self::PATH);
    }
}
