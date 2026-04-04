<?php
namespace Ahy\FlxPoint\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Ahy\FlxPoint\Helper\Data as AhyHelperData;
    
class UpdateProduct extends Command
{

    protected $_helperData;
    
    public function __construct(
        AhyHelperData $helperData
    ) {
        $this->_helperData = $helperData;
        parent::__construct();
    } 

    const UPDATE_PRODUCT    = 'update-product';
    const READ_CSV          = 'read-csv';

    protected function configure()
    {
        $Options = [
            new InputOption(
                    self::UPDATE_PRODUCT,
                    '-u',
                    InputOption::VALUE_NONE,
                    'Get the product details from the API service',
                ),
            new InputOption(
                    self::READ_CSV,
                    '-r',
                    InputOption::VALUE_NONE,
                    'Read CSV',
                ),
            ];
        $this->setName('ahy:flxpoint:pull-product');
        $this->setDescription('Pull the product data from the FlxPoint API service');
        $this->setDefinition($Options);
        
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $product = $input->getOption(self::UPDATE_PRODUCT);
        $readCsv = $input->getOption(self::READ_CSV);

        $fs = new Filesystem();
        if ($product){
            $returnMsgArr = $this->_helperData->getProductParentsDetails();
            foreach($returnMsgArr as $msg){
                $output->writeln($msg);
            }
        } elseif ($readCsv){
            $returnMsgArr = $this->_helperData->readVendorProductCsvImportFile();
            foreach($returnMsgArr as $msg){
                $output->writeln($msg);
            }
        }
    }
}