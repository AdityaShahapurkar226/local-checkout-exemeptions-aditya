<?php 

namespace Ahy\Authorizenet\Controller\Test;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Ahy\Authorizenet\Cron\DeactivateExpiredVaultCards;

class RunCron extends Action
{
    protected $cronJob;

    public function __construct(Context $context, DeactivateExpiredVaultCards $cronJob)
    {
        parent::__construct($context);
        $this->cronJob = $cronJob;
    }

    public function execute()
    {
        $this->cronJob->execute();
        echo "Cron executed successfully.";
    }
}
