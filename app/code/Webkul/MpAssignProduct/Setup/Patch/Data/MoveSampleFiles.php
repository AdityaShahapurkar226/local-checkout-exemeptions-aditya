<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAssignProduct\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class MoveSampleFiles implements
    DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /** @var Reader */
    private $_reader;

    /** @var Filesystem */
    private $_filesystem;

    /** @var File */
    private $_fileDriver;

    /**
     * Initialization
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Reader $reader
     * @param Filesystem $filesSystem
     * @param File $fileDriver
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Reader $reader,
        Filesystem $filesSystem,
        File $fileDriver
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->_reader = $reader;
        $this->_filesystem = $filesSystem;
        $this->_fileDriver = $fileDriver;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->moveDirToMediaDir();
    }

    /**
     * Copy Sample CSV,XML and XSL files to Media
     */
    private function moveDirToMediaDir()
    {
        try {
            $type = \Magento\Framework\App\Filesystem\DirectoryList::MEDIA;
            $smpleFilePath = $this->_filesystem->getDirectoryRead($type)
                            ->getAbsolutePath().'marketplace/assignproduct/samples/';
            $files = [
                'sample.csv',
                'config.csv',
                'virtual.csv',
                'sample.xls',
                'config.xls',
                'virtual.xls'
            ];
            if ($this->_fileDriver->isExists($smpleFilePath)) {
                $this->_fileDriver->deleteDirectory($smpleFilePath);
            }
            if (!$this->_fileDriver->isExists($smpleFilePath)) {
                $this->_fileDriver->createDirectory($smpleFilePath, 0777);
            }
            foreach ($files as $file) {
                $filePath = $smpleFilePath.$file;
                if (!$this->_fileDriver->isExists($filePath)) {
                    $path = '/pub/media/marketplace/assignproduct/samples/'.$file;
                    $mediaFile = $this->_reader->getModuleDir('', 'Webkul_MpAssignProduct').$path;
                    if ($this->_fileDriver->isExists($mediaFile)) {
                        $this->_fileDriver->copy($mediaFile, $filePath);
                    }
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    /**
     * Get Aliases
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get dependencies
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
