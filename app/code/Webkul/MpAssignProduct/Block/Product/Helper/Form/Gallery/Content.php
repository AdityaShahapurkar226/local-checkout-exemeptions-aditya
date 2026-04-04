<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAssignProduct\Block\Product\Helper\Form\Gallery;

use Magento\Catalog\Model\Product;

class Content extends \Webkul\Marketplace\Block\Product\Helper\Form\Gallery\Content
{
    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
   /**
    * Initialization
    *
    * @param \Magento\Framework\View\Element\Template\Context $context
    * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
    * @param \Magento\Framework\File\Size $fileSize
    * @param \Magento\Framework\Json\EncoderInterface $jsonEncoderInterface
    * @param \Magento\Framework\Registry $coreRegistry
    * @param Product $product
    * @param \Webkul\MpAssignProduct\Helper\Data $helper
    * @param \Magento\Store\Model\StoreManagerInterface $storeManager
    * @param \Magento\Framework\Json\Helper\Data $jsonHelper
    * @param array $data
    */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\File\Size $fileSize,
        \Magento\Framework\Json\EncoderInterface $jsonEncoderInterface,
        \Magento\Framework\Registry $coreRegistry,
        Product $product,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $mediaConfig,
            $fileSize,
            $jsonEncoderInterface,
            $coreRegistry,
            $product,
            $data
        );
        $this->helper = $helper;
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
    }
    /**
     * Retrieve product.
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $currentUrl = $this->storeManager->getStore()->getCurrentUrl(false);
        if (strpos($currentUrl, 'mpassignproduct/product/edit') !== false) {
            $helper = $this->helper;
            $assignId = $helper->getProductId();
            $assignData = $helper->getAssignDataByAssignId($assignId);
            $assignProductId = $assignData->getAssignProductId();
            $assignProduct = $helper->getProduct($assignProductId);
            return $assignProduct;
        }
        return parent::getProduct();
    }

    /**
     * Get product image data.
     *
     * @return array
     */
    public function getProductImagesJson()
    {
        $currentUrl = $this->storeManager->getStore()->getCurrentUrl(false);
        if (strpos($currentUrl, 'mpassignproduct/product/edit') !== false) {
            $productColl = $this->getProduct();
            $mediaGalleryImages = $productColl->getMediaGalleryImages();
            $productImages = [];
            if (count($mediaGalleryImages) > 0) {
                foreach ($mediaGalleryImages as &$mediaGalleryImage) {
                    $mediaGalleryImage['url'] = $this->_mediaConfig->getMediaUrl(
                        $mediaGalleryImage['file']
                    );
                    array_push($productImages, $mediaGalleryImage->getData());
                }

                return $this->_jsonEncoderInterface->encode($productImages);
            }
        }
        return '[]';
    }

    /**
     * Get product image type
     *
     * @return array
     */
    public function getProductImageTypes()
    {
        $productImageTypes = [];
        $productColl = $this->getProduct();
        foreach ($this->getProductMediaAttributes() as $attribute) {
            $productImageTypes[$attribute->getAttributeCode()] = [
              'code' => $attribute->getAttributeCode(),
              'value' => isset($productColl[$attribute->getAttributeCode()])
              ? ($productColl[$attribute->getAttributeCode()]) : 0,
              'label' => $attribute->getFrontend()->getLabel(),
              'name' => 'product['.$attribute->getAttributeCode().']',
            ];
        }

        return $productImageTypes;
    }

    /**
     * GetAllowedMediaAttributes returns the allowed media attributes
     *
     * @return array
     */
    public function getAllowedMediaAttributes()
    {
        return ['image', 'small_image', 'thumbnail'];
    }
    /**
     * GetJsonHelper function
     *
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
    /**
     * ProductGalleryUrl function
     *
     * @return string
     */
    public function productGalleryUrl()
    {
        return $this->getUrl(
            'marketplace/product_gallery/upload',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
}
