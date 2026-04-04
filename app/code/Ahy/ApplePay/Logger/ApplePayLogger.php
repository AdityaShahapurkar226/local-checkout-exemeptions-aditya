<?php
namespace Ahy\ApplePay\Logger;

use Monolog\Logger;

class ApplePayLogger extends Logger
{
    public function __construct(
        $name,
        array $handlers = [],
        array $processors = []
    ) {
        parent::__construct($name, $handlers, $processors);
    }
}
