<?php

namespace Ahy\ThemeCustomization\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Hyva\Theme\Model\ViewModelRegistry;
use Magento\Swatches\Block\Product\Renderer\Configurable;
use Magento\Framework\Registry;
use Magento\Catalog\Block\Product\Price;
use Magento\Catalog\Block\Product\View\Gallery;


class GetProductSwatchesDetails extends Action
{
    /**
    * @var Configurable
    */
   private $configurableBlock;
    /**
    * @var Price
    */
   private $_productPrice;
    /**
    * @var Gallery
    */
   private $_productSwatchesGallery;

   /**
    * @var Escaper
    */
   private $escaper;

   /**
    * @var ViewModelRegistry
    */
   private $viewModelRegistry;

   /**
    * @var LayoutFactory
    */
   private $layoutFactory;

   protected $resultJsonFactory;

   protected $registry;

   public function __construct(
        Context $context,
        Configurable $configurableBlock,
        Escaper $escaper,
        ViewModelRegistry $viewModelRegistry,
        LayoutFactory $layoutFactory,
        JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        Registry $registry,
        Price $ProductPrice,
        Gallery $Gallery
   ) {
       parent::__construct($context);
       $this->configurableBlock = $configurableBlock;
       $this->escaper = $escaper;
       $this->viewModelRegistry = $viewModelRegistry;
       $this->layoutFactory = $layoutFactory;
       $this->resultJsonFactory = $resultJsonFactory;
       $this->productFactory = $productFactory;
       $this->registry = $registry;
       $this->_productPrice = $ProductPrice;
       $this->_productSwatchesGallery = $Gallery;
   }

   public function execute(){
        $productId = $this->getRequest()->getParam('product_id');
        $product = $this->productFactory->create()->load($productId);
        $result = $this->resultJsonFactory->create();

        // if($product->getNoLongerAvailable() !== NULL && $product->getNoLongerAvailable()){
        //     $result->setData([
        //         'success' => false,
        //         'message' => '<span class="swatch-unavailable message error">Product Unavailable</span>'
        //     ]);
        //
        //     return $result;
        // }

        $isSalable = $product->getTypeInstance()->isSalable($product);

        if($isSalable && $product->getTypeId() == 'configurable'){
            // Set the current product in the registry
            $this->registry->register('current_product', $product);

            $layout = $this->layoutFactory->create();

            // Load the layout XML file
            $layout->getUpdate()->load('catalog_product_view');
            $layout->generateXml()->generateElements();

            $blockConfigurableSwatch = $layout->createBlock(Configurable::class);
            $blockConfigurableSwatch->setProduct($product);
            $swatchContentHtml = $blockConfigurableSwatch->toHtml();

            $productPriceBlock = $layout->createBlock(Price::class);
            $productPriceBlock->setTemplate('Magento_Catalog::product/view/price.phtml');
            $productPriceBlock->setProduct($product);
            $productPriceHtml = $productPriceBlock->toHtml();

            $productSwatchesGallery = $layout->createBlock(Gallery::class);
            $productSwatchesGallery->setTemplate('Magento_Catalog::product/view/gallery.phtml');
            $productSwatchesGallery->setProduct($product);
            $productSwatchesGalleryHtml = $productSwatchesGallery->toHtml();

            //$productPriceHtmlContent = $productPriceHtml->toHtml();
            //
            // $attributes = $blockConfigurableSwatch->decorateArray($blockConfigurableSwatch->getAllowAttributes());
            //
            // $tooltipBlockHtml = $blockConfigurableSwatch->getBlockHtml('product.swatch.tooltip');
            //
            // $jsonConfig = $blockConfigurableSwatch->getJsonConfig();
            // $getJsonSwatchConfig = $blockConfigurableSwatch->getJsonSwatchConfig();

            // Unset the current product from the registry to prevent side effects
            $this->registry->unregister('current_product');

            $string = '@private-content-loaded.window="onGetCartData($event.detail.data)"';
            // $swatchContentHtml = str_replace('@private-content-loaded.window="onGetCartData($event.detail.data)"', '', $swatchContentHtml);

            // Remove all HTML comments
            $swatchContentHtml = preg_replace('/<!--(.|\s)*?-->/', '', $swatchContentHtml);
            $productPriceHtml = preg_replace('/<!--(.|\s)*?-->/', '', $productPriceHtml);
            $productSwatchesGalleryHtml = preg_replace('/<!--(.|\s)*?-->/', '', $productSwatchesGalleryHtml);
            // Find all script tags and get their content
            preg_match_all('/<script.*?>(.*?)<\/script>/s', $swatchContentHtml, $matches);
            preg_match_all('/<script.*?>(.*?)<\/script>/s', $productPriceHtml, $matches1);
            preg_match_all('/<script.*?>(.*?)<\/script>/s', $productSwatchesGalleryHtml, $matches2);
            $scriptContent = implode('', $matches[1]);
            $productPriceScript = implode('', $matches1[1]);
            $productSwatchesGalleryScript = implode('', $matches2[1]);

            // Remove script tags and their content from the HTML
            $swatchContentHtml = preg_replace('/<script.*?>.*?<\/script>/s', '', $swatchContentHtml);
            $productPriceHtml = preg_replace('/<script.*?>.*?<\/script>/s', '', $productPriceHtml);
            $productSwatchesGalleryHtml = preg_replace('/<script.*?>.*?<\/script>/s', '', $productSwatchesGalleryHtml);
            $productSwatchesGalleryHtml = preg_replace('/<style.*?>.*?<\/style>/s', '', $productSwatchesGalleryHtml);

            $result->setData([
                'swatch_html' => $swatchContentHtml,
                'swatch_script' => $scriptContent,
                'success' => true,
                'product_price_html' => $productPriceHtml,
                'product_price_script' => $productPriceScript,
                'product_swatch_gallery_html' => $productSwatchesGalleryHtml,
                'product_swatch_gallery_script' => $productSwatchesGalleryScript,
            ]);

        } else {
            $this->registry->register('current_product', $product);

            $layout = $this->layoutFactory->create();

            // Load the layout XML file
            $layout->getUpdate()->load('catalog_product_view');
            $layout->generateXml()->generateElements();

            $productSwatchesGallery = $layout->createBlock(Gallery::class);
            $productSwatchesGallery->setTemplate('Magento_Catalog::product/view/gallery.phtml');
            $productSwatchesGallery->setProduct($product);
            $productSwatchesGalleryHtml = $productSwatchesGallery->toHtml();

            $this->registry->unregister('current_product');

            $productSwatchesGalleryHtml = preg_replace('/<!--(.|\s)*?-->/', '', $productSwatchesGalleryHtml);

            preg_match_all('/<script.*?>(.*?)<\/script>/s', $productSwatchesGalleryHtml, $matches2);

            $productSwatchesGalleryScript = implode('', $matches2[1]);

            $productSwatchesGalleryHtml = preg_replace('/<script.*?>.*?<\/script>/s', '', $productSwatchesGalleryHtml);
            $productSwatchesGalleryHtml = preg_replace('/<style.*?>.*?<\/style>/s', '', $productSwatchesGalleryHtml);

            $result->setData([
                'success' => false,
                'message' => '<span class="swatch-unavailable message error">Product Unavailable</span>',
                'product_swatch_gallery_html' => $productSwatchesGalleryHtml,
                'product_swatch_gallery_script' => $productSwatchesGalleryScript,
            ]);

        }

        return $result;
    }

}
