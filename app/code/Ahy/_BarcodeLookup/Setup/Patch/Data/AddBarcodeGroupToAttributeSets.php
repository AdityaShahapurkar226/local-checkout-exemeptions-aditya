<?php
namespace Ahy\BarcodeLookup\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Api\SearchCriteriaBuilder;

class AddBarcodeGroupToAttributeSets implements DataPatchInterface
{
    private $moduleDataSetup;
    private $eavSetupFactory;
    private $attributeSetRepository;
    private $attributeGroupRepository;
    private $attributeManagement;
    private $searchCriteriaBuilder;

    private const ENTITY_TYPE = 'catalog_product';
    private const GROUP_NAME = 'Barcode Lookup Data';

    private const ATTRIBUTE_CODES = [
        'barcode_last_update',
        'barcode_color',
        'barcode_format',
        'barcode_age_group',
        'barcode_description',
        'barcode_brand',
        'barcode_asin',
        'barcode_weight',
        'barcode_gender',
        'barcode_height',
        'barcode_width',
        'barcode_length',
        'barcode_material',
        'barcode_pattern',
        'barcode_manufacturer',
        'barcode_size',
        'barcode_title',
        'barcode_model',
        'barcode_mpn'
    ];

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        AttributeSetRepositoryInterface $attributeSetRepository,
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        AttributeManagementInterface $attributeManagement,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->attributeManagement = $attributeManagement;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $attributeSets = $this->attributeSetRepository->getList($searchCriteria)->getItems();

        foreach ($attributeSets as $attributeSet) {
            $setId = $attributeSet->getAttributeSetId();

            // Check if group already exists
            try {
                $group = $this->attributeGroupRepository->get(self::ENTITY_TYPE, $setId, self::GROUP_NAME);
                $groupId = $group->getAttributeGroupId();
            } catch (LocalizedException $e) {
                // Group does not exist — create it
                $eavSetup->addAttributeGroup(
                    self::ENTITY_TYPE,
                    $setId,
                    self::GROUP_NAME,
                    99 // sort order
                );
                $groupId = $eavSetup->getAttributeGroupId(self::ENTITY_TYPE, $setId, self::GROUP_NAME);
            }

            foreach (self::ATTRIBUTE_CODES as $attributeCode) {
                try {
                    // Assign attribute to group only if not already assigned
                    $this->attributeManagement->assign(
                        self::ENTITY_TYPE,
                        $setId,
                        $groupId,
                        $attributeCode,
                        100
                    );
                } catch (\Exception $e) {
                    // Attribute might already be assigned or not exist — skip silently
                    continue;
                }
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies() { return []; }
    public function getAliases() { return []; }
}
