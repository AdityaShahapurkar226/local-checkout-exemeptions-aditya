<?php

namespace Ahy\FlxPoint\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Ahy\FlxPoint\Helper\Data as AhyHelperData;

class ImportProducts extends Command
{
    protected $_helperData;

    const CREATE_JSON       = 'create-json';
    const IMPORT_PRODUCT    = 'import-product';
    const CREATE_CSV        = 'create-csv';
    const CREATE_SQL        = 'create-sql';
    const CHANGE_DELTA      = 'change-delta';
    const MAGENTO_ROOT_PATH = BP;

    public function __construct(
        AhyHelperData $helperData
    ) {
        $this->_helperData = $helperData;
        parent::__construct();
    }

    protected function configure()
    {
        $Options = [
            new InputOption(
                self::CREATE_JSON,
                '-j',
                InputOption::VALUE_NONE,
                'Get the product details from the Flxpoint API service and create a JSON file'
            ),
            new InputOption(
                self::IMPORT_PRODUCT,
                '-i',
                InputOption::VALUE_NONE,
                'Create the SQL file from the CSV, import the products, and move the SQL file to the processed folder'
            ),
            new InputOption(
                self::CREATE_CSV,
                '-c',
                InputOption::VALUE_NONE,
                'Create the CSV from the JSON file'
            ),
            new InputOption(
                self::CREATE_SQL,
                '-s',
                InputOption::VALUE_NONE,
                'Create the SQL file from the CSV'
            ),
            new InputOption(
                self::CHANGE_DELTA,
                '-d',
                InputOption::VALUE_NONE,
                'Update the delta in the database'
            ),
        ];
        $this->setName('ahy:flxpoint:import-product');
        $this->setDescription('Import the product data from the FlxPoint API service');
        $this->setDefinition($Options);

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $createJson     = $input->getOption(self::CREATE_JSON);
        $importProducts = $input->getOption(self::IMPORT_PRODUCT);
        $createCsv      = $input->getOption(self::CREATE_CSV);
        $createSql      = $input->getOption(self::CREATE_SQL);
        $changeDelta    = $input->getOption(self::CHANGE_DELTA);

        if ($createJson) {
            $returnMsgArr = $this->_helperData->getProductParentsDetails();
            foreach ($returnMsgArr as $msg) {
                $output->writeln($msg);
            }
        } elseif ($createCsv) {
            $returnMsgArr = $this->_helperData->createCsvFileFromJsonFile();
            $output->writeln('Downloading product images...');
            $magentoRootDir = self::MAGENTO_ROOT_PATH;
            $scriptPath = $magentoRootDir . '/app/code/Ahy/FlxPoint/ShellScripts/download_product_image.sh';
            $baseFolderPath = $magentoRootDir . '/';
            $command = escapeshellcmd("bash $scriptPath $baseFolderPath");
            $result = shell_exec($command);
            $output->writeln($result);
            $output->writeln('Downloading product images complete...');
            foreach ($returnMsgArr as $msg) {
                $output->writeln($msg);
            }
        } elseif ($importProducts) {
            $output->writeln("Starting product import...");
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
            $scriptPath = $magentoRootDir . '/app/code/Ahy/FlxPoint/ShellScripts/import_script.sh';
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
            $returnMsgArr = $this->_helperData->createSqlFileFromCsvFile();
            foreach ($returnMsgArr as $msg) {
                $output->writeln($msg);
            }
        } elseif ($changeDelta) {
            $output->writeln("Updating delta in the database...");
            try {
                $returnMsgArr[] = $this->_helperData->updateDelta();
                foreach ($returnMsgArr as $msg) {
                    $output->writeln($msg);
                }
                $output->writeln("<info>Delta updated successfully.</info>");
            } catch (\Exception $e) {
                $output->writeln("<error>Error updating delta: " . $e->getMessage() . "</error>");
            }
        }
    }
}
