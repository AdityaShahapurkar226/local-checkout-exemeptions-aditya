<?php

namespace Ahy\EstateApiIntegration\Model;

use Ahy\EstateApiIntegration\Api\ProductZipValidatorInterface;
use Ahy\EstateApiIntegration\Service\EstateApiService;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Ahy\EstateApiIntegration\Logger\Logger;

class ProductZipValidator implements ProductZipValidatorInterface
{
    private ProductRepositoryInterface $productRepository;
    private Logger $logger;
    private EstateApiService $estateApiService;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        Logger $logger,
        EstateApiService $estateApiService
    ) {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->estateApiService = $estateApiService;
    }

    /**
     * Validate a product's UPC against a ZIP code.
     *
     * @param int $productId
     * @param string $zip
     * @return int|array
     */
    public function validate(int $productId, string $zip)
    {
        try {
            $product = $this->productRepository->getById($productId);

            // Support upc / upc_number attributes
            $upcAttr = $product->getCustomAttribute('upc') 
                ?? $product->getCustomAttribute('upc_number');

            $upc = $upcAttr ? $upcAttr->getValue() : null;
            $this->logger->info("Validating product ID {$productId} with UPC {$upc} for ZIP {$zip}");

            if (!$upc) {
                $this->logger->warning("Missing UPC for product ID {$productId}");
                return 4;
            }

            // Return the EXACT output of the service:
            // int OR restriction object
            return $this->estateApiService->validateProductUpcWithZip($upc, $zip);

        } catch (\Exception $e) {
            $this->logger->error("Error in validate(): " . $e->getMessage());
            return 4;
        }
    }
}
