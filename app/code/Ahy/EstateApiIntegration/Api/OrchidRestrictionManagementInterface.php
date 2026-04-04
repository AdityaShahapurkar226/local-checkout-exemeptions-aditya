<?php
declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Api;

interface OrchidRestrictionManagementInterface
{
    /**
     * Save or update the Orchid restriction level on the quote.
     *
     * @param mixed $responseCode
     * @param int    $productId   Product ID for which the response applies
     *
     * @return bool Returns true on success, false on failure
     */
    public function saveRestriction($responseCode, int $productId): bool;
}
