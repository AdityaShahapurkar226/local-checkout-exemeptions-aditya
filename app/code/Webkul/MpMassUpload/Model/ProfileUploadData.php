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
use \Webkul\MpMassUpload\Api\Data\ProfileUploadDataInterface;
use \Magento\Framework\Model\AbstractModel;

/**
 * ProfileUploadData Class Getter Setter
 */
class ProfileUploadData extends AbstractModel implements IdentityInterface, ProfileUploadDataInterface
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
     * @param string $massuploadCsv
     * @return Webkul\MpMassUpload\Api\Data\ProfileUploadDataInterface
     */
    public function setMassuploadCsv($massuploadCsv)
    {
        return $this->setData(self::MASSUPLOAD_CSV, $massuploadCsv);
    }

    /**
     * Get File
     *
     * @return string
     */
    public function getMassuploadCsv()
    {
        return parent::getData(self::MASSUPLOAD_CSV);
    }
    /**
     * Set Attribute set
     *
     * @param string $attributeSet
     * @return \Webkul\MpMassUpload\Api\Data\ProfileUploadDataInterface
     */
    public function setAttributeSet($attributeSet)
    {
        return $this->setData(self::ATTRIBUTE_SET, $attributeSet);
    }

    /**
     * Get Attributeset
     *
     * @return string
     */
    public function getAttributeSet()
    {
        return parent::getData(self::ATTRIBUTE_SET);
    }
    
    /**
     * Set AttributeProfileId
     *
     * @param int $attributeProfileId
     * @return \Webkul\MpMassUpload\Api\Data\ProfileUploadDataInterface
     */
    public function setAttributeProfileId($attributeProfileId = null)
    {
        return $this->setData(self::ATTRIBUTE_PROFILE_ID, $attributeProfileId);
    }

    /**
     * Get Attribute Profile Id
     *
     * @return int
     */
    public function getAttributeProfileId()
    {
        return parent::getData(self::ATTRIBUTE_PROFILE_ID);
    }

    /**
     * Set Massupload Image
     *
     * @param string $massuploadImage
     * @return \Webkul\MpMassUpload\Api\Data\ProfileUploadDataInterface
     */
    public function setMassuploadImage($massuploadImage = null)
    {
        return $this->setData(self::MASSUPLOAD_IMAGE, $massuploadImage);
    }

    /**
     * Get Massupload Image
     *
     * @return string
     */
    public function getMassuploadImage()
    {
        return parent::getData(self::MASSUPLOAD_IMAGE);
    }
    
    /**
     * Set Downloadable
     *
     * @param int $downloadable
     * @return \Webkul\MpMassUpload\Api\Data\ProfileUploadDataInterface
     */
    public function setDownloadable($downloadable = null)
    {
        return $this->setData(self::DOWNLOADABLE, $downloadable);
    }

    /**
     * Get Downloadable
     *
     * @return int
     */
    public function getDownloadable()
    {
        return parent::getData(self::DOWNLOADABLE);
    }

    /**
     * Set link Files
     *
     * @param string $linkFiles
     * @return \Webkul\MpMassUpload\Api\Data\ProfileUploadDataInterface
     */
    public function setLinkFiles($linkFiles = null)
    {
        return $this->setData(self::LINK_FILES, $linkFiles);
    }

    /**
     * Get link files
     *
     * @return string
     */
    public function getLinkFiles()
    {
        return parent::getData(self::LINK_FILES);
    }
    
    /**
     * Set is Link Samples
     *
     * @param int $isLinkSamples
     * @return \Webkul\MpMassUpload\Api\Data\ProfileUploadDataInterface
     */
    public function setIsLinkSamples($isLinkSamples = null)
    {
        return $this->setData(self::IS_LINK_SAMPLES, $isLinkSamples);
    }

   /**
    * Get is Link Samples
    *
    * @return int
    */
    public function getIsLinkSamples()
    {
        return parent::getData(self::IS_LINK_SAMPLES);
    }
   
   /**
    * Set LinkSampleFiles
    *
    * @param string $linkSampleFiles
    * @return \Webkul\MpMassUpload\Api\Data\ProfileUploadDataInterface
    */
    public function setLinkSampleFiles($linkSampleFiles = null)
    {
        return $this->setData(self::LINK_SAMPLE_FILES, $linkSampleFiles);
    }

    /**
     * Get LinkSampleFiles
     *
     * @return string
     */
    public function getLinkSampleFiles()
    {
        return parent::getData(self::LINK_SAMPLE_FILES);
    }

    /**
     * Set is Samples
     *
     * @param int $isSamples
     * @return \Webkul\MpMassUpload\Api\Data\ProfileUploadDataInterface
     */
    public function setIsSamples($isSamples = null)
    {
        return $this->setData(self::IS_SAMPLE, $isSamples);
    }

   /**
    * Get is Samples
    *
    * @return int
    */
    public function getIsSamples()
    {
        return parent::getData(self::IS_SAMPLE);
    }
   
   /**
    * Set Sample Files
    *
    * @param string $sampleFiles
    * @return \Webkul\MpMassUpload\Api\Data\ProfileUploadDataInterface
    */
    public function setSampleFiles($sampleFiles = null)
    {
        return $this->setData(self::SAMPLE_FILES, $sampleFiles);
    }

    /**
     * Get Sample Files
     *
     * @return string
     */
    public function getSampleFiles()
    {
        return parent::getData(self::SAMPLE_FILES);
    }
}
