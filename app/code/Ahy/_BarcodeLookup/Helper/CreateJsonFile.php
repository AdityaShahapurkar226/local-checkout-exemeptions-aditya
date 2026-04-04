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

class CreateJsonFile extends AbstractHelper
{
    /**
     * @var LoggerInterface
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

    public function createJsonFile($response, $filepath): string
    {
        try {
            $this->validateResponse(response: $response);

            $combinedProducts = [];

            foreach ($response as $jsonString) {
                $decodedJson = json_decode(json: $jsonString, associative: true);
                if (isset($decodedJson['products'])) {
                    $combinedProducts = array_merge( $combinedProducts, $decodedJson['products']);
                }
            }

            $finalJson = json_encode(value: ['products' => $combinedProducts], flags: JSON_PRETTY_PRINT);

            $this->createDirectoryIfNotExists(filepath: $filepath);
            $this->logFileStatus(filepath: $filepath);
            $this->writeToFile(response: $finalJson, filepath: $filepath);
            $this->setFilePermissions(filepath: $filepath);

            $this->logger->info('JSON file created/updated successfully with 777 permissions: ' . $filepath);
            return 'JSON file created/updated successfully: ' . $filepath;
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $trace = debug_backtrace();
            $caller = $trace[0];
            $this->logger->error('Error in function ' . __FUNCTION__ . ' on line ' . $caller['line'] . ': ' . $errorMessage);
            return 'Error when creating JSON file from CSV.';
        }
    }

    private function validateResponse($response): void
    {
        if (empty($response)) {
            throw new \InvalidArgumentException('Response content is empty.');
        }
    }

    private function createDirectoryIfNotExists($filepath): string
    {
        $directory = dirname($filepath);
        if (!is_dir($directory)) {
            if (!mkdir($directory, 0777, true)) {
                throw new \RuntimeException('Failed to create directory: ' . $directory);
            }
            $this->logger->info('Directory created: ' . $directory);
        }
        return $directory;
    }

    private function logFileStatus($filepath): void
    {
        if (file_exists($filepath)) {
            $this->logger->info('File already exists. Updating content: ' . $filepath);
        } else {
            $this->logger->info('Creating new JSON file: ' . $filepath);
        }
    }

    private function writeToFile($response, $filepath): void
    {
        $file = fopen($filepath, 'w');
        if ($file === false) {
            throw new \Exception('Unable to open file: ' . $filepath);
        }

        if (fwrite($file, $response) === false) {
            throw new \Exception('Failed to write data to file: ' . $filepath);
        }

        fclose($file);
    }

    private function setFilePermissions($filepath): void
    {
        if (!chmod($filepath, 0777)) {
            throw new \RuntimeException('Failed to set file permissions to 777.');
        }
    }
}
