<?php

namespace Ewave\ExtendedBundleProduct\Plugin\Magento\Bundle\Model\LinkManagement;

use Ewave\ExtendedBundleProduct\Model\BundleLinkRegistry;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ObjectManager;
use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class ReindexSourceItemsAfterSaveBundleSelectionPlugin
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
     * @var BundleLinkRegistry
     */
    private $bundleLinkRegistry;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ModuleManager $moduleManager
     * @param BundleLinkRegistry $bundleLinkRegistry
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ModuleManager $moduleManager,
        BundleLinkRegistry $bundleLinkRegistry
    ) {
        $this->productRepository = $productRepository;
        $this->moduleManager = $moduleManager;
        $this->bundleLinkRegistry = $bundleLinkRegistry;
    }

    /**
     * @param ProductLinkManagementInterface $subject
     * @param string $sku
     * @param LinkInterface $linkedProduct
     * @return array
     */
    public function beforeSaveChild(
        ProductLinkManagementInterface $subject,
        string $sku,
        LinkInterface $linkedProduct
    ) {
        $this->bundleLinkRegistry->setLink($linkedProduct);
        return [$sku, $linkedProduct];
    }

    /**
     * Reindex source items after bundle selection has been updated.
     *
     * @param ProductLinkManagementInterface $subject
     * @param bool $result
     * @param string $sku
     * @param LinkInterface $linkedProduct
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function afterSaveChild(
        ProductLinkManagementInterface $subject,
        bool $result,
        string $sku,
        LinkInterface $linkedProduct
    ): bool {
        $this->bundleLinkRegistry->clearLink();
        if (!$this->moduleManager->isEnabled('Magento_InventorySalesApi')) {
            return $result;
        }

        $linkProductModel = $this->productRepository->get($linkedProduct->getSku());
        if ($linkProductModel->getTypeId() == Configurable::TYPE_CODE) {
            return $result;
        }

        return $this->getPlugin()->afterSaveChild($subject, $result, $sku, $linkedProduct);
    }

    /**
     * @return object
     */
    private function getPlugin()
    {
        /** @codingStandardsIgnoreStart */
        return ObjectManager::getInstance()->get(
            'Magento\InventoryBundleProductIndexer\Plugin\Bundle\Model\LinkManagement\ReindexSourceItemsAfterSaveBundleSelectionPlugin'
        );
        /** @codingStandardsIgnoreEnd */
    }
}
