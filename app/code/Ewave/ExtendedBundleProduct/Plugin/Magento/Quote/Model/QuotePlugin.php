<?php
namespace Ewave\ExtendedBundleProduct\Plugin\Magento\Quote\Model;

use Magento\Catalog\Model\ProductRepository;
use Magento\Quote\Model\Quote as Subject;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Bundle\Model\Product\Price as BundlePrice;
use Magento\Bundle\Model\Selection;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\DataObject;

/**
 * Class QuotePlugin
 */
class QuotePlugin
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var BundlePrice
     */
    protected $bundlePrice;

    /**
     * QuotePlugin constructor.
     * @param ProductRepository $productRepository
     * @param BundlePrice $bundlePrice
     */
    public function __construct(
        ProductRepository $productRepository,
        BundlePrice $bundlePrice
    ) {
        $this->productRepository = $productRepository;
        $this->bundlePrice = $bundlePrice;
    }

    /**
     * @param Subject $subject
     * @param \Closure $proceed
     * @param Product $product
     * @param null|float|\Magento\Framework\DataObject $request
     * @param string $processMode
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundAddProduct(
        Subject $subject,
        \Closure $proceed,
        Product $product,
        $request = null,
        $processMode = AbstractType::PROCESS_MODE_FULL
    ) {
        $lastAddedItem = null;
        $product->setBuyRequest($request);
        if ($product->getTypeId() == Bundle::TYPE_CODE
            && $product->getIsSeparateCartItems()
            && $request instanceof DataObject
        ) {
            $bundleQty = max((int)$request->getQty(), 1);
            $bundleOptions = (array)$request->getBundleOption();
            $bundleOptionQty = (array)$request->getBundleOptionQty();

            if (!empty($bundleOptions)) {
                /** @var Bundle $bundleInstance */
                /** @var Selection|Product[] $selections */
                $bundleInstance = $product->getTypeInstance();
                $selections = $bundleInstance->getSelectionsCollection(array_keys($bundleOptions), $product);

                foreach ($selections as $selection) {
                    if ($this->isSelectionSent($selection, $bundleOptions)) {
                        $selectionProduct = $this->productRepository->getById(
                            $selection->getProductId(),
                            false,
                            $subject->getStoreId(),
                            true
                        );
                        $selectionProduct->setSelectionId($selection->getSelectionId());

                        if (isset($bundleOptionQty[$selection->getOptionId()])) {
                            $selectionQty = max((int)$bundleOptionQty[$selection->getOptionId()], 1);
                        } else {
                            $selectionQty = max($selection->getSelectionQty(), 1);
                        }

                        $request->setQty($bundleQty * $selectionQty);

                        if ($selection->getSelectionPriceType()) {
                            $customPrice = $this->bundlePrice->getSelectionFinalTotalPrice(
                                $product,
                                $selectionProduct,
                                $bundleQty,
                                $selectionQty,
                                false
                            );
                            $request->setCustomPrice($customPrice);
                        }
                        $lastAddedItem = $subject->addProduct($selectionProduct, $request);
                    }
                }
            }
        }

        if ($lastAddedItem !== null) {
            return $lastAddedItem;
        }

        return $proceed($product, $request, $processMode);
    }

    /**
     * @param Product $selection
     * @param array $bundleOptions
     * @return bool
     */
    protected function isSelectionSent(Product $selection, array $bundleOptions)
    {
        return in_array($selection->getSelectionId(), $bundleOptions)
            || (
                isset($bundleOptions[$selection->getOptionId()])
                && in_array($selection->getSelectionId(), (array)$bundleOptions[$selection->getOptionId()])
            );
    }
}
