<?php
namespace Ewave\ExtendedBundleProduct\Model;

use Ewave\ExtendedBundleProduct\Api\SelectionRepositoryInterface;
use Ewave\ExtendedBundleProduct\Api\SelectionLowestPriceInterface;
use Ewave\ExtendedBundleProduct\Helper\Data as Helper;
use Ewave\ExtendedBundleProduct\Model\ResourceModel\Selection as SelectionResourceModel;
use Magento\Bundle\Model\Selection;
use Magento\Bundle\Model\SelectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\ConfigurableProduct\Pricing\Price\FinalPriceResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\SaleableInterface;

class SelectionRepository implements SelectionRepositoryInterface, SelectionLowestPriceInterface
{
    /**
     * @var SelectionFactory
     */
    protected $selectionFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var FinalPriceResolver
     */
    protected $priceResolver;

    /**
     * @var mixed
     */
    protected $selectionResource;

    /**
     * SelectionRepository constructor.
     * @param SelectionFactory $selectionFactory
     * @param Helper $helper
     * @param ProductCollectionFactory|null $productCollectionFactory
     * @param FinalPriceResolver|null $priceResolver
     * @param SelectionResourceModel|null $selectionResource
     */
    public function __construct(
        SelectionFactory $selectionFactory,
        Helper $helper,
        ProductCollectionFactory $productCollectionFactory = null,
        FinalPriceResolver $priceResolver = null,
        SelectionResourceModel $selectionResource = null
    ) {
        $this->selectionFactory = $selectionFactory;
        $this->helper = $helper;
        $this->productCollectionFactory = $productCollectionFactory ?: ObjectManager::getInstance()->get(
            ProductCollectionFactory::class
        );
        $this->priceResolver = $priceResolver ?: ObjectManager::getInstance()->get(
            FinalPriceResolver::class
        );
        $this->selectionResource = $selectionResource ?: ObjectManager::getInstance()->get(
            SelectionResourceModel::class
        );
    }

    /**
     * @param int $selectionId
     * @return array
     */
    public function getConfigurableOptions($selectionId)
    {
        /** @var Selection $selection */
        $selection = $this->selectionFactory->create();
        $selection->load($selectionId);
        return $this->helper->getConfigurableOptions($selection);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array|null $selectedOptions
     * @return array
     */
    public function getSelectionConfigurableOptions(\Magento\Catalog\Model\Product $product, $selectedOptions = null)
    {
        $options = [];
        if ($product->getTypeId() != Configurable::TYPE_CODE) {
            return $options;
        }

        if ($selectedOptions === null) {
            $selectedOptions = $this->helper->getConfigurableOptions($product);
        }

        $configurableOptions = $product->getTypeInstance()->getUsedProducts($product);
        foreach ($configurableOptions as $option) {
            /** @var Product $option */
            $options[] = [
                'value' => $option->getId(),
                'label' => $option->getSku(),
                'selected' => (empty($selectedOptions) || in_array($option->getId(), $selectedOptions)),
            ];
        }

        return $options;
    }

    /**
     * @param SaleableInterface $selection
     * @return float|false
     */
    public function getSelectionLowestPrice(SaleableInterface $selection)
    {
        $configOptions = $this->helper->getConfigurableOptions($selection);
        if (empty($configOptions)) {
            return false;
        }

        $price = null;
        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect(
                ['price', 'special_price', 'special_from_date', 'special_to_date', 'tax_class_id']
            )
            ->addIdFilter($configOptions)
            ->getItems();

        foreach ($collection as $subProduct) {
            $productPrice = $this->priceResolver->resolvePrice($subProduct);
            $price = $price ? min($price, $productPrice) : $productPrice;
        }

        return (float)$price;
    }

    /**
     * @param int $selectionId
     * @return mixed
     */
    public function getParentProductIdBySelectionId($selectionId)
    {
        return $this->selectionResource->getParentProductIdBySelectionId($selectionId);
    }
}
