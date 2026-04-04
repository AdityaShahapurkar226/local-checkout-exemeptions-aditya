<?php

/**
 * Copyright © Ahy Consulting All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Ahy\BarcodeLookup\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Ahy\BarcodeLookup\Logger\Logger as BarcodeLookupApiLogger;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaBuilder;

use Ahy\BarcodeLookup\Config\BarcodeAttributeConfig;

class CreateCsvFromApiJsonFile extends AbstractHelper
{
    /**
     * @var BarcodeLookupApiLogger
     */
    private $_barcodeLookupApiLogger;

    /**
     * @var ProductRepositoryInterface
     */
    private $_productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param BarcodeLookupApiLogger $barcodeLookupApiLogger
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        BarcodeLookupApiLogger $barcodeLookupApiLogger,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->_barcodeLookupApiLogger = $barcodeLookupApiLogger;
        $this->_productRepository = $productRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function createCsvFromApiJsonFile(string $jsonFile, string $csvFile): array
    {
        try {
            // Check if the JSON file exists and is not empty
            if (!file_exists(filename: $jsonFile)) {
                throw new \Exception(message: 'JSON file does not exist: ' . $jsonFile);
            }

            if (filesize(filename: $jsonFile) === 0) {
                throw new \Exception(message: 'JSON file is empty: ' . $jsonFile);
            }

            // Read the JSON file
            $jsonData = file_get_contents(filename: $jsonFile);
            if ($jsonData === false) {
                throw new \Exception(message: 'Failed to read JSON file: ' . $jsonFile);
            }

            // Decode the JSON data
            $data = json_decode(json: $jsonData, associative: true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception(message: 'Invalid JSON data: ' . json_last_error_msg());
            }

            // Check if 'products' key exists in the JSON data
            if (!isset($data['products']) || !is_array($data['products'])) {
                throw new \Exception(message: 'Invalid JSON structure: "products" key is missing or not an array');
            }

            // Open the CSV file for writing
            $file = fopen(filename: $csvFile, mode: 'w');
            if ($file === false) {
                throw new \Exception(message: 'Unable to open CSV file for writing: ' . $csvFile);
            }

            // Write the CSV header
            $header = BarcodeAttributeConfig::BARCODE_ATTRIBUTES;
            fputcsv($file, $header);

            $productsAddedToCSVCount = 0;
            $productsSkippedCount = 0;

            // Write the product data to the CSV file
            foreach ($data['products'] as $product) {
                
                // Fetch the product by UPC (barcode_number)
                if (!empty($product['barcode_number'])) {
                    $shouldProductUpdate = $this->checkBarcodeLastUpdateDelta($product);
                    
                    if (!$shouldProductUpdate) {
                        $productsSkippedCount++;
                        continue; // Skip this iteration and move to the next product
                    }
                }

                $productsAddedToCSVCount++;

                // Apply the "=\"" and "\"" format to barcode_number to preserve leading zeros
                // $barcodeNumber = isset($product['barcode_number']) ? "=\"" . (string)$product['barcode_number'] . "\"" : '';

                $row = [
                    $product['barcode_number'] ?? '',
                    $product['mpn'] ?? '',
                    $product['model'] ?? '',
                    $product['asin'] ?? '',
                    $product['title'] ?? '',
                    $product['manufacturer'] ?? '',
                    $product['brand'] ?? '',
                    $product['age_group'] ?? '',
                    $product['color'] ?? '',
                    $product['gender'] ?? '',
                    $product['material'] ?? '',
                    $product['pattern'] ?? '',
                    $product['format'] ?? '',
                    $product['size'] ?? '',
                    $product['length'] ?? '',
                    $product['width'] ?? '',
                    $product['height'] ?? '',
                    $product['weight'] ?? '',
                    $product['description'] ?? '',
                    implode(', ', $product['images'] ?? []),
                    $product['last_update'] ?? ''
                ];
                fputcsv($file, $row);
            }

            fclose($file);
        } catch (\Exception $e) {
            // Log the error message
            $errorMessage = $e->getMessage();
            $trace = debug_backtrace();
            $caller = $trace[0];
            $this->logError(message: 'Error in function ' . __FUNCTION__ . ' on line ' . $caller['line'] . ': ' . $errorMessage);

            return ['status' => 'error', 'message' => 'Error creating CSV from JSON file: ' . $errorMessage];
        }

        return [
            'status' => 'success',
            'message' => 'CSV file created successfully from JSON file. Products written: ' . $productsAddedToCSVCount . '; Products skipped: ' . $productsSkippedCount . '.'
        ];        
    }

    private function checkBarcodeLastUpdateDelta(array $product): bool
    {
        $barcodeNumber = $product['barcode_number'];
        $apiLastUpdate = $product['last_update'];
        $productTitle = $product['title'] ?? '';

        try {
            // Create search criteria to fetch the product by UPC (barcode_number) attribute
            $searchCriteria = $this->_searchCriteriaBuilder
                ->addFilter('upc_number', $barcodeNumber, 'eq') // Filter by UPC number
                ->addFilter('status', 1, 'eq') // Filter by enabled products (status = 1)
                ->setPageSize(1) // Limit to 1 result since we only expect one product with that UPC
                ->create();
            
            // Get the products based on search criteria
            $productList = $this->_productRepository->getList($searchCriteria);
            
            // Check if the product exists
            $products = $productList->getItems();
            if (count($products) > 0) {
                $product = reset($products); // Get the first product
    
                // Get the barcode_last_update value from Magento (product attribute)
                $barcodeLastUpdate = $product->getData('barcode_last_update');

                // If barcode_last_update is null, we continue the loop without skipping it
                if ($barcodeLastUpdate === null) {
                    $this->logInfo('Barcode ' . $barcodeNumber . ' - No barcode_last_update, continuing iteration.');
                    return true; // Continue the loop
                }
    
                // Convert both dates to DateTime objects for comparison
                $apiLastUpdateTime = new \DateTime($apiLastUpdate, new \DateTimeZone('UTC'));
                $barcodeLastUpdateTime = new \DateTime($barcodeLastUpdate, new \DateTimeZone('UTC'));

                // If API last update is older than Magento's barcode_last_update, we skip the iteration
                if ($apiLastUpdateTime <= $barcodeLastUpdateTime) {
                    $this->logInfo("SKIPPING: Barcode {$barcodeNumber} ({$productTitle}) - as API last update is older or equal to Magento last update.");
                    return false;
                }
    
                // If API last update is newer, continue the loop
                $this->logInfo("PROCEEDING: Barcode {$barcodeNumber} ({$productTitle}) - as API last update is newer.");
                return true;
            } else {
                $this->logInfo('No product found with barcode: ' . $barcodeNumber);
                return true; // Continue the loop (no product found, but we continue)
            }
    
        } catch (\Exception $e) {
            $this->logError('Error checking barcode last update for barcode: ' . $barcodeNumber . '. ' . $e->getMessage());
            return true;
        }
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
     * The logInfo function logs an informational message using the barcode lookup API logger.
     *
     * @param string message The `logInfo` function takes a string parameter named ``, which is
     * used as the message to be logged by the `_barcodeLookupApiLogger`.
     */
    public function logInfo(string $message): void
    {
        $this->_barcodeLookupApiLogger->info($message);
    }
}
