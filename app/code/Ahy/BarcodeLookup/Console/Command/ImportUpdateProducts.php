<?php

/**
 * Copyright © Ahy Consulting All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Ahy\BarcodeLookup\Console\Command;
use Ahy\BarcodeLookup\Logger\Logger as BarcodeLookupApiLogger;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Ahy\BarcodeLookup\Helper\Data as AhyHelperData;

class ImportUpdateProducts extends Command
{

    protected $_helperData;
    const MAGENTO_ROOT_PATH = BP;

    /**
     * @var BarcodeLookupApiLogger
     */
    private $_barcodeLookupApiLogger;

    public function __construct(
        AhyHelperData $helperData,
        BarcodeLookupApiLogger $barcodeLookupApiLogger
    ) {
        $this->_helperData = $helperData;
        $this->_barcodeLookupApiLogger = $barcodeLookupApiLogger;
        parent::__construct();
    }

    const GET_UPC           = 'get-upc';
    const CREATE_JSON       = 'create-json';
    const IMPORT_PRODUCT    = 'import-product';
    const CREATE_CSV        = 'create-csv';
    const CREATE_SQL        = 'create-sql';

    protected function configure()
    {
        $Options = [
            new InputOption(
                self::CREATE_JSON,
                '-j',
                InputOption::VALUE_NONE,
                'Get the product details from the BarcodeLookup API service and create a json file',
            ),
            new InputOption(
                self::IMPORT_PRODUCT,
                '-i',
                InputOption::VALUE_NONE,
                'Create the SQL file form the CSV and import the products and then moved the SQL file in the processed folder `var/BarcodeLookup/Import-Process/product-catalog/processed/{current_date}`. Check the `var/BarcodeLookup/Import-Process/product-catalog/log/` for further import related details. ',
            ),
            new InputOption(
                self::CREATE_CSV,
                '-c',
                InputOption::VALUE_NONE,
                'Create the CSV from the json file',
            ),
            new InputOption(
                self::CREATE_SQL,
                '-s',
                InputOption::VALUE_NONE,
                'Create the SQL file from the CSV',
            ),
            new InputOption(
                self::GET_UPC,
                '-g',
                InputOption::VALUE_NONE,
                'This will create the CSV file with the product data where the products has UPC Numbers',
            ),
        ];
        $this->setName('ahy:barcodelookup:update-product');
        $this->setDescription('import the product data from the BarcodeLookup API service');
        $this->setDefinition($Options);

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $createJson     = $input->getOption(self::CREATE_JSON);
        $importProducts = $input->getOption(self::IMPORT_PRODUCT);
        $createCsv      = $input->getOption(self::CREATE_CSV);
        $createSql      = $input->getOption(self::CREATE_SQL);
        $getUpc         = $input->getOption(self::GET_UPC);

        $fs = new Filesystem();
        if ($createJson) {
            $output->writeln("create json");
            $returnMsgArr = $this->_helperData->createJsonFileFromCsvFile();

            /* foreach ($returnMsgArr as $msg) {
                $output->writeln($msg);
            } */

            /*
                $returnMsgArr = [
                    [
                        '{ "products": [ ... ] }',
                        '{ "products": [ ... ] }'
                    ]
                ]; 
            */

            foreach ($returnMsgArr as $msgBlock) {
                if (is_array($msgBlock)) {
                    foreach ($msgBlock as $msg) {
                        $data = json_decode($msg, true);
            
                        if (isset($data['products']) && is_array($data['products'])) {
                            foreach ($data['products'] as $product) {
                                $barcode = $product['barcode_number'] ?? 'N/A';
                                $title = $product['title'] ?? 'N/A';
                                $this->_barcodeLookupApiLogger->info("Product - Barcode: $barcode | Title: $title");
                            }
                        }
                    }
                }
            }                                    

        } elseif ($createCsv) {
            $output->writeln("create csv");
            $returnMsgArr = $this->_helperData->createCsvFileFromJsonFile();

            foreach ($returnMsgArr as $msg) {
                $output->writeln($msg);
            }
        } elseif ($importProducts) {
            $output->writeln("Starting product import for BarcodeLookup...");
            
            $magentoRootDir = self::MAGENTO_ROOT_PATH;
            $envFilePath = $magentoRootDir . '/app/etc/env.php';
            if (!file_exists($envFilePath)) {
                $output->writeln("<error>Error: env.php file not found at $envFilePath</error>");
            }
            $envConfig = include $envFilePath;
            if (!isset($envConfig['db']['connection']['default'])) {
                $output->writeln("<error>Error: Database configuration not found in env.php</error>");
            }
            $dbConfig = $envConfig['db']['connection']['default'];
            $mysqlUser = $dbConfig['username'];
            $mysqlPassword = $dbConfig['password'];
            $mysqlDatabase = $dbConfig['dbname'];
            $mysqlHost = $dbConfig['host'];
            $scriptPath = $magentoRootDir . '/app/code/Ahy/BarcodeLookup/ShellScripts/import_script.sh';

            $baseFolderPath = $magentoRootDir . '/';
            $command = escapeshellcmd("bash $scriptPath $baseFolderPath $mysqlUser $mysqlPassword $mysqlDatabase $mysqlHost");
            $result = shell_exec($command);
            $output->writeln($result);

            $output->writeln("Running Magento reindex command...");

            exec('php bin/magento indexer:reset');
            exec('php bin/magento indexer:reindex');

            $output->writeln("Magento reindex command completed...");
            $output->writeln("Running Magento cache clean flush command...");

            exec('php bin/magento c:c');
            exec('php bin/magento c:f');
            
            $output->writeln("Magento cache clean flush command completed...");
        } elseif ($createSql) {
            $output->writeln("create sql");
            $returnMsgArr = $this->_helperData->createSqlFileFromCsvFile();
            foreach ($returnMsgArr as $msg) {
                $output->writeln($msg);
            }
        } elseif ($getUpc) {
            $output->writeln("Start retrieving the UPCs");
            $returnMsgArr = $this->_helperData->getProductsWithUpc();
            // If products are found, print details of each product
            foreach ($returnMsgArr as $message) {
                $output->writeln($message);
            }
        } else {
            // Default action: create json file if none provided.
            $output->writeln("No action provided. Please use the provided options.\n");
            $output->writeln("Available options:\n");
            foreach ($this->getDefinition()->getOptions() as $option) {
                $output->writeln($option->getName() . ": " . $option->getShortcut());
                if ($option->getDescription()) {
                    $output->writeln("<comment>" . $option->getDescription() . "</comment>");
                }
                $output->writeln("");
            }
        }
    }
}
