<?php

/**
 * Copyright © Afzal Sayed All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Ahy\BarcodeLookup\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Ahy\BarcodeLookup\Logger\Logger as BarcodeLookupApiLogger;

class CreateCsvForMagentoProducts extends AbstractHelper
{
    /**
     * @var BarcodeLookupApiLogger
     */
    private $logger;

    /**
     * @param Context $context
     * @param BarcodeLookupApiLogger $logger
     */
    public function __construct(Context $context, BarcodeLookupApiLogger $logger)
    {
        parent::__construct($context);
        $this->logger = $logger;
    }

    public function createCsvForMagentoProducts($products, $csvFile): array
    {
        $returnMessageArray = [];
        $csvHeader = ['entity_id', 'type_id', 'sku', 'upc_number', 'status', 'barcode_last_update', 'name', 'visibility'];

        try {
            // Ensure the directory exists, create it if not
            $directory = dirname($csvFile); // Get the directory from the file path

            if (!file_exists($directory)) {
                // Create the directory, with recursive flag to create any necessary parent directories
                mkdir($directory, 0777, true);  // 0777 gives full permissions
            }

            // Try opening the file for writing
            $fp = fopen($csvFile, 'w');

            // Check if the file pointer is valid
            if ($fp === false) {
                throw new \Exception(__('Failed to open file for writing: ') . $csvFile);
            }

            // Write the CSV content
            $header = false;
            foreach ($products as $productData) {
                // Check if the product data is an array
                if (!is_array($productData)) {
                    continue;
                }

                if (!$header) {
                    // Write the CSV header row
                    fputcsv($fp, $csvHeader); // Add header row
                    $header = true;
                }

                // Filter product data to include only the keys present in $csvHeader
                $filteredProductData = array_intersect_key($productData, array_flip($csvHeader));

                // Write the filtered product data row
                fputcsv($fp, $filteredProductData);
            }

            // Close the file after writing
            fclose($fp);

            // Return success message
            $returnMessageArray[] = __('CSV file has been created successfully.');
            $this->logger->info('CSV file has been created successfully: ' . $csvFile);
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $trace = debug_backtrace();
            $caller = $trace[0];
            $this->logger->error('Error in function ' . __FUNCTION__ . ' on line ' . $caller['line'] . ': ' . $errorMessage);
            $returnMessageArray[] = ['status' => 'error', 'message' => 'Error when creating CSV from Magento products.'];
        }

        return $returnMessageArray;
    }
}
