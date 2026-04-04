<?php

namespace Ahy\Authorizenet\Logger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class SavedCCAdminLogger extends Logger
{
    public function __construct()
    {
        parent::__construct('savedcc_admin_logger');
        $this->pushHandler(new StreamHandler(BP . '/var/log/savedcc_admin.log', Logger::DEBUG));
    }
}
