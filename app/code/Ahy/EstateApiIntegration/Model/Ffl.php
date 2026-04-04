<?php
declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Model;

use Ahy\EstateApiIntegration\Api\FflInterface;

class Ffl implements FflInterface
{
    private FflManager $fflManager;

    public function __construct(FflManager $fflManager)
    {
        $this->fflManager = $fflManager;
    }

    public function setFflRequired(bool $required): bool
    {
        $this->fflManager->setFflRequired($required);
        return true;
    }
}
