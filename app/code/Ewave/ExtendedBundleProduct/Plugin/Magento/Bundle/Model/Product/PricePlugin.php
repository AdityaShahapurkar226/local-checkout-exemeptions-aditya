<?php
namespace Ewave\ExtendedBundleProduct\Plugin\Magento\Bundle\Model\Product;

use Magento\Bundle\Model\Product\Price as Subject;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class StockStateProviderPlugin
 */
class PricePlugin
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * PricePlugin constructor.
     * @param ProductRepository $productRepository
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        ProductRepository $productRepository,
        ManagerInterface $eventManager
    ) {
        $this->productRepository = $productRepository;
        $this->eventManager = $eventManager;
    }

    /**
     * @param Subject $subject
     * @param \Magento\Catalog\Model\Product $bundleProduct
     * @param \Magento\Catalog\Model\Product $selectionProduct
     * @param float $bundleQty
     * @param float $selectionQty
     * @param bool $multiplyQty
     * @param bool $takeTierPrice
     * @return array
     */
    public function beforeGetSelectionFinalTotalPrice(
        Subject $subject,
        $bundleProduct,
        $selectionProduct,
        $bundleQty,
        $selectionQty,
        $multiplyQty = true,
        $takeTierPrice = true
    ) {
        if ($bundleProduct->getPriceType() == Subject::PRICE_TYPE_DYNAMIC
            && $selectionProduct->getTypeId() == Configurable::TYPE_CODE
        ) {
            /** @var DataObject $request */
            $request = $bundleProduct->getBuyRequest();
            if ($request instanceof DataObject) {
                $configurableOptions = (array)$request->getSelectionConfigurableOption();
                if (isset($configurableOptions[$selectionProduct->getSelectionId()])) {
                    $simpleProductId = $configurableOptions[$selectionProduct->getSelectionId()];
                    $selectionProduct = $this->productRepository->getById($simpleProductId);
                }
            }

            $this->eventManager->dispatch(
                'catalog_product_get_final_price',
                ['product' => $selectionProduct, 'qty' => $bundleQty]
            );
        }

        return [
            $bundleProduct,
            $selectionProduct,
            $bundleQty,
            $selectionQty,
            $multiplyQty,
            $takeTierPrice
        ];
    }
}
