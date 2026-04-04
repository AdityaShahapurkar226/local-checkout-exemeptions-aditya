<?php

namespace Ahy\EstateApiIntegration\Api;

interface CityInterface
{
    /**
     * @param string $ruleType
     * @param string $state
     * @return string[]
     */
    public function getCities($ruleType, $state);
}
