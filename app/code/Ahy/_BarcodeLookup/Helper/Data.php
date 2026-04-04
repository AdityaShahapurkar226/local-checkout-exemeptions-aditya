<?php

/**
 * Copyright � Ahy Consulting All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Ahy\BarcodeLookup\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Ahy\BarcodeLookup\Helper\CreateCsvFromApiJsonFile;
use Ahy\BarcodeLookup\Helper\CreateJsonFile;
use Ahy\BarcodeLookup\Helper\CreateSqlFileFromCsv;
use Ahy\BarcodeLookup\Helper\CreateCsvForMagentoProducts;
use Magento\Framework\Filesystem\DirectoryList;
use Ahy\BarcodeLookup\Service\GetProductDetails;
use Ahy\BarcodeLookup\Logger\Logger as BarcodeLookupApiLogger;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;
    private $createCsvFile;
    private $createJsonFile;
    private $createSqlFile;
    private $createCsvForMagentoProducts;

    protected $scopeConfig;
    protected $eavConfig;
    /**
     * @var DirectoryList
     */
    private $directoryList;
    /**
     * @var GetProductDetails
     */
    private $getProductDetails;
    /**
     * @var BarcodeLookupApiLogger
     */
    private $_barcodeLookupApiLogger;

    private $_processedFolder = '/var/BarcodeLookup/Import-Process/product-catalog/processed/';

    /**
     * @param Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param CreateCsvFromApiJsonFile $createCsvFile
     * @param CreateJsonFile $createJsonFile
     * @param CreateSqlFileFromCsv $createSqlFile
     * @param CreateCsvForMagentoProducts $createCsvForMagentoProducts
     * @param DirectoryList $directoryList
     * @param BarcodeLookupApiLogger $barcodeLookupApiLogger
     * @param GetProductDetails $getProductDetails
     */
    public function __construct(
        Context $context,
        CollectionFactory $productCollectionFactory,
        CreateCsvFromApiJsonFile $createCsvFile,
        CreateJsonFile $createJsonFile,
        CreateSqlFileFromCsv $createSqlFile,
        CreateCsvForMagentoProducts $createCsvForMagentoProducts,
        DirectoryList $directoryList,
        BarcodeLookupApiLogger $barcodeLookupApiLogger,
        GetProductDetails $getProductDetails,
        ScopeConfigInterface $scopeConfig,
        EavConfig $eavConfig
    ) {
        parent::__construct($context);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->createCsvFile = $createCsvFile;
        $this->createJsonFile = $createJsonFile;
        $this->createSqlFile = $createSqlFile;
        $this->createCsvForMagentoProducts = $createCsvForMagentoProducts;
        $this->directoryList = $directoryList;
        $this->_barcodeLookupApiLogger = $barcodeLookupApiLogger;
        $this->getProductDetails = $getProductDetails;
        $this->scopeConfig = $scopeConfig;
        $this->eavConfig = $eavConfig;
    }


    /**
     * The logInfo function logs an informational message using the barcode lookup API logger.
     *
     * @param string message The `logInfo` function takes a string parameter named ``, which is
     * used as the message to be logged by the `_barcodeLookupApiLogger`.
     */
    public function logInfo(string $message): void
    {
        $this->_barcodeLookupApiLogger->info($message);
    }

    /**
     * The logError function logs an error message using the _barcodeLookupApiLogger object.
     *
     * @param string message The `logError` function takes a string parameter named ``, which
     * is the error message that you want to log. This message will be passed to the
     * `_barcodeLookupApiLogger` object's `error` method for logging purposes.
     */
    public function logError(string $message): void
    {
        $this->_barcodeLookupApiLogger->error($message);
    }

    /**
     * The function creates a SQL file from a CSV file and returns an array containing the paths to
     * both files.
     *
     * @return array An array is being returned with keys 'csv' and 'sql' containing the processed CSV
     * file and processed SQL file paths, respectively.
     */
    public function createSqlFileFromCsvFile(): array
    {
        try {
            $csvFile = $this->getProcessedCsvFromApiJsonFile();
            $sqlFile = $this->getProcessedSqlFile();
            // Check if the CSV file exists and is not empty
            if (!file_exists(filename: $csvFile)) {
                throw new \Exception(message: 'CSV file does not exist: ' . $csvFile);
            }

            if (filesize(filename: $csvFile) === 0) {
                throw new \Exception(message: 'CSV file is empty: ' . $csvFile);
            }

            $this->createSqlFile->createSqlFileFromCsv(csvFile: $csvFile, sqlFile: $sqlFile);

            return ['status' => 'success', 'message' => 'SQL file created successfully from CSV file'];
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $trace = debug_backtrace();
            $caller = $trace[0];
            $this->logError(message: 'Error in function ' . __FUNCTION__ . ' on line ' . $caller['line'] . ': ' . $errorMessage);
            return ['status' => 'error', 'message' => 'Error when creating SQL file from CSV.'];
        }
    }

    public function getProductsWithUpc(): array
    {
        try {
            $interval = $this->scopeConfig->getValue(
                'barcode_lookup/general/update_interval',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            $returnMsgArr = [];
            $returnMsgArr['status'] = 'error';
            $returnMsgArr['message'] = 'No products found with UPC Number';

            $productCollection = $this->productCollectionFactory->create();

            // $productCollection->addAttributeToSelect('*');
            $productCollection->addAttributeToSelect(['sku', 'name', 'upc_number', 'visibility', 'barcode_last_update']); // Only select needed attributes

            $productCollection->addAttributeToFilter('upc_number', ['notnull' => true]);
            $productCollection->addAttributeToFilter('upc_number', ['neq' => '']);
            $productCollection->addAttributeToFilter('type_id', ['eq' => 'simple']);
            $productCollection->addAttributeToFilter('status', ['eq' => 1]);
            $productCollection->addAttributeToFilter('price', ['lteq' => 99999]);

            // Check if 'barcode_last_update' attribute exists
            $attribute = $this->eavConfig->getAttribute(Product::ENTITY, 'barcode_last_update');

            if ($attribute && $attribute->getId()) {
                $interval = is_numeric($interval) ? (int) $interval : 30;
                $cutoffDate = (new \DateTime())->modify("-{$interval} days")->format('Y-m-d H:i:s');

                $productCollection->addAttributeToFilter(
                    [
                        ['attribute' => 'barcode_last_update', 'null' => true], // case 1: not set
                        ['attribute' => 'barcode_last_update', 'lt' => $cutoffDate] // case 2: older than 30 days
                    ]
                );
            }

            $collection = $productCollection->getItems(); // Get the collection of products from the collection factory

            // Convert collection to array
            $collection = array_map(callback: function ($product): mixed {
                return $product->getData();
            }, array: $collection);

            // If products are found, update status
            if (count($collection) > 0) {
                $returnMsgArr['status'] = 'success';
                $returnMsgArr['message'] = count($collection) . ' products met our Barcode implementation conditions and valid delta!';
            } else {
                $returnMsgArr['status'] = 'No error';
            }

            $returnMsgArr[] = $this->createCsvForMagentoProducts(collection: $collection);
            return $returnMsgArr;  // Return the array so we can process or print later.
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $trace = debug_backtrace();
            $caller = $trace[0];
            $this->logError(message: 'Error in function ' . __FUNCTION__ . ' on line ' . $caller['line'] . ': ' . $errorMessage);
            return ['status' => 'error', 'message' => 'Error fetching products with UPC Number'];
        }
    }

    public function getUpcsFromCsv(): array
    {
        try {
            $csvFile = $this->getMagentoProductsCsvFile();
            $upcsArr = [];

            if (($handle = fopen(filename: $csvFile, mode: 'r')) !== false) {
                // Skip the header row
                fgetcsv(stream: $handle);

                /* // Read the rest of the rows
                while (($data = fgetcsv(stream: $handle)) !== false) {
                    // Assuming the UPC is in the fourth column (index 3)
                    if (isset($data[3]) && !empty($data[3])) { // Check if the UPC is not empty
                        $upcsArr[] = $data[3]; // Add the UPC to the array
                    }
                } */
                
                // 19th May: Even though this is a waste of making API request (as anyway the product with UPC trailing spaces isn't going to be updated in DB), this will avoid conflicting with other UPCs in the list when making API request.

                // Read the rest of the rows
                while (($data = fgetcsv(stream: $handle)) !== false) {
                    $upc = trim(str_replace("\u{00A0}", '', $data[3]));

                    // Assuming the UPC is in the fourth column (index 3)
                    if (isset($data[3])) {
                        $upc = trim(str_replace("\u{00A0}", '', $data[3]));  // explicitly replace non-breaking spaces first, then trim.

                        if (!empty($upc)) {
                            $upcsArr[] = $upc;  // Add the UPC to the array
                        }
                    }
                }

                fclose(stream: $handle);
            }
            
            // Remove duplicates and sort the array
            // $upcsArr = array_unique(array: $upcsArr); // Remove duplicates
            // sort(array: $upcsArr);

            // Split the UPCsArr array into chunks of 10
            $upcsChunks = array_chunk(array: $upcsArr, length: 10); // Get the UPCsArr array in chunks of 10 chunks separated by commas between each chunk of the UPCsArr array

            // Convert each chunk to a comma-separated string
            $upcsChunks = array_map(callback: function ($chunk): string {
                return implode(separator: ',', array: $chunk);
            }, array: $upcsChunks); // array keys are preserved as they are returned from array_map function call to avoid duplicates and performance issues with arrays with large number of elements returned from array_map

            return $upcsChunks;
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $trace = debug_backtrace();
            $caller = $trace[0];
            $this->logError('Error in function ' . __FUNCTION__ . ' on line ' . $caller['line'] . ': ' . $errorMessage);
            return [];
        }
    }


    public function createJsonFileFromCsvFile(): array
    {
        try {
            $upcs = $this->getUpcsFromCsv();
            $responseContent = [
                'response' => [],
                'error' => [],
                'message' => ''
            ];
            $loopStartTime = microtime(as_float: true);

            foreach ($upcs as $index => $upc) {
                $startTime = microtime(as_float: true);
                $startTimeReadable = date(format: 'Y-m-d H:i:s', timestamp: (int)$startTime);

                // Log the start time of the API request
                $this->logInfo(message: (string) 'API request start time: ' . $startTimeReadable);
                // Log the current UPC
                $this->logInfo(message: (string) 'Current UPC Number: ' . $upc);
                // Log the request index
                $this->logInfo(message: (string) 'Request index: ' . $index);

                // Make API request to get product data for each UPC
                $apiResponse = $this->getProductDetails->getProductData(upcs: $upc);
                $status = $apiResponse['status'];

                // Handle different status codes
                switch ($status) {
                    case 200:
                        // Successful response
                        $responseContent['response'][] = $apiResponse['response'];
                        break;
                    case 403:
                        $responseContent['error'][] = 'Error: Unauthorized';
                        $responseContent['message'] = 'Message: Invalid API key';
                        break;
                    case 404:
                        $responseContent['error'][] = 'Error: Not Found';
                        $responseContent['message'] = 'Message: No data returned';
                        break;
                    case 429:
                        $responseContent['error'][] = 'Error: Too Many Requests';
                        $responseContent['message'] = 'Message: Exceeded API call limits';
                        break;
                    case 500:
                        $responseContent['error'][] = 'Error: Internal Server Error';
                        $responseContent['message'] = 'Message: API server error';
                        break;
                    default:
                        $responseContent['error'][] = 'Error: Unknown';
                        $responseContent['message'] = 'Message: An unexpected error occurred';
                        break;
                }

                // Sleep for 2 second after every 10 requests
                if ($index % 10 === 0 && $index !== 0) {
                    sleep(seconds: 2);
                }

                // Log the API request status
                $this->logInfo(message: (string) 'API request status: ' . $status);
                // Log the end time of the API response
                $this->logInfo(message: (string) 'API response end time: ' . date(format: 'Y-m-d H:i:s', timestamp: (int)microtime(as_float: true)));
                // Log the total time taken for the API request
                $this->logInfo(message: (string) 'API request total time: ' . (microtime(as_float: true) - $startTime) . ' seconds');
            }

            // Log the total number of API requests made
            $this->logInfo(message: (string) 'Total number of API requests made: ' . count(value: $upcs));

            // Log the total number of API responses received
            $this->logInfo(message: (string) 'Total number of API responses received: ' . count(value: $responseContent['response']));

            // Log the total number of API responses with errors
            $this->logInfo(message: (string) 'Total number of API responses with errors: ' . count(value: $responseContent['error']));
            if (!empty($responseContent['response'])) {
                // Create JSON file
                $jsonFile = $this->getProcessedApiJsonFile();
                $this->createJsonFile->createJsonFile(response: $responseContent['response'], filepath: $jsonFile);
            } else {
                $this->logError(message: 'No response content to create JSON file');
            }

            // Log the total time taken for the JSON file creation
            $loopEndTime = microtime(as_float: true);
            $loopTotalTime = $loopEndTime - $loopStartTime;

            // Log the total time taken for the API request loop
            $this->logInfo(message: (string) 'API request loop total time: ' . $loopTotalTime . ' seconds');

            return $responseContent;
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $trace = debug_backtrace();
            $caller = $trace[0];
            $this->logError(message: 'Error in function ' . __FUNCTION__ . ' on line ' . $caller['line'] . ': ' . $errorMessage);
            return ['status' => 'error', 'message' => 'Error creating JSON from CSV file'];
        }
    }

    public function createCsvFileFromJsonFile(): array
    {
        try {
            $jsonFile = $this->getProcessedApiJsonFile();
            $csvFile = $this->getProcessedCsvFromApiJsonFile();

            // Check if the JSON file exists and is not empty
            if (!file_exists(filename: $jsonFile)) {
                throw new \Exception('JSON file does not exist: ' . $jsonFile);
            }

            if (filesize(filename: $jsonFile) === 0) {
                throw new \Exception('JSON file is empty: ' . $jsonFile);
            }

            return $this->createCsvFile->createCsvFromApiJsonFile(jsonFile: $jsonFile, csvFile: $csvFile);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $trace = debug_backtrace();
            $caller = $trace[0];
            $this->logError(message: 'Error in function ' . __FUNCTION__ . ' on line ' . $caller['line'] . ': ' . $errorMessage);

            return ['status' => 'error', 'message' => 'Error creating CSV from JSON file: ' . $errorMessage];
        }
    }

    public function createCsvForMagentoProducts($collection): array
    {
        try {
            $products = $collection;
            $csvFile = $this->getMagentoProductsCsvFile();

            $this->createCsvForMagentoProducts->createCsvForMagentoProducts($products, $csvFile);

            return ['csv' => $csvFile];
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $trace = debug_backtrace();
            $caller = $trace[0];
            $this->logError(message: 'Error in function ' . __FUNCTION__ . ' on line ' . $caller['line'] . ': ' . $errorMessage);
            return ['status' => 'error', 'message' => 'Error creating CSV for Magento Products. Check Logs for more information. ' . $errorMessage];
        }
    }

    public function getMagentoProductsCsvFile(): string
    {
        try {
            return $this->getBasePath() . $this->_processedFolder . date(format: 'Y-m-d') . '/magento-upc-products' . '.csv';
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $trace = debug_backtrace();
            $caller = $trace[0];
            $this->logError(message: 'Error in function ' . __FUNCTION__ . ' on line ' . $caller['line'] . ': ' . $errorMessage);
            return '';
        }
    }

    public function getProcessedApiJsonFile(): string
    {
        try {
            return $this->getBasePath() . $this->_processedFolder . date(format: 'Y-m-d') . '/api-products-response' . '.json';
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $trace = debug_backtrace();
            $caller = $trace[0];
            $this->logError(message: 'Error in function ' . __FUNCTION__ . ' on line ' . $caller['line'] . ': ' . $errorMessage);
            return '';
        }
    }

    public function getProcessedCsvFromApiJsonFile(): string
    {
        try {
            return $this->getBasePath() . $this->_processedFolder . date(format: 'Y-m-d') . '/api-products-data' . '.csv';
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $trace = debug_backtrace();
            $caller = $trace[0];
            $this->logError(message: 'Error in function ' . __FUNCTION__ . ' on line ' . $caller['line'] . ': ' . $errorMessage);
            return '';
        }
    }

    public function getProcessedJsonFile(): string
    {
        try {
            return $this->getBasePath() . $this->_processedFolder . date(format: 'Y-m-d') . '/product-catalog' . '.json';
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $trace = debug_backtrace();
            $caller = $trace[0];
            $this->logError(message: 'Error in function ' . __FUNCTION__ . ' on line ' . $caller['line'] . ': ' . $errorMessage);
            return '';
        }
    }

    public function getProcessedCsvFile(): string
    {
        try {
            return $this->getBasePath() . $this->_processedFolder . date(format: 'Y-m-d') . '/product-catalog' . '.csv';
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $trace = debug_backtrace();
            $caller = $trace[0];
            $this->logError(message: 'Error in function ' . __FUNCTION__ . ' on line ' . $caller['line'] . ': ' . $errorMessage);
            return '';
        }
    }

    public function getProcessedSqlFile(): string
    {
        try {
            return $this->getBasePath() . $this->_processedFolder . date(format: 'Y-m-d') . '/update-magento-catalog-products' . '.sql';
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $trace = debug_backtrace();
            $caller = $trace[0];
            $this->logError(message: 'Error in function ' . __FUNCTION__ . ' on line ' . $caller['line'] . ': ' . $errorMessage);
            return '';
        }
    }

    protected function getBasePath(): string
    {
        try {
            return $this->directoryList->getRoot();
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $trace = debug_backtrace();
            $caller = $trace[0];
            $this->logError(message: 'Error in function ' . __FUNCTION__ . ' on line ' . $caller['line'] . ': ' . $errorMessage);
            return '';
        }
    }
}
