<?php

namespace Ewave\ExtendedBundleProduct\Plugin\Magento\ConfigurableProduct\Block\Product\View\Type;

use Ewave\ExtendedBundleProduct\Api\SelectionRepositoryInterface;
use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as Subject;
use Magento\Framework\Locale\Format;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;

/**
 * Class Configurable
 *
 * @package Ewave\ExtendedBundleProduct\Plugin\Magento\ConfigurableProduct\Block\Product\View\Type
 */
class Configurable
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var Format
     */
    protected $localeFormat;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var SelectionRepositoryInterface
     */
    protected $selectionRepository;

    /**
     * @var JsonSerializer
     */
    protected $jsonSerializer;

    /**
     * @var CalculatorInterface
     */
    protected $calculator;

    /**
     * Configurable constructor.
     * @param ProductRepository $productRepository
     * @param Format $localeFormat
     * @param CollectionFactory $productCollectionFactory
     * @param SelectionRepositoryInterface $selectionRepository
     * @param JsonSerializer $jsonSerializer
     * @param CalculatorInterface $calculator
     */
    public function __construct(
        ProductRepository $productRepository,
        Format $localeFormat,
        CollectionFactory $productCollectionFactory,
        SelectionRepositoryInterface $selectionRepository,
        JsonSerializer $jsonSerializer,
        CalculatorInterface $calculator
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->localeFormat = $localeFormat;
        $this->selectionRepository = $selectionRepository;
        $this->jsonSerializer = $jsonSerializer;
        $this->calculator = $calculator;
    }

    /**
     * @param Subject $subject
     * @param string $result
     * @return false|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetJsonConfig(Subject $subject, $result)
    {
        $jsonConfig = $this->jsonSerializer->unserialize($result);
        $parentProductId = $subject->getProduct()->getParentProductId();
        if ($selectionId = $subject->getProduct()->getSelectionId()) {
            $parentProductId = $this->selectionRepository->getParentProductIdBySelectionId($selectionId);
        }
        if (!empty($parentProductId)) {
            $bundleProduct = $this->productRepository->getById($parentProductId);
            if ($bundleProduct->getTypeId() == BundleProductType::TYPE_CODE) {
                if (!empty($jsonConfig['optionPrices'])) {
                    $productsId = array_keys($jsonConfig['optionPrices']);
                    $productCollection = $this->productCollectionFactory->create()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('entity_id', ['in' => $productsId]);
                    foreach ($jsonConfig['optionPrices'] as $productId => $option) {
                        $product = $productCollection->getItemById($productId);
                        $preFinalPrice = $bundleProduct->getPriceModel()
                            ->getSelectionPreFinalPrice($bundleProduct, $product, 1);
                        $amount = $this->localeFormat->getNumber($preFinalPrice);
                        $amount = $this->calculator->getAmount($amount, $product, null)->getValue();
                        $jsonConfig['optionPrices'][$productId]['finalPrice']['amount'] = $amount;
                    }
                }
            }
        }

        return $this->jsonSerializer->serialize($jsonConfig);
    }
}
