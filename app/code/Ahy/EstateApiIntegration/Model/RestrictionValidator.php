<?php

namespace Ahy\EstateApiIntegration\Model;

use Ahy\EstateApiIntegration\Api\RestrictionValidatorInterface;
use Ahy\EstateApiIntegration\Api\Data\RestrictionResponseInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;
use Ahy\EstateApiIntegration\Service\LocalLawApiService;

class RestrictionValidator implements RestrictionValidatorInterface
{
    private $productRepository;
    private $responseFactory;
    private $request;
    private $api;
    private $logger;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        RestrictionResponseInterfaceFactory $responseFactory,
        RequestInterface $request,
        LocalLawApiService $api,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->responseFactory = $responseFactory;
        $this->request = $request;
        $this->api = $api;
        $this->logger = $logger;
    }

    private function detectProductType($product): ?array
    {
        $value = $product->getAttributeText('producttype');

        if (!$value || $value === false) {
            return null;
        }

        $value = is_array($value) ? implode(',', $value) : (string) $value;
        $value = strtolower(trim($value));

        if ($value === '') {
            return null;
        }

        if ($value === 'magazine') {
            $magType = $product->getAttributeText('magazine_type');
            $magType = is_array($magType) ? implode(',', $magType) : (string) $magType;
            return [
                'rule_type' => 'magazine',
                'product_type' => strtolower(trim(str_replace(' ', '', $magType)))
            ];
        }

        return [
            'rule_type' => 'regulated_weapon',
            'product_type' => $value
        ];
    }

    public function validate($productId, $state, $age)
    {
        $response = $this->responseFactory->create();

        $product = $this->productRepository->getById($productId);

        $body = json_decode($this->request->getContent(), true);
        $city = $body['city'] ?? '';

        $typeData = $this->detectProductType($product);

        if (!$typeData) {
            return $this->build(false, null);
        }

        $this->logger->info('Restriction Type', $typeData);

        $api = $this->api->validateRule(
            $typeData['rule_type'],
            $state,
            $typeData['product_type'],
            $city
        );

        if (!$api || !isset($api['rule'])) {
            return $this->build(false, null);
        }

        $rule = $api['rule'];

        if (isset($rule['ship']) && $rule['ship'] === false) {
            $location = !empty($city) ? "{$city}, {$state}" : $state;
            return $this->build(true, "Cannot ship to {$location}");
        }

        if (!empty($rule['blocked'])) {
            return $this->build(true, "Restricted in {$state}");
        }

        if (isset($rule['min_age'])) {

            if ($age == 0) {
                return $this->build(true, "__AGE_REQUIRED__|" . $rule['min_age']);
            }

            if ($age < $rule['min_age']) {
                return $this->build(true, "Minimum age: " . $rule['min_age']);
            }
        }

        if (isset($rule['magzine_capacity']) && $rule['magzine_capacity'] > 0) {
            return $this->build(true, "Max allowed: " . $rule['magzine_capacity });']);
        }

        return $this->build(false, null);
    }

    private function build($restricted, $reason)
    {
        $r = $this->responseFactory->create();
        $r->setRestricted($restricted);
        $r->setReason($reason);
        return $r;
    }

    /** 🔹 For ViewModel */
    public function getProductRegulatedType(int $productId): ?string
    {
        $product = $this->productRepository->getById($productId);

        $type = $this->detectProductType($product);

        return $type && $type['rule_type'] === 'regulated_weapon'
            ? $type['product_type']
            : null;
    }

    public function isMagazineProduct(int $productId): bool
    {
        $product = $this->productRepository->getById($productId);
        $value = $product->getAttributeText('producttype');

        if (!$value || $value === false) {
            return false;
        }

        $value = is_array($value) ? implode(',', $value) : (string) $value;

        return strtolower(trim($value)) === 'magazine';
    }
}
