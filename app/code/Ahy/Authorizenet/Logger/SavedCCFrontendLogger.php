<?php

namespace Ahy\Authorizenet\Logger;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class SavedCCFrontendLogger extends Logger
{
    public function __construct()
    {
        parent::__construct('savedcc_frontend_logger');
        $this->pushHandler(new StreamHandler(BP . '/var/log/savedcc_frontend.log', Logger::DEBUG));
    }
}
