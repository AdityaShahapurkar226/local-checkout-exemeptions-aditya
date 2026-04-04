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

namespace Webkul\MpMassUpload\Setup\Patch\Data;

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
class MoveMediaFiles implements
    DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
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
                            ->getAbsolutePath().'marketplace/massupload/samples/';
            $files = [
                'simple.csv',
                'downloadable.csv',
                'config.csv',
                'virtual.csv',
                'simple.xml',
                'downloadable.xml',
                'config.xml',
                'virtual.xml',
                'simple.xls',
                'downloadable.xls',
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
                    $path = '/pub/media/marketplace/massupload/samples/'.$file;
                    $mediaFile = $this->_reader->getModuleDir('', 'Webkul_MpMassUpload').$path;
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
     * Alias
     *
     * @return array
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Dependency
     *
     * @return array
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
