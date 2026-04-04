<?php
declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Api;

interface FflInterface
{
    /**
     * Set FFL required for all products in cart
     *
     * @param bool $required
     * @return bool
     */
    public function setFflRequired(bool $required): bool;
}
