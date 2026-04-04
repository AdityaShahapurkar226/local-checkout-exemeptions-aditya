<?php
namespace Ahy\EstateApiIntegration\Api;

interface ProductZipValidatorInterface
{
    /**
     * Validate ZIP code for a product.
     *
     * @param int $productId
     * @param string $zip
     * @return int|array  Returns:
     *                    - int (1,2,3,5,4)
     *                    - array with restriction data for new Orchid format
     */
    public function validate(int $productId, string $zip);
}
