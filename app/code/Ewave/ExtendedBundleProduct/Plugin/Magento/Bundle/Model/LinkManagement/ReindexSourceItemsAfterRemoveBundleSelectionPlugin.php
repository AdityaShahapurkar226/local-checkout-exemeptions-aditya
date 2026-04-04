<?php

namespace Ewave\ExtendedBundleProduct\Plugin\Magento\Bundle\Model\LinkManagement;

use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ObjectManager;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class ReindexSourceItemsAfterRemoveBundleSelectionPlugin
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ModuleManager $moduleManager
    ) {
        $this->productRepository = $productRepository;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Process source items after bundle selection has been removed.
     *
     * @param ProductLinkManagementInterface $subject
     * @param bool $result
     * @param string $sku
     * @param int $optionId
     * @param string $childSku
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRemoveChild(
        ProductLinkManagementInterface $subject,
        bool $result,
        string $sku,
        int $optionId,
        string $childSku
    ): bool {
        if (!$this->moduleManager->isEnabled('Magento_InventorySalesApi')) {
            return $result;
        }

        $linkProductModel = $this->productRepository->get($childSku);
        if ($linkProductModel->getTypeId() == Configurable::TYPE_CODE) {
            return $result;
        }

        return $this->getPlugin()->afterRemoveChild($subject, $result, $sku, $optionId, $childSku);
    }

    /**
     * @return object
     */
    private function getPlugin()
    {
        /** @codingStandardsIgnoreStart */
        return ObjectManager::getInstance()->get(
            'Magento\InventoryBundleProductIndexer\Plugin\Bundle\Model\LinkManagement\ReindexSourceItemsAfterRemoveBundleSelectionPlugin'
        );
        /** @codingStandardsIgnoreEnd */
    }
}
