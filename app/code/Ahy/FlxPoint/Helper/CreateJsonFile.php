<?php
/**
 * Copyright © Ahy Consulting All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types = 1);

namespace Ahy\FlxPoint\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;

class CreateJsonFile extends AbstractHelper
{
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Creates or updates a JSON file with product data.
     *
     * @param string $folderPath The folder where the file will be stored.
     * @param string $filePath The full path of the file to be created/updated.
     * @param string $responseData The JSON data to be written to the file.
     * @return array Array of messages indicating the operation result.
     */
    public function createJsonFile(string $folderPath, string $filePath, string $responseData): array
    {
        $returnMsgArr = [];

        try {
            // Check if the folder exists, if not create it
            if (!is_dir(filename: $folderPath)) {
                $returnMsgArr[] = "<error>Folder does not exist.<error>\n<info>Creating $folderPath for product-list json.<info>";
                if (!mkdir(directory: $folderPath, permissions: 0777, recursive: true) && !is_dir(filename: $folderPath)) {
                    throw new Exception(message: (string) 'Failed to create directory: ' . $folderPath);
                }
            } else {
                $returnMsgArr[] = "<comment>Folder already exists.<comment>";
            }

            // Open file for writing
            $File = fopen(filename: $filePath, mode: 'w');
            if ($File === false) {
                throw new Exception(message: (string) 'Unable to open file: ' . $filePath);
            }

            // Write the JSON data to the file
            if (fwrite(stream: $File, data: $responseData) === false) {
                throw new Exception(message: (string) 'Failed to write data to file: ' . $filePath);
            }

            // Close the file
            fclose(stream: $File);

            $returnMsgArr[] = '<info>File Created/Updated with product list JSON data successfully.<info>';
        } catch (Exception $e) {
            $returnMsgArr[] = "<error>Error: {$e->getMessage()}<error>";
        }

        return $returnMsgArr;
    }
}
