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

    /**  Magazine */
    private array $magazineCategoryIds = [3579, 3581, 3582];

    /** Regulated */
    private array $regulatedCategories = [
        273  => 'utility',
        3583 => 'folding',
        261  => 'fixed_blade',
        914  => 'bowie',
        3586 => 'fighting',
        267  => 'throwing',
        3587 => 'resembling_blade',
        265  => 'spring_assisted_switchblade',
        3585 => 'knuckle_guard',
        3589 => 'black-powder-pistols',
        3590 => 'handguns',
        3591 => 'rifles',
        3592 => 'shotguns',
        3593 => 'black-powder-rifles',
        3594 => 'black-powder-bullets',
        3595 => 'antique-rifles',
        3596 => 'blank-guns',
        3597 => 'imitation-guns',
        3598 => 'toy-and-look-a-like-guns',
        3599 => 'paintball-guns',
        3600 => 'grenade-launcher',
        3601 => 'grenade-launcher-rifle-adapter',
        3602 => 'ar-parts-and-stocks',
        3603 => 'pistol-kits',
        3604 => 'rifle-and-pistol-barrels',
        3605 => 'pistol-compensators',
        3606 => 'night-vision',
        3607 => 'lasers',
        3608 => 'gatling-gun-kits',
        3609 => 'speedloaders',
        3610 => 'exploding-targets',
        3611 => 'blank-ammo',
        3612 => 'handgun-ammo',
        3613 => 'rifle-and-shotgun-ammo',
        3614 => 'caliber-50-rifle-ammo',
        3615 => 'caliber-50-handgun-ammo',
        3616 => 'armor-penetrating-rifle-ammo',
        3617 => 'armor-penetrating-handgun-ammo',
        3618 => 'tracer-ammo',
        3619 => 'ammo-shipping',
        3620 => 'reloading-components',
        3621 => 'exotic-shotgun-ammo',
        3622 => 'airguns-rifles',
        3623 => 'airgun-shotshell-ammo',
        3624 => 'airsoftguns',
        3625 => 'bbs-and-pellets',
        3626 => 'co2-capsules',
        3627 => 'kitchen-cutlery',
        3628 => 'machetes',
        3629 => 'throwing-axes',
        3630 => 'axes-hatchets',
        3631 => 'daggers',
        3632 => 'stilettos',
        3633 => 'spears',
        3634 => 'bayonets',
        3635 => 'dirks',
        3636 => 'swords',
        3637 => 'broadheads',
        3638 => 'crossbolts',
        3639 => 'dartguns',
        3640 => 'flare-guns',
        3641 => 'batons',
        3642 => 'handcuffs',
        3643 => 'lathi-sticks',
        3644 => 'hand-grenades',
        3645 => 'blowguns',
        3646 => 'slingshots',
        3647 => 'pepper-spray',
        3648 => 'stun-guns-and-tasers',
        3649 => 'gas-cans',
        3650 => 'co2-insect-repellant-and-co2-auto-inflate-life-vests',
        3651 => 'listening-devices',
        3652 => 'body-armor',
        3653 => 'wood-burning-stoves',
        3654 => 'snakeskin-or-non-traditional-animal-leather',
        3655 => 'medical-devices',
        3656 => 'artic-freeze',
        3657 => 'mr-heater-brand-heaters',
        3658 => 'delta-8',
    ];
    private function detectCategory($product): ?array
    {
        $categoryIds = array_map('intval', $product->getCategoryIds());

        foreach ($categoryIds as $catId) {
            if (in_array($catId, $this->magazineCategoryIds)) {
                return [
                    'rule_type' => 'magazine',
                    'product_type' => 'general'
                ];
            }
        }

        foreach ($categoryIds as $catId) {
            if (isset($this->regulatedCategories[$catId])) {
                return [
                    'rule_type' => 'regulated_weapon',
                    'product_type' => $this->regulatedCategories[$catId]
                ];
            }
        }

        return null;
    }

    public function validate($productId, $state, $age)
    {
        $response = $this->responseFactory->create();

        $product = $this->productRepository->getById($productId);

       
        // --- START LOGGING ALL ATTRIBUTES ---
        try {
            $attributes = $product->getAttributes();
            foreach ($attributes as $attribute) {
                $code = $attribute->getAttributeCode();
                $label = $attribute->getFrontendLabel();
                $value = $product->getData($code);

                // Safe conversion of any value (array, object, or string) to a loggable string
                if (is_array($value) || is_object($value)) {
                    $logValue = json_encode($value);
                } else {
                    $logValue = (string)$value;
                }

                $this->logger->info(sprintf(
                    'Product Attribute Debug - Code: %s, Label: %s, Value: %s',
                    $code,
                    $label,
                    $logValue
                ));
            }
        } catch (\Exception $e) {
            $this->logger->error('Error logging attributes: ' . $e->getMessage());
        }
        // --- END LOGGING ALL ATTRIBUTES ---

        $body = json_decode($this->request->getContent(), true);
        $city = $body['city'] ?? '';

        $typeData = $this->detectCategory($product);

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

        $type = $this->detectCategory($product);

        return $type && $type['rule_type'] === 'regulated_weapon'
            ? $type['product_type']
            : null;
    }

    public function isMagazineProduct(int $productId): bool
    {
        $product = $this->productRepository->getById($productId);

        return !empty(array_intersect(
            $this->magazineCategoryIds,
            $product->getCategoryIds()
        ));
    }
}
