<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class MoveMediaFile implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param \Magento\Framework\Filesystem\Io\File $file
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Framework\Module\Dir\Reader $reader
    ) {
        $this->filesystem = $filesystem;
        $this->file = $file;
        $this->reader = $reader;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->processDefaultImages();
    }

    /**
     * Copy Banner and Icon Images to Media
     */
    private function processDefaultImages()
    {
        $error = false;
        try {
            $this->createDirectory();
            $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
            $ds = "/";
            $baseModulePath = $this->reader->getModuleDir('', 'Webkul_MpAssignProduct');
            $mediaDirectory = "marketplace/assignproduct/product";
            $image = "noimage.jpg";
            $modulePath = "pub/media/marketplace/assignproduct/product";
            $path = $directory->getAbsolutePath($mediaDirectory);
            $mediaFilePath = $path.$ds.$image;
            $moduleFilePath = $baseModulePath.$ds.$modulePath.$ds.$image;
            if ($this->file->fileExists($mediaFilePath)) {
                $error = true;
            }
            if (!$this->file->fileExists($moduleFilePath)) {
                $error = true;
            }
            if (!$error) {
                $this->file->cp($moduleFilePath, $mediaFilePath);
            }
        } catch (\Exception $e) {
            $error = true;
        }
    }

    /**
     * Create default directorie
     */
    private function createDirectory()
    {
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $path = $directory->getAbsolutePath('marketplace/assignproduct/product');
        if (!$this->file->fileExists($path)) {
            $this->file->mkdir($path, 0777, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}
