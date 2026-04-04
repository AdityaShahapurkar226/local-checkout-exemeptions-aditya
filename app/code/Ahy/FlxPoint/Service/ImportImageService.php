<?php

namespace Ahy\FlxPoint\Service;

use \Magento\Catalog\API\ProductRepositoryInterface as ProductRepositoryInterface ;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Ahy\FlxPoint\Logger\Logger as FlxPointApiLogger;

/**
 * Class ImportImageService
 * assign images to products by image URL
 */
class ImportImageService
{
    /**
     * Directory List
     *
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * File interface
     *
     * @var File
     */
    protected $file;

    /**
     * @var ProductRepositoryInterface 
     */
    protected $_productRepository;

    /**
     * @var Product 
     */
    protected $_product;

    /**
     * @var FlxPointApiLogger
     */
    private $_flxPointApiLogger;


    /**
     * ImportImageService constructor
     *
     * @param DirectoryList              $directoryList
     * @param File                       $file
     * @param ProductRepositoryInterface $productRepository
     * @param Product                    $product
     * @param FlxPointApiLogger                $flxPointApiLogger
     */
    public function __construct(
        DirectoryList               $directoryList,
        File                        $file,
        ProductRepositoryInterface  $productRepository,
        FlxPointApiLogger           $flxPointApiLogger,
        Product                     $product
    ) {
        $this->directoryList        = $directoryList;
        $this->file                 = $file;
        $this->_productRepository   = $productRepository;
        $this->_flxPointApiLogger   = $flxPointApiLogger;
        $this->_product             = $product;
    }

    /**
     * Execute the image import for a product.
     *
     * @param Product $product The product to import the image for.
     * @param string $imageUrl The URL of the image to import.
     * @param bool $visible (Optional) Whether the image should be visible.
     * @param array $imageType (Optional) The image type.
     *
     * @return bool True if the image import is successful, false otherwise.
     */
    public function execute($product, $imageUrl, $visible = false, $imageType = [])
    {
        try {
            $product = $this->_product->load($product->getId());
            $productRepository = $this->_productRepository;
            $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
            foreach ($existingMediaGalleryEntries as $key => $entry) {
                unset($existingMediaGalleryEntries[$key]);
            }
            $product->setMediaGalleryEntries($existingMediaGalleryEntries);
            $productRepository->save($product);
            
            $productObj = $this->_productRepository->getById($product->getId());
            $checkProductExistingImage = 'no_selection';
            if(NULL !== $productObj){
                $checkProductExistingImage = $productObj->getImage(); 
            }
                /** 
                 * $tmpDir 
                 * 
                 * @var string 
                 * */
                $tmpDir = $this->getMediaDirTmpDir();

                /** 
                 * create folder if it is not exists 
                 */
                $this->file->checkAndCreateFolder($tmpDir);

                $fileName = $tmpDir . baseName($imageUrl) . '.png';
                $fn = str_replace('(', '_', $fileName);
                $fn1 = str_replace(')', '_', $fn);

                /** 
                 * $newFileName 
                 * 
                 * @var string 
                 */
                $newFileName = $fn1;
                /** 
                 * read file from URL and copy it to the new destination 
                 */
                $result = $this->file->read($imageUrl, $newFileName);    
                
                if ($result) {
                    $product->addImageToMediaGallery($newFileName, $imageType, true, !$visible);
                    $productRepository->save($product);
                }
            
            return $result;
        }catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $errorFile      = $e->getFile();
            $errorLine      = $e->getLine();
            $productId      = $product->getId();

            // Log the error message with additional information
            $errorLogMessage = "Error in Ahy\FlxPoint\Service\ImportImageService for product ID: $productId. Error Message: $errorMessage. Error in $errorFile on line $errorLine.";
            $this->_flxPointApiLogger->info($errorLogMessage);
        }
    }

    protected function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp/';
    }
}