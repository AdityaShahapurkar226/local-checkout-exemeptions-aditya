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

use Webkul\MpMassUpload\Api\Data\ProfileUploadDataInterface;
use Magento\Framework\Filesystem\Io\File;
use Webkul\MpMassUpload\Api\ProfileUploadInterface;

/**
 * ProfileUpload Class Curd
 */
class ProfileUpload implements ProfileUploadInterface
{

    /**
     * @param \Magento\Framework\File\Csv $csvReader
     * @param \Webkul\MpMassUpload\Helper\Data $helper
     * @param File $fileSystemIoFile
     */
    public function __construct(
        \Magento\Framework\File\Csv $csvReader,
        \Webkul\MpMassUpload\Helper\Data $helper,
        File $fileSystemIoFile
    ) {
        $this->_csvReader = $csvReader;
        $this->helper = $helper;
        $this->_fileSystemIoFile = $fileSystemIoFile;
    }

    /**
     *  Save Profile
     *
     * @param \Webkul\MpMassUpload\Api\Data\ProfileUploadDataInterface $profileData
     * @return \Webkul\MpMassUpload\Api\Data\ProfileUploadDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveProfile(ProfileUploadDataInterface $profileData)
    {
        $fileData = $profileData->getMassuploadCsv();
        try {
            if ($fileData) {
                $fileData    = $this->helper->getJsonDecode($fileData);
                
                $csvFile        = $fileData['name'];
                $csvFilePath    = $fileData['path'];
                if ($this->helper->checkFileExists($csvFilePath.'/'.$csvFile)) {
                    $filePath       = $csvFilePath.'/'.$csvFile;
                    $csvFileData    = $this->_fileSystemIoFile->getPathInfo($csvFile);
                    $validateData   = $this->helper->validateUploadedFile(
                        $profileData,
                        $fileData,
                        $csvFileData['extension']
                    );
                    $zipData    = $this->helper->getJsonDecode($profileData->getMassuploadImage());
                    $response   = $this->profileUpload($profileData, $validateData, $filePath, $zipData);
                    
                    if ($this->helper->checkFileExists($zipData['path'].'/'.$zipData['name'])) {
                        $validateZip    = $this->validateZip($zipData['path'].'/'.$zipData['name']);
                        if ($validateZip['error']) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __($validateZip['msg'])
                            );
                        }
                    }
                    return $profileData;
                } else {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("File not exist")
                    );
                }
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * Read Csv File
     *
     * @param string $csvFilePath
     * @param string $attributeMappedArr
     * @return array
     */
    public function readCsv($csvFilePath, $attributeMappedArr)
    {
        try {
            $uploadedFileRowData = $this->_csvReader->getData($csvFilePath);
            // Start: Coverting uploaded file data attributes into magento attributes
            foreach ($uploadedFileRowData[0] as $key => $productkey) {
                if (!empty($attributeMappedArr[$productkey])) {
                    $productkey = $attributeMappedArr[$productkey];
                    $uploadedFileRowData[0][$key] = $productkey;
                }
            }
            // End: Coverting uploaded file data attributes into magento attributes
        } catch (\Exception $e) {
            $uploadedFileRowData = [];
        }
        return $uploadedFileRowData;
    }

    /**
     * Validate Zip File
     *
     * @param string $zipFilePath
     * @return array
     */
    public function validateZip($zipFilePath)
    {
        try {
            
            $allowedImages = ['png', 'jpg', 'jpeg', 'gif'];
            $zip = zip_open($zipFilePath);
            if ($zip) {
                while ($zipEntry = zip_read($zip)) {
                    $fileName = zip_entry_name($zipEntry);
                    if (strpos($fileName, '.') !== false) {
                        $ext = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
                        if (!in_array($ext, $allowedImages)) {
                            $msg = 'There are some files in zip which are not image.';
                            $result = ['error' => true, 'msg' => $msg];
                            return $result;
                        }
                    }
                }
                zip_close($zip);
            }
            
            $result = ['error' => false];
        } catch (\Exception $e) {
            $msg = 'There is some problem in uploading image zip file.';
            $result = ['error' => true, 'msg' => $msg];
        }
        return $result;
    }

    /**
     * Upload File
     *
     * @param array $profileData
     * @param array $validateData
     * @param string $filePath
     * @param array $zipData
     * @return array
     */
    public function profileUpload($profileData, $validateData, $filePath, $zipData = [])
    {
        $helper = $this->helper;
        if (!$validateData['error']) {
            $productType = $validateData['type'];
            $fileName = $validateData['csv'];
            $fileData = $validateData['csv_data'];
            $result = $this->helper->saveProfilesData(
                $productType,
                $fileName,
                $fileData,
                $validateData['extension'],
                $profileData
            );
            
            $uploadCsv = $this->helper->uploadFile($result, $validateData['extension'], $fileName, $filePath);
            
            if ($uploadCsv['error']) {
                
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($uploadCsv['msg'])
                );
            }
            $uploadZip = $this->helper->uploadedZip($result, $fileData, $zipData['path'], $zipData['name']);
            
            if ($uploadZip['error']) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($uploadZip['msg'])
                );
            }
            
            $isDownloadableAllowed = $helper->isProductTypeAllowed('downloadable');
            if ($productType == 'downloadable' && $isDownloadableAllowed) {
                $uploadLinks = $helper->uploadLinks($result, $fileData);
                if ($uploadLinks['error']) {
                    
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __($uploadLinks['msg'])
                    );
                }
                if ($profileData->getIsLinkSamples()) {
                    $uploadLinkSamples = $helper->uploadLinkSamples($result, $fileData);
                    if ($uploadLinkSamples['error']) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __($uploadLinkSamples['msg'])
                        );
                    }
                }
                if ($profileData->getIsSamples()) {
                    $uploadSamples = $helper->uploadSamples($result, $fileData);
                    if ($uploadSamples['error']) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __($uploadSamples['msg'])
                        );
                    }
                }
            }
            $message = __('Your file was uploaded and unpacked.');
            return $message;
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Something went wrong")
            );
        }
    }
}
