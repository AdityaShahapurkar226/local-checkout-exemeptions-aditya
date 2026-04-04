<?php
namespace Ahy\Authorizenet\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Ahy\Authorizenet\Helper\Data;
    
class AuthEncryptionKeyGenerate extends Command
{

    protected $_helperData;
    
    public function __construct(
        Data    $helperData
    ) {
        $this->_helperData = $helperData;
        parent::__construct();
    } 

    const ENCRYPTION_KEY = 'encryptionKey';

    protected function configure()
    {
        $Options = [
            new InputOption(
                    self::ENCRYPTION_KEY,
                    '-key',
                    InputOption::VALUE_NONE,
                    'Generate New Authorize Net Encryption Key',
                )
            ];
        $this->setName('ahy:authorizenet:generate');
        $this->setDescription('Generate New Authorize Net Encryption Key');
        $this->setDefinition($Options);
        
        parent::configure();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $authorizeEncryptionKey = $input->getOption(self::ENCRYPTION_KEY);
        $enKey = $this->_helperData->generateAuthorizeNetEncryptionKey();
        $output->writeln('New Authorize Net Encryption Key Generated Successfully');
    }
}