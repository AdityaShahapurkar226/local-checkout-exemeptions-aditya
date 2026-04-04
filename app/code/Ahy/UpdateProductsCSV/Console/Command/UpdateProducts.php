<?php
namespace Ahy\UpdateProductsCSV\Console\Command;

use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateProducts extends Command
{
    private $appState;

    const FLAG_UPDATE_QUANTITY  = 'quantity';
    const FLAG_UPDATE_CATEGORY  = 'category';

    public function __construct(State $appState)
    {
        $this->appState = $appState;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('ahy:update-products')
            ->setDescription('Update product prices/quantities or categories from CSV files')
            ->addOption(
                self::FLAG_UPDATE_QUANTITY,
                null,
                InputOption::VALUE_NONE,
                'Update product prices and quantities'
            )
            ->addOption(
                self::FLAG_UPDATE_CATEGORY,
                null,
                InputOption::VALUE_NONE,
                'Update product categories'
            );

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if (!$this->appState->getAreaCode()) {
                $this->appState->setAreaCode('adminhtml');
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $output->writeln("<info>Area code already set.</info>");
        }

        $basePath = BP . '/app/code/Ahy/UpdateProductsCSV/custom-script/';
        $executed = false;

        // Run quantity/price update script
        if ($input->getOption(self::FLAG_UPDATE_QUANTITY)) {
            $scriptPath = $basePath . 'update_products_quantities_prices.php';
            if (file_exists($scriptPath)) {
                $output->writeln("<info>Running quantities/prices update script...</info>");
                $result = shell_exec("php $scriptPath");
                $output->writeln("<info>Output:</info>\n" . $result);
            } else {
                $output->writeln("<error>Script not found: $scriptPath</error>");
            }
            $executed = true;
        }

        // Run category update script
        if ($input->getOption(self::FLAG_UPDATE_CATEGORY)) {
            $scriptPath = $basePath . 'update_products_categories.php';
            if (file_exists($scriptPath)) {
                $output->writeln("<info>Running category update script...</info>");
                $result = shell_exec("php $scriptPath");
                $output->writeln("<info>Output:</info>\n" . $result);
            } else {
                $output->writeln("<error>Script not found: $scriptPath</error>");
            }
            $executed = true;
        }

        if (!$executed) {
            $output->writeln("<comment>No option provided. Use --help for available flags.</comment>");
        }

        return 0;
    }
}
