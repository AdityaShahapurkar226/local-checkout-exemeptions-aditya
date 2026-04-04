<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Controller\Cart;

use Amasty\Mostviewed\Block\Product\Popup;
use Amasty\Mostviewed\Model\Block\Renderer\Flag as RendererFlag;
use Amasty\Mostviewed\Model\Cart\Add\IsProductHasRequiredOptions;
use Amasty\Mostviewed\Model\Cart\Add\MessageManager as CustomMessageManager;
use Amasty\Mostviewed\Model\Cart\AddProductsByIds;
use Amasty\Mostviewed\Model\Cart\BundleResult;
use Amasty\Mostviewed\Model\ConfigProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product;
use Magento\Checkout\Helper\Cart;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\LocalizedToNormalized;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\View\LayoutInterface;
use Amasty\Mostviewed\Model\Block\Renderer\Flag;
use Magento\Framework\View\Result\PageFactory;

class Add extends \Magento\Checkout\Controller\Cart\Add
{
    /**
     * @var Product
     */
    private $productHelper;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Amasty\Mostviewed\Model\Cart\AddProductsByIds
     */
    private $addProductsByIds;

    /**
     * @var \Amasty\Mostviewed\Model\Di\Wrapper
     */
    private $generateAmastyCartResponse;

    /**
     * @var \Amasty\Mostviewed\Model\Pack\Cart\ProductRegistry
     */
    private $productRegistry;

    /**
     * @var \Amasty\Mostviewed\Model\Pack\Cart\GenerateConfirmMessage
     */
    private $generateConfirmMessage;

    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    private $dataObjectFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var Flag
     */
    private $rendererFlag;

    /**
     * @var CustomMessageManager
     */
    private $customMessageManager;

    /**
     * @var IsProductHasRequiredOptions
     */
    private $isProductHasRequiredOptions;

    /**
     * @var Cart
     */
    private $cartHelper;

    /**
     * @var LocalizedToNormalized
     */
    private $localizedToNormalized;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        Product $productHelper,
        PageFactory $resultPageFactory,
        UrlHelper $urlHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Amasty\Mostviewed\Model\Cart\AddProductsByIds $addProductsByIds,
        \Amasty\Mostviewed\Model\Di\Wrapper $generateAmastyCartResponse,
        \Amasty\Mostviewed\Model\Pack\Cart\ProductRegistry $productRegistry,
        \Amasty\Mostviewed\Model\Pack\Cart\GenerateConfirmMessage $generateConfirmMessage,
        \Magento\Framework\DataObject\Factory $dataObjectFactory,
        ConfigProvider $configProvider,
        RendererFlag $rendererFlag,
        CustomMessageManager $customMessageManager,
        IsProductHasRequiredOptions $isProductHasRequiredOptions,
        Cart $cartHelper,
        LocalizedToNormalized $localizedToNormalized = null
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $productRepository
        );
        $this->productHelper = $productHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->urlHelper = $urlHelper;
        $this->coreRegistry = $coreRegistry;
        $this->layoutFactory = $layoutFactory;
        $this->addProductsByIds = $addProductsByIds;
        $this->generateAmastyCartResponse = $generateAmastyCartResponse;
        $this->productRegistry = $productRegistry;
        $this->generateConfirmMessage = $generateConfirmMessage;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->configProvider = $configProvider;
        $this->rendererFlag = $rendererFlag;
        $this->customMessageManager = $customMessageManager;
        $this->isProductHasRequiredOptions = $isProductHasRequiredOptions;
        $this->cartHelper = $cartHelper;
        $this->localizedToNormalized = $localizedToNormalized ?? ObjectManager::getInstance()->get(
            LocalizedToNormalized::class
        );
    }

    /**
     * @return JsonResult|Redirect
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $message = __('We can\'t add these items to your shopping cart right now. Please reload the page.');
            return $this->generateAjaxResponse($message, false);
        }

        $productsInCart = $this->getRequest()->getParam('products_in_cart', []);
        $this->productRegistry->addProducts($productsInCart);

        $status = false;

        $popupProducts = $this->getRequest()->getParam('amrelated_products_popup', false);
        if ($popupProducts) {
            $messages = [];
            $countProducts = count($popupProducts);

            foreach ($popupProducts as $key => $popupProductParams) {
                // @codingStandardsIgnoreLine
                parse_str($popupProductParams, $params);
                $isLast = ($key == $countProducts - 1) ? true : false;
                [$message, $status] = $this->addProductWithParams($params, $isLast);

                if (!$status) {
                    return $this->generateAjaxResponse($message, $status);
                }

                $messages[] = $message;
            }
            $message = $messages;
        } else {
            $params = $this->getRequest()->getParams();
            [$message, $status] = $this->addProductWithParams($params, true);
        }

        if ($status && $this->isAjaxCartEnabled()) {
            $message = $this->generateAmastyCartResponse();
        }

        if ($message instanceof JsonResult) {
            return $message;
        }

        return $this->generateAjaxResponse($message, $status);
    }

    /**
     * @param $params
     * @param bool $isLast
     *
     * @return array|JsonResult
     */
    protected function addProductWithParams($params, $isLast)
    {
        try {
            if (isset($params['qty'])) {
                $this->localizedToNormalized->setOptions(['locale' => $this->_objectManager->get(
                    \Magento\Framework\Locale\ResolverInterface::class
                )->getLocale()]);
                $params['qty'] = $this->localizedToNormalized->filter($params['qty']);
            }

            $product = $this->initializeProduct(isset($params['product']) ? $params['product'] : null);
            $related = $this->getRequest()->getParam('amrelated_products', []);
            $packId = (int) $this->getRequest()->getParam('pack_id');

            $bundleResult = $this->addProductsByIds->execute($packId, $related);

            if ($product) {
                $request = $this->dataObjectFactory->create($params);
                if (!$this->configProvider->isProductsCanBeAddedSeparately()) {
                    $product->addCustomOption(AddProductsByIds::BUNDLE_PACK_ID_OPTION, $packId);
                    $product->addCustomOption(AddProductsByIds::BUNDLE_PACK_OPTION_CODE, true);
                }
                $quoteItem = $this->_checkoutSession->getQuote()->addProduct($product, $request);
                if (is_string($quoteItem)) {
                    $bundleResult->addSkippedProduct($product, $quoteItem);
                    $bundleResult->setHasRequiredOptions($this->isProductHasRequiredOptions->execute($product));
                } else {
                    $options = ['qty' => (float)($params['qty'] ?? 1)];
                    if ($quoteItem->getProduct()->getTypeId() === Configurable::TYPE_CODE
                        && $this->configProvider->isChildImageForConfigurable()
                        && $quoteItem->getChildren()
                    ) {
                        $options['child'] = $quoteItem->getChildren()[0]->getProduct()->getId();
                    }
                    $this->productRegistry->addProduct((int)$product->getId(), $options);
                }
            }

            if ($isLast) {
                $this->cart->save();
            }

            //should show popup for composite products
            if ($bundleResult->getSkippedProducts()) {
                return [$this->generatePopupResponse($bundleResult), false];
            } elseif ($this->isAjaxCartEnabled()) {
                return ['', true];
            } else {
                return ['addPackSuccessMessage', true];
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return [$e->getMessage(), false];
        } catch (\Exception $e) {
            $message = __('We can\'t add this item to your shopping cart right now.');

            return [$message, false];
        }
    }

    private function isAjaxCartEnabled(): bool
    {
        return $this->getRequest()->getParam('ajax_cart') && $this->generateAmastyCartResponse->isAvailable();
    }

    /**
     * @param $productId
     *
     * @return bool|ProductInterface
     * @throws NoSuchEntityException
     */
    private function getProductById($productId)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        try {
            return $this->productRepository->getById($productId, false, $storeId);
        } catch (NoSuchEntityException $e) {
            return $productId;
        }
    }

    protected function generatePopupResponse(BundleResult $bundleResult): JsonResult
    {
        $result = ['is_add_to_cart' => 0];
        $products = [];

        foreach ($bundleResult->getSkippedProducts() as $productId => $item) {
            $products[$productId] = [
                'html' => $this->generateOptionsForProduct($item['product']),
                'message' => $item['message'],
            ];
        }

        /** @var Popup $block */
        $block = $this->layoutFactory->create()->createBlock(Popup::class, 'amasty.mostviewed.popup', [
            'isAjaxCartEnabled' => $this->isAjaxCartEnabled()
        ]);

        if ($block) {
            $block->setHasRequiredOptions($bundleResult->isHasRequiredOptions());
            $block->setProducts($products);
            $block->setPackId($bundleResult->getPackId());
            $result['html'] = $block->toHtml();
        }

        $result = $this->prepareResult($result);
        /** @var JsonResult $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }

    private function generateAmastyCartResponse(): JsonResult
    {
        $this->generateAmastyCartResponse->setType(\Amasty\Cart\Model\Source\Section::CART);
        $result = $this->generateAmastyCartResponse->execute(
            $this->generateConfirmMessage->execute($this->productRegistry->getProducts()),
            ['is_add_to_cart' => 1]
        );
        $result = $this->prepareResult($result);
        /** @var JsonResult $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }

    /**
     * @param ProductInterface|int $product
     *
     * @return string
     * @throws NoSuchEntityException
     */
    protected function generateOptionsForProduct($product)
    {
        $html = '';

        if (!is_object($product)) {
            $product = $this->getProductById($product);
        }

        if (!is_object($product)) {
            return '';
        }

        $product->setCustomOptions([]); // fix error for bundle products
        /** @var ProductInterface $product */
        $this->coreRegistry->unregister('current_product');
        $this->coreRegistry->unregister('product');
        $this->coreRegistry->unregister('current_category');
        $this->productHelper->initProduct($product->getId(), $this);
        $page = $this->resultPageFactory->create(false, ['isIsolated' => true]);
        $page->addHandle('catalog_product_view');
        $page->addHandle('catalog_product_view_type_' . $product->getTypeId());
        $layout = $page->getLayout();

        // remove request a quote button
        $layout->unsetElement('product.info.addquote')
            ->unsetElement('product.info.addquote.additional');

        $block = $layout->createBlock(
            \Amasty\Mostviewed\Block\Product\MiniPage::class,
            'amasty.mostviewed.minipage',
            [
                'data' =>
                    [
                        'product'       => $product,
                        'loaded_layout' => $layout,
                        'optionsHtml'   => $this->generateOptionsHtml($product, $layout)
                    ]
            ]
        );

        if ($block) {
            $html = $block->toHtml();
        }

        return $html;
    }

    /**
     * Generate html for product options
     * @param ProductInterface $product
     * @param LayoutInterface $layout
     *
     * @return mixed|string
     */
    protected function generateOptionsHtml(ProductInterface $product, LayoutInterface $layout)
    {
        $block = $layout->getBlock('product.info');

        if (!$block) {
            $block = $layout->createBlock(
                \Magento\Catalog\Block\Product\View::class,
                'product.info',
                [ 'data' => [] ]
            );
        }

        $qty = $this->getRequest()->getParam('amrelated_products')[$product->getId()] ?? 1;
        if ($qty > 1) {
            $values = $product->getPreconfiguredValues();
            $values->setQty($qty);
            $product->setPreconfiguredValues($values);
        }

        $block->setProduct($product);
        $this->rendererFlag->enable();
        $html = $block->toHtml();
        $this->rendererFlag->disable();

        $html = str_replace(
            '"spConfig',
            '"priceHolderSelector": ".price-box[data-product-id=' . $product->getId() . ']", "spConfig',
            $html
        );
        $html = $this->replaceHtmlElements($html, $product);

        return $html;
    }

    /**
     * @param array|string $message
     * @param bool $status
     */
    protected function generateAjaxResponse($message, bool $status): JsonResult
    {
        $result = ['is_add_to_cart' => $status];

        if (!$status) {
            $this->messageManager->addErrorMessage($message);
            $result['error'] = true;
        } else {
            if ($this->cartHelper->getShouldRedirectToCart($this->_storeManager->getStore()->getId())) {
                $result['backUrl'] = $this->cartHelper->getCartUrl();
            }
            if (!is_array($message)) {
                $message = [$message];
            }
            $this->customMessageManager->execute($message);
        }

        $result = $this->prepareResult($result);
        /** @var JsonResult $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }

    /**
     * Add ability for creating plugin
     * @param $result
     *
     * @return mixed
     */
    public function prepareResult($result)
    {
        return $result;
    }

    /**
     * @param $html
     * @param ProductInterface $product
     *
     * @return mixed
     */
    private function replaceHtmlElements($html, ProductInterface $product)
    {
        /* replace uenc for correct redirect*/
        $currentUenc = $this->urlHelper->getEncodedUrl();
        $refererUrl = $product->getProductUrl();
        $newUenc = $this->urlHelper->getEncodedUrl($refererUrl);

        $container = '#amrelated-product-container-' . $product->getId();
        $priceHolderSelector = sprintf('%s [data-role=priceBox]', $container);

        $html = str_replace($currentUenc, $newUenc, $html);
        $html = str_replace('"swatch-opt"', '"swatch-opt swatch-opt-' . $product->getId() . '"', $html);
        $html = str_replace(
            'spConfig": {"attributes',
            'spConfig": {"containerId":"' . $container . '", "attributes',
            $html
        );
        $html = str_replace(
            '[data-role=swatch-options]',
            '' . $container . ' [data-role=swatch-options]',
            $html
        );
        $html = str_replace(
            '"jsonConfig":',
            '"selectorProduct":".amrelated-product-info","jsonConfig":',
            $html
        );

        // replace priceHolderSelector for custom options . needed for find correctly price box
        $html = preg_replace(
            '@"priceHolderSelector":\s*"[^"]*"@s',
            sprintf('"priceHolderSelector":"%s"', $priceHolderSelector),
            $html
        );

        $htmlIdsToReplace = [
            'id="product_addtocart_form',
            'id="product-addtocart-button',
            'id="qty',
            'for="qty',
            'id="related-products-field',
            'id="giftcard-amount-input',
            'id="giftcard-message',
            'id="giftcard_recipient_name',
            'id="giftcard_sender_name',
            '#product_addtocart_form'
        ];

        foreach ($htmlIdsToReplace as $elementId) {
            $html = str_replace(
                $elementId,
                $elementId . '_' . $product->getId(),
                $html
            );
        }

        return $html;
    }

    /**
     * @param null $productId
     *
     * @return bool|ProductInterface
     */
    protected function initializeProduct($productId = null)
    {
        if (!$productId) {
            $productId = (int)$this->getRequest()->getParam('product');
        }

        if ($productId) {
            $storeId = $this->_objectManager->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }

        return false;
    }
}
