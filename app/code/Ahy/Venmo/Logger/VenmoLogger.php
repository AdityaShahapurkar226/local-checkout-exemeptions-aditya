<?php
declare(strict_types=1);

namespace Ahy\Venmo\Logger;

use Monolog\Logger as MonologLogger;
use Ahy\Venmo\Logger\Handler\Venmo as VenmoHandler;

class VenmoLogger extends MonologLogger
{
    public function __construct(VenmoHandler $handler)
    {
        parent::__construct('venmo');  // Monolog requires a name
        $this->pushHandler($handler);  // attach injected handler
    }
}
