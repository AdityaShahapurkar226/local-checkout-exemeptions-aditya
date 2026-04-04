<?php

namespace Ahy\EstateApiIntegration\Api;

use Ahy\EstateApiIntegration\Api\Data\RestrictionResponseInterface;

interface RestrictionValidatorInterface
{
    /**
     * @param int $productId
     * @param string $state
     * @param int $age
     * @return RestrictionResponseInterface
     */
    public function validate($productId, $state, $age);
}