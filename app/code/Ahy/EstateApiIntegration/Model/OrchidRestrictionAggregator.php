<?php

declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Model;

class OrchidRestrictionAggregator
{
    /**
     * 1 = highest priority
     * 4 = lowest priority
     */
    private array $priorityMap = [
        // Highest
        '2'  => 1,
        'FF' => 1,

        // High
        '3'  => 3,

        // Medium
        'AA'  => 2,
        'BB'  => 2,
        'CC'  => 2,
        'SHI' => 2,
        'SNJ' => 2,

        // Lowest
        '4' => 4,
        '5' => 4,
        '0' => 4,
        '1' => 4,
    ];

    /**
     * Returns the highest-priority restriction
     */
    public function aggregate(
        int|string|null $existing,
        int|string|null $new
    ): ?string {
        if ($existing === null) {
            return $new !== null ? (string) $new : null;
        }

        if ($new === null) {
            return (string) $existing;
        }

        $existingStr = (string) $existing;
        $newStr      = (string) $new;

        $existingRank = $this->priorityMap[$existingStr] ?? PHP_INT_MAX;
        $newRank      = $this->priorityMap[$newStr] ?? PHP_INT_MAX;

        // LOWER number = HIGHER priority
        return ($newRank < $existingRank) ? $newStr : $existingStr;
    }
}
