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
 * Interface ProfileUploadDataInterface
 * @api
 * @since 100.0.2
 */
interface ProfileUploadDataInterface
{
    public const ATTRIBUTE_SET          = 'attribute_set';

    public const MASSUPLOAD_CSV         = 'massupload_csv';

    public const ATTRIBUTE_PROFILE_ID   = 'attribute_profile_id';

    public const MASSUPLOAD_IMAGE       = 'massupload_image';

    public const DOWNLOADABLE           = 'downloadable';
    
    public const LINK_FILES             = 'link_files'; /* dependent downloadable */
    
    public const IS_LINK_SAMPLES        = 'is_link_samples';
    
    public const LINK_SAMPLE_FILES      = 'link_sample_files'; /* dependent is_link_samples */

    public const IS_SAMPLE              = 'is_samples';
    
    public const SAMPLE_FILES           = 'sample_files'; /* dependent is_samples */

    /**
     * Get Attribute
     *
     * @return string
     */
    public function getAttributeSet();

    /**
     * Set Attribute Set
     *
     * @param int $attributeSet
     * @return $this
     */
    public function setAttributeSet($attributeSet);

    /**
     * Get File
     *
     * @return string
     */
    public function getMassuploadCsv();

    /**
     * Set File
     *
     * @param string $massuploadCsv
     * @return $this
     */
    public function setMassuploadCsv($massuploadCsv);

    /**
     * Set AttributeProfileId
     *
     * @param int $attributeProfileId
     * @return $this
     */
    public function setAttributeProfileId($attributeProfileId = null);

    /**
     * Get Attribute Profile Id
     *
     * @return int
     */
    public function getAttributeProfileId();

    /**
     * Set Massupload Image
     *
     * @param string $massuploadImage
     * @return $this
     */
    public function setMassuploadImage($massuploadImage = null);

    /**
     * Get Massupload Image
     *
     * @return string
     */
    public function getMassuploadImage();
    
    /**
     * Set Downloadable
     *
     * @param int $downloadable
     * @return $this
     */
    public function setDownloadable($downloadable = null);

    /**
     * Get Downloadable
     *
     * @return int
     */
    public function getDownloadable();

    /**
     * Set link Files
     *
     * @param string $linkFiles
     * @return $this
     */
    public function setLinkFiles($linkFiles = null);

    /**
     * Get link files
     *
     * @return string
     */
    public function getLinkFiles();
    
    /**
     * Set is Link Samples
     *
     * @param int $isLinkSamples
     * @return $this
     */
    public function setIsLinkSamples($isLinkSamples = null);

   /**
    * Get is Link Samples
    *
    * @return int
    */
    public function getIsLinkSamples();
   
   /**
    * Set LinkSampleFiles
    *
    * @param string $linkSampleFiles
    * @return $this
    */
    public function setLinkSampleFiles($linkSampleFiles = null);

    /**
     * Get LinkSampleFiles
     *
     * @return string
     */
    public function getLinkSampleFiles();

    /**
     * Set is Samples
     *
     * @param int $isSamples
     * @return $this
     */
    public function setIsSamples($isSamples = null);

   /**
    * Get is Samples
    *
    * @return int
    */
    public function getIsSamples();
   
   /**
    * Set Sample Files
    *
    * @param string $sampleFiles
    * @return $this
    */
    public function setSampleFiles($sampleFiles = null);

    /**
     * Get Sample Files
     *
     * @return string
     */
    public function getSampleFiles();
}
