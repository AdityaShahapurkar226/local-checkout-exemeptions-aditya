<?php

namespace Ewave\ExtendedBundleProduct\Plugin\Magento\Bundle\Model\LinkManagement;

use Ewave\ExtendedBundleProduct\Model\BundleLinkRegistry;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class ReindexSourceItemsAfterAddBundleSelectionPlugin
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
     * @param ProductInterface $product
     * @param int $optionId
     * @param LinkInterface $linkedProduct
     * @return array
     */
    public function beforeAddChild(
        ProductLinkManagementInterface $subject,
        ProductInterface $product,
        int $optionId,
        LinkInterface $linkedProduct
    ) {
        $this->bundleLinkRegistry->setLink($linkedProduct);
        return [$product, $optionId, $linkedProduct];
    }

    /**
     * Reindex source items after selection has been added to bundle product.
     *
     * @param ProductLinkManagementInterface $subject
     * @param int $result
     * @param ProductInterface $product
     * @param int $optionId
     * @param LinkInterface $linkedProduct
     * @return int
     * @throws InputException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAddChild(
        ProductLinkManagementInterface $subject,
        int $result,
        ProductInterface $product,
        int $optionId,
        LinkInterface $linkedProduct
    ): int {
        $this->bundleLinkRegistry->clearLink();
        if (!$this->moduleManager->isEnabled('Magento_InventorySalesApi')) {
            return $result;
        }

        $linkProductModel = $this->productRepository->get($linkedProduct->getSku());
        if ($linkProductModel->getTypeId() == Configurable::TYPE_CODE) {
            return $result;
        }

        return $this->getPlugin()->afterAddChild($subject, $result, $product, $optionId, $linkedProduct);
    }

    /**
     * @return object
     */
    private function getPlugin()
    {
        /** @codingStandardsIgnoreStart */
        return ObjectManager::getInstance()->get(
            'Magento\InventoryBundleProductIndexer\Plugin\Bundle\Model\LinkManagement\ReindexSourceItemsAfterAddBundleSelectionPlugin'
        );
        /** @codingStandardsIgnoreEnd */
    }
}
