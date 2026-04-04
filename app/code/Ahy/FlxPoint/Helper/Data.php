<?php
/**
 * Copyright © Ahy Consulting All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Ahy\FlxPoint\Helper;

use Ahy\FlxPoint\Helper\CreateCsvFile;
use Ahy\FlxPoint\Helper\CreateJsonFile;
use Ahy\FlxPoint\Helper\CreateSqlFile;
use Ahy\FlxPoint\Logger\Logger as FlxPointApiLogger;
use Ahy\FlxPoint\Service\GetProductDetails;
use Ahy\FlxPoint\Service\GetVendorDetails;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\State;

class Data extends AbstractHelper
{
    protected $_getVendorDetails;

    protected $_getProductDetails;

    protected $resourceConnection;

    private State $appState;

    /** 
     * @var string
     * This is the path to the folder where the spot price json file will be stored. 
     */
    private $_vendorStaticPath          = '/flxPoint/vendor/';
    /** 
     * @var string
     * This is the path to the folder where the spot price json file will be stored. 
     */
    private $_productStaticPath         = '/flxPoint/Import-Process/product-catalog/pending/';

    /**
     * @var string
     * this is the name of the file that will be created in the folder. 
     */
    private $_productParentJsonFileName = 'productInventoryParent.json';
    /**
     * @var string
     * this is the name of the file that will be created in the folder. 
     */
    private $_productParentCsvFileName  = 'flxpoint_product_custom_import.csv';
    // private $_productParentJsonFileName     = 'productParent.json';
    /**
     * @var string
     * this is the name of the file that will be created in the folder. 
     */
    private $_vendorJsonFileName        = 'vendors-list.json';
    
    /**
     * @var string
     * this is the name of the file that will be created in the folder. 
     */
    private $_productImageCsvFileName   = 'flxpoint_product_custom_import.csv';

    /**
     * @var DirectoryList
     */
    protected $_magentoRootDir;

    /**
     *
     * @var CreateJsonFile
     */
    protected $_createJsonFile;
    
    /**
     *
     * @var CreateCsvFile
     */
    protected $_createCsvFile;
    
    /**
     *
     * @var CreateSqlFile
     */
    protected $_createSqlFile;
    
    /**
     * @var FlxPointApiLogger
     */
    private $_flxPointApiLogger;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param FlxPointApiLogger flxPointApiLogger is the class that will be used to log the API calls.
     */

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        GetVendorDetails $getVendorDetails,
        GetProductDetails $getProductDetails,
        DirectoryList $magentoDir,
        FlxPointApiLogger $flxPointApiLogger,
        CreateJsonFile $createJsonFile,
        CreateSqlFile $createSqlFile,
        CreateCsvFile $createCsvFile,
        ResourceConnection $resourceConnection,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        State $appState // Injecting App State
    ) {
        parent::__construct($context);
        $this->_getProductDetails = $getProductDetails;
        $this->_getVendorDetails = $getVendorDetails;
        $this->_flxPointApiLogger = $flxPointApiLogger;
        $this->_createJsonFile = $createJsonFile;
        $this->_createSqlFile = $createSqlFile;
        $this->_createCsvFile = $createCsvFile;
        $this->_magentoRootDir = $magentoDir;
        $this->resourceConnection = $resourceConnection;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->appState = $appState; // Assigning appState
    }

    /**
     * Get the Magento root directory
     */
    public function getMagentoDir($magentoDirPath): string
    {
        $path = $this->_magentoRootDir->getPath($magentoDirPath);
        return $path;
    }

    /**
     * Get the folder path for stored files
     */
    public function getStoredFileFoldersDirectory($magentoDirPath, $folderPath): string
    {
        $folderPath = $this->getMagentoDir(magentoDirPath: $magentoDirPath) . $folderPath;
        return $folderPath;
    }

    /**
     * Fetch vendor details from the API and store them in a JSON file
     */
    public function getVendorDetails(): array
    {
        $returnMsgArr = [];
        $folderPath = $this->getStoredFileFoldersDirectory(magentoDirPath: 'var', folderPath: $this->_vendorStaticPath);
        $filePath = (string) $folderPath . $this->_vendorJsonFileName;
        try {
            $vendorApiResponseData = $this->_getVendorDetails->getVendor();
            if ($vendorApiResponseData == 500) {
                $this->logError(message: 'Vendor API response error: 500');
                $returnMsgArr[] = '<info> 500 response error <info>';
                // $this->notifyAdmin(subject: 'Vendor API Error', message: 'There was an error while fetching vendor data.');
            } else {
                $returnMsgArr[] = '<info>Data received successfully from the FlxPoint API<info>';
                $returnMsgArr[] = $this->_createJsonFile->createJsonFile($folderPath, $filePath, $vendorApiResponseData);
            }
        } catch (\Exception $e) {
            $this->logError(message: 'Error while creating vendor list file: ' . $e->getMessage());
            // $this->notifyAdmin(subject: 'Vendor Data Error', message: 'There was an exception: ' . $e->getMessage());
        }
        return $returnMsgArr;
    }

    /**
     * Fetch product parent details in batches and handle pagination with rate limits
     */
    public function getProductParentsDetails(): array
    {
        $returnMsgArr = [];
        $folderPath = $this->getStoredFileFoldersDirectory(magentoDirPath: 'var', folderPath: $this->_productStaticPath);
        $filePath = (string) $folderPath . $this->_productParentJsonFileName;
        $pageNumber = 1;
        $lastUpdatedAt = $this->getLastUpdateAt();
        $sellerIdsArr   = [
            // 991989, //Outsiders
            // 994891, //Hot Bento
            // 994150, //Grazly
            //994017, //Echo Water
            // 993548, //K9 Sport Sack
            // 993175, //MODL Outdoors
            //993547, //Coyote Eyewear
            //994262, //Buck Wipes
            //993916, //Ice Barrel
            //993820, //Maniac Outdoors
            //993626, //Malo'o
            //993628, //Blue Ribbon Nets
            //992730, //Fav Fishing
            //993627, //Patriot Coolers
            //993458, //DSG Outerwear
            //985348, //Sasquatch Tea Company
            //992751, //GrandeBass
            //992254, //Dark Energy
            //992750, //Simtek
            //992255, //MonsterBass
            //976092, //Forloh
            //988904, //Evolution Outdoor
            //991821, //Caddis sports
            //975921  //Grill Your Ass Off
        ];

        try {
            $responseData = '';
            foreach ($sellerIdsArr as $sellerId) {
                $continueLoop = true;
                do {
                    $productApiResponseData = $this->_getProductDetails->getInventoryParentAndVariants($pageNumber, $lastUpdatedAt, $sellerId);

                    if ($productApiResponseData == 500) {
                        $this->logError(message: "Product API response error for Seller ID $sellerId on page $pageNumber");
                        // $this->notifyAdmin(subject: 'Product API Error', message: "Error fetching data for Seller ID $sellerId on page $pageNumber.");
                        break;
                    }

                    if ($productApiResponseData !== '[]') {
                        $returnMsgArr[] = "<info>Data received successfully for Seller ID: $sellerId and page number: $pageNumber<info>";
                        $responseData .= $productApiResponseData;
                        $pageNumber++;
                    } else {
                        $continueLoop = false;
                        $pageNumber = 1; // Reset for next seller
                    }

                    // Sleep for 1 second to avoid rate limits
                    sleep(seconds: 1);

                } while ($continueLoop);
            }
            $returnMsgArr[] = $this->_createJsonFile->createJsonFile(folderPath: $folderPath, filePath: $filePath, responseData: $responseData);
            // $this->notifyAdmin(subject: 'Product Data success', message: 'Success: true ' );
        } catch (\Exception $e) {
            $this->logError(message: 'Error while creating parent product file: ' . $e->getMessage());
            // $this->notifyAdmin(subject: 'Product Data Error', message: 'Exception: ' . $e->getMessage());
        }
        return $returnMsgArr;
    }

    /**
     * Log errors using the FlxPoint API Logger
     */
    private function logError(string $message): void
    {
        $this->_flxPointApiLogger->error($message);
    }

    /**
     * Notify the admin via email in case of critical failures
     */
    // private function notifyAdmin(string $subject, string $message): void
    // {
    //     try {
    //          // Set area code if not set
    //         if (!$this->appState->getAreaCode()) {
    //             $this->appState->setAreaCode('frontend');
    //         }

    //         $storeId = $this->storeManager->getStore()->getId();
    //         $transport = $this->transportBuilder
    //             ->setTemplateIdentifier('admin_error_notification') // Template in email_templates.xml
    //             ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
    //             ->setTemplateVars(['message' => $message])
    //             ->setFrom('general')
    //             ->addTo('afzal.sayed04@gmail.com') // Admin email
    //             ->getTransport();
    //         $transport->sendMessage();
    //         $this->logError(message: 'sending admin notification: true');
    //     } catch (\Exception $e) {
    //         $this->logError(message: 'Error while sending admin notification: ' . $e->getMessage());
    //     }
    // }

    
    public function createSqlFileFromCsvFile(): array
    {
        $returnMsgArr   = [];
        $returnMsgArr[] = $this->createCsvFileFromJsonFile();
        $folderPath     = $this->getStoredFileFoldersDirectory(magentoDirPath: 'var', folderPath: $this->_productStaticPath);
        $filePath       = (string) $folderPath . $this->_productImageCsvFileName;
        $returnMsgArr   = $this->_createSqlFile->createSqlFile(folderPath: $folderPath, filePath: $filePath);

        return $returnMsgArr;
    }

    public function createCsvFileFromJsonFile(): array
    {
        $returnMsgArr   = [];
        $folderPath     = $this->getStoredFileFoldersDirectory(magentoDirPath: 'var', folderPath: $this->_productStaticPath);
        $csvFilePath    = (string) $folderPath . $this->_productParentCsvFileName;
        $jsonFilePath   = (string) $folderPath . $this->_productParentJsonFileName;
        $lastUpdatedAt  = $this->getLastUpdateAt();
        $returnMsgArr[] = $this->_createCsvFile->createCsvFile(folderPath: $folderPath, filePath: $csvFilePath, jsonFilePath: $jsonFilePath, lastUpdatedAt: $lastUpdatedAt);

        return $returnMsgArr;
    }

    public function getLastUpdateAt(): mixed
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('flxpoint_delta');
        $select = $connection->select()->from($tableName, ['last_update_at'])->order('entity_id DESC')->limit(1);
        try {
            return $connection->fetchOne($select);
        } catch (\Exception $e) {
            $this->logError(message: 'Error while fetching last update: ' . $e->getMessage());
            return null;
        }
    }
}
