<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ProductStockAlert\Controller\Ajax;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatus;
use Magento\Framework\App\ActionInterface;

class FormData extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface
{

    const JSON_DATA_CONFIG_DISPATCHER = 'json_data_config_dispatcher';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var EncoderInterface
     */
    protected $encoder;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var StockStatus
     */
    protected $stockStatusResource;

    /**
     * @var \Bss\ProductStockAlert\Helper\MultiSourceInventory
     */
    protected $multiSourceInventoryHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * FormData constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Bss\ProductStockAlert\Helper\Data $helperData
     * @param \Magento\Framework\Registry $coreRegistry
     * @param DataObjectFactory $dataObjectFactory
     * @param EncoderInterface $encoder
     * @param FormKey $formKey
     * @param StockStatus $stockStatus
     * @param \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventoryHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Bss\ProductStockAlert\Helper\Data $helperData,
        \Magento\Framework\Registry $coreRegistry,
        DataObjectFactory $dataObjectFactory,
        EncoderInterface $encoder,
        FormKey $formKey,
        StockStatus $stockStatus,
        \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventoryHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
        $this->helperData = $helperData;
        $this->coreRegistry = $coreRegistry;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->encoder = $encoder;
        $this->formKey = $formKey;
        $this->stockStatusResource = $stockStatus;
        $this->multiSourceInventoryHelper = $multiSourceInventoryHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $productId = $this->getRequest()->getParam('product_id');
            if (!$productId) {
                return $this->buildErrorResponse();
            }
            $product = null;
            if ($productId) {
                $product = $this->productRepository->getById($productId);
            } else {
                // Try get product by registry
                $product = $this->coreRegistry->registry('current_product');
            }
            if (!$product || !$product->getId()) {
                return $this->buildErrorResponse();
            }
            $data = $this->getFormData($product);
            return $this->resultJsonFactory->create()
                ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true)
                ->setData($data);
        } catch (\Exception $e) {
            return $this->buildErrorResponse();
        }
    }

    /**
     * @param Product $product
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFormData($product)
    {
        $data = [];
        $formKey = $this->formKey->getFormKey();
        $productType = $product->getTypeId();

        $stockResolver = $this->multiSourceInventoryHelper->getStockResolverObject();
        $salableQty = $this->multiSourceInventoryHelper->getSalableQtyObject();
        $stockId = $this->getStockId($stockResolver, $salableQty);

        if ($this->checkProductType($productType, 'simple')) {
            $stockItem = $product->getExtensionAttributes()->getStockItem();
            $isInStock = $this->isChildInStock(
                $product->getSku(),
                $stockItem->getIsInStock(),
                $stockId,
                $salableQty
            );
            if (!$isInStock && $this->isProductEnableNotice($product)) {
                $data = $this->createSimpleRender($product, $formKey);
            }
        } elseif ($this->checkProductType($productType, 'configurable')) {
            $data = $this->createConfigurableRender($product, $formKey, $salableQty, $stockId);
        } elseif ($this->checkProductType($productType, 'grouped')) {
            $data = $this->createGroupedRender($product, $formKey, $salableQty, $stockId);
        } elseif ($this->checkProductType($productType, 'bundle')) {
            $data = $this->createBundleRender($product, $formKey, $salableQty, $stockId);
        }
        $formDataObject = $this->dataObjectFactory->create()->setData($data);
        $this->_eventManager->dispatch(self::JSON_DATA_CONFIG_DISPATCHER, ['data' => $formDataObject]);
        return $formDataObject->getData();
    }

    /**
     * @param \Magento\InventorySalesApi\Api\StockResolverInterface|null $stockResolver
     * @param \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface|null $salableQty
     * @return int|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getStockId(
        $stockResolver,
        $salableQty
    ) {
        try {
            $websiteId = $this->storeManager->getWebsite()->getCode();
            if ($stockResolver && $stockResolver instanceof \Magento\InventorySalesApi\Api\StockResolverInterface &&
                $salableQty && $salableQty instanceof \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface) {
                return $stockResolver->execute('website', $websiteId)->getStockId();
            }
            return null;
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param string $sku
     * @param bool $childStock
     * @param int|null $stockId
     * @param \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface|null $salableQty
     * @param bool $salableTag
     * @return bool
     */
    private function isChildInStock(
        $sku,
        $childStock,
        $stockId,
        $salableQty
    ) {
        if (!$stockId) {
            return $childStock;
        }
        try {
            return $salableQty->execute($sku, (int)$stockId);
        } catch (\Exception $exception) {
            return 0;
        }
    }

    /**
     * @param Product $product
     * @param string $formKey
     * @param \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface|null $salableQty
     * @param int|null $stockId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function createConfigurableRender(
        $product,
        $formKey,
        $salableQty,
        $stockId
    ) {
        // If parent is not available
        // That means, parent is in stock
        // But all of child are out of stock
        $isAvailable = $product->isAvailable();
        if (!$isAvailable && $this->isProductEnableNotice($product)) {
            return $this->createSimpleRender($product, $formKey);
        }
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $childItems = $productTypeInstance->getUsedProductCollection($product);
        $childItems->addAttributeToSelect('product_stock_alert');
        $this->stockStatusResource->addStockDataToCollection($childItems, false);
        $skeleton = $this->createSkeletonResponse('configurable');
        $merger = [];
        foreach ($childItems as $childItem) {
            $isInStock = $this->isChildInStock(
                $childItem->getSku(),
                $childItem->getIsSalable(),
                $stockId,
                $salableQty
            );
            if (!$isInStock &&
                $this->isProductEnableNotice($childItem)) {
                $pid = $childItem['entity_id'];
                $parId = $product->getId();
                $merger[$pid] = $this->createSkeletonAdditional($pid, $parId, $formKey);
            }
        }

        return $this->addAdditionalDataResponse($skeleton, $merger);
    }

    /**
     * @param Product $product
     * @param string $formKey
     * @param \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface|null $salableQty
     * @param int|null $stockId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function createGroupedRender(
        $product,
        $formKey,
        $salableQty,
        $stockId
    ) {
        // If parent is not available
        // That means, parent is in stock
        // But all of child are out of stock
        $isAvailable = $product->isAvailable();
        if (!$isAvailable && $this->isProductEnableNotice($product)) {
            return $this->createSimpleRender($product, $formKey);
        }
        /** @var \Magento\GroupedProduct\Model\Product\Type\Grouped $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $childItems = $productTypeInstance->getAssociatedProductCollection($product);
        $childItems->addAttributeToSelect(
            'product_stock_alert'
        );
        $skeleton = $this->createSkeletonResponse('grouped');
        $merger = [];
        foreach ($childItems as $childItem) {
            $isInStock = $this->isChildInStock(
                $childItem->getSku(),
                $childItem->getIsSalable(),
                $stockId,
                $salableQty
            );
            if (!$childItem->getIsSalable() && $this->isProductEnableNotice($childItem)) {
                $pid = $childItem['entity_id'];
                $parId = $product->getId();
                $merger[$pid] = $this->createSkeletonAdditional($pid, $parId, $formKey);
            }
        }

        return $this->addAdditionalDataResponse($skeleton, $merger);
    }

    /**
     * @param Product $product
     * @param string $formKey
     * @param \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface|null $salableQty
     * @param int|null $stockId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function createBundleRender(
        $product,
        $formKey,
        $salableQty,
        $stockId
    ) {
        // If parent is not available
        // That means, parent is in stock
        // But all of child are out of stock
        $isAvailable = $product->isAvailable();
        if (!$isAvailable && $this->isProductEnableNotice($product)) {
            return $this->createSimpleRender($product, $formKey);
        }
        /** @var \Magento\Bundle\Model\Product\Type $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter(
            $product->getStoreId(),
            $product
        );

        $selectionItems = $productTypeInstance->getSelectionsCollection(
            $productTypeInstance->getOptionsIds($product),
            $product
        )->addFieldToSelect(
            'product_id'
        )->addFieldToSelect(
            'option_id'
        )->addFieldToSelect(
            'selection_id'
        )->addAttributeToSelect(
            'product_stock_alert'
        );
        $selectionItems->getSelect()->joinInner(
            ['bundleOption' => $selectionItems->getTable('catalog_product_bundle_option')],
            'selection.option_id = bundleOption.option_id',
            ['type']
        );
        $skeleton = $this->createSkeletonResponse('bundle');
        $merger = [];
        foreach ($selectionItems as $childItem) {
            $isInStock = $this->isChildInStock(
                $childItem->getSku(),
                $childItem->getIsSalable(),
                $stockId,
                $salableQty
            );
            if (!$childItem->getIsSalable() && $this->isProductEnableNotice($childItem)) {
                $pid = $childItem->getProductId();
                $parId = $product->getId();
                $merger[$pid] = $this->createSkeletonAdditional($pid, $parId, $formKey);
                $merger[$pid]['option_id'] = $childItem->getOptionId();
                $merger[$pid]['selection_id'] = $childItem->getSelectionId();
                $merger[$pid]['option_type'] = $childItem->getType();
            }
        }
        return $this->addAdditionalDataResponse($skeleton, $merger);
    }

    /**
     * @param Product $product
     * @param string $formKey
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function createSimpleRender($product, $formKey)
    {
        $skeleton = $this->createSkeletonResponse('simple');
        $pid = $product->getId();
        $additionalData =  [
            $pid => $this->createSkeletonAdditional($pid, $pid, $formKey)
        ];
        return $this->addAdditionalDataResponse($skeleton, $additionalData);
    }

    /**
     * @param string $type
     * @param string $compareType
     * @return bool
     */
    private function checkProductType($type, $compareType)
    {
        if ($compareType == "simple") {
            return in_array($type, ['simple', 'virtual', 'downloadable']);
        }
        return $type == $compareType;
    }

    /**
     * @param int $product_id
     * @return string
     */
    private function getCancelPostAction($productId, $parentId)
    {
        return $this->_url->getUrl(
            'productstockalert/unsubscribe/stock',
            [
                'product_id' => $productId,
                'parent_id' => $parentId,
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl(
                    $productId,
                    'productstockalert/unsubscribe/stock'
                )
            ]
        );
    }

    /**
     * @param string $type
     * @return string
     */
    private function getAddPostAction($productId, $parentId)
    {
        return $this->_url->getUrl(
            'productstockalert/add/stock',
            [
                'product_id' => $productId,
                'parent_id' => $parentId,
                ActionInterface::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl(
                    $productId,
                    'productstockalert/add/stock'
                )
            ]
        );
    }

    /**
     * @param int $pid
     * @param string $path
     * @param string|null $url
     * @return string
     */
    private function getEncodedUrl($pid, $path, $url = null)
    {
        if (!$url) {
            $url = $this->_url->getUrl(
                $path,
                [
                    'product_id' => $pid
                ]
            );
        }
        return $this->encoder->encode($url);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function buildErrorResponse()
    {
        return $this->resultJsonFactory->create()->setData([
            '_reload' => 1,
            '_error' => 1
        ]);
    }

    /**
     * @param array $skeletonResponse
     * @param array $additionalData
     * @return array
     */
    private function addAdditionalDataResponse($skeletonResponse, $additionalData)
    {
        $skeletonResponse['product_data'] = $additionalData;
        return $skeletonResponse;
    }

    /**
     * @param int $pid
     * @param int $parId
     * @param string $formKey
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function createSkeletonAdditional($pid, $parId, $formKey)
    {
        return [
            'has_email' => $this->helperData->hasEmail($pid),
            'available' => 0,
            'form_action' => $this->getAddPostAction($pid, $parId),
            'form_key' => $formKey,
            'product_id' => $pid,
            'parent_id' => $parId,
            'form_action_cancel' => $this->getCancelPostAction($pid, $parId)
        ];
    }

    /**
     * @param $type
     * @return array
     */
    private function createSkeletonResponse($type)
    {
        return [
            'title' => $this->helperData->getNotificationMessage(),
            'label' => '',
            'button_text' => $this->helperData->getButtonText(),
            'stop_button_text' => $this->helperData->getStopButtonText(),
            'button_style' => $this->helperData->getButtonColor(),
            'button_text_color' => $this->helperData->getButtonTextColor(),
            'title_cancel' => $this->helperData->getStopNotificationMessage(),
            'button_text_cancel' => $this->helperData->getStopButtonText(),
            'has_options' => 1,
            'type' => $type
        ];
    }

    /**
     * @param Product|array $product
     * @return bool
     */
    private function isProductEnableNotice($product)
    {
        if (is_array($product)) {
            if (isset($product['product_stock_alert'])) {
                $alertAttr = $product['product_stock_alert'];
                return (int)$alertAttr == 1 || !$alertAttr;
            }
            return false;
        }
        return (int)$product->getProductStockAlert() == 1 || !$product->getProductStockAlert();
    }
}
