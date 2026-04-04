<?php

namespace Ahy\EstateApiIntegration\Api\Data;

/**
 * Interface RestrictionResponseInterface
 *
 * Data interface used to return restriction validation results
 * from Estate API integration.
 *
 * This response determines:
 * - Whether the product is restricted
 * - The restriction reason (if any)
 * - Whether the product belongs to Knife category
 *
 * @api
 */
interface RestrictionResponseInterface
{
    /**
     * Check if product is restricted.
     *
     * @return bool
     */
    public function getRestricted();

    /**
     * Set restriction flag.
     *
     * @param bool $restricted
     * @return \Ahy\EstateApiIntegration\Api\Data\RestrictionResponseInterface
     */
    public function setRestricted($restricted);

    /**
     * Get restriction reason message.
     *
     * @return string|null
     */
    public function getReason();

    /**
     * Set restriction reason message.
     *
     * @param string|null $reason
     * @return \Ahy\EstateApiIntegration\Api\Data\RestrictionResponseInterface
     */
    public function setReason($reason);

    /**
     * Check if product belongs to Knife category.
     *
     * Used by frontend to determine whether
     * age verification modal should be triggered.
     *
     * @return bool
     */
    public function getIsKnife();

    /**
     * Set knife category flag.
     *
     * @param bool $isKnife
     * @return \Ahy\EstateApiIntegration\Api\Data\RestrictionResponseInterface
     */
    public function setIsKnife($isKnife);
}