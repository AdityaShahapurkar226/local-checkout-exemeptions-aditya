<?php
namespace Ahy\FlxPoint\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Ahy\FlxPoint\Helper\Data as AhyHelperData;
    
class UpdateVendor extends Command
{

    protected $_helperData;
    
    public function __construct(
        AhyHelperData $helperData
    ) {
        $this->_helperData = $helperData;
        parent::__construct();
    } 

    const UPDATE_VENDOR = 'update-vendor';

    protected function configure()
    {
        $Options = [
            new InputOption(
                    self::UPDATE_VENDOR,
                    '-u',
                    InputOption::VALUE_NONE,
                    'Get the vendor details from the API service',
                ),
            ];
        $this->setName('ahy:flxpoint:pull-vendor');
        $this->setDescription('Pull the vendor data from the FlxPoint API service');
        $this->setDefinition($Options);
        
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $vendor = $input->getOption(self::UPDATE_VENDOR);

        $fs = new Filesystem();
        if ($vendor){
            $returnMsgArr = $this->_helperData->getVendorDetails();
            foreach($returnMsgArr as $msg){
                $output->writeln($msg);
            }
        } 
    }
}