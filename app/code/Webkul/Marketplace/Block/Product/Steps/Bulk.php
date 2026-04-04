<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/**
 * Marketplace block for fieldset of configurable product.
 */

namespace Webkul\Marketplace\Block\Product\Steps;

use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Catalog\Model\ProductFactory;

class Bulk extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\VariationMediaAttributes
     */
    protected $_mediaConfig;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_helperImage;

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param MediaConfig $mediaConfig
     * @param ProductFactory $productFactory
     * @param \Magento\Catalog\Helper\Image $helperImage
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        MediaConfig $mediaConfig,
        ProductFactory $productFactory,
        \Magento\Catalog\Helper\Image $helperImage
    ) {
        parent::__construct($context);
        $this->_mediaConfig = $mediaConfig;
        $this->_productFactory = $productFactory;
        $this->_helperImage = $helperImage;
    }

    /**
     * Get caption
     *
     * @return string
     */
    public function getCaption()
    {
        return __('Bulk Images &amp; Price');
    }

    /**
     * Get conf media attribute
     *
     * @return void
     */
    public function getConfMediaAttributes()
    {
        static $simple;
        if (empty($simple)) {
            $simple = $this->_productFactory->create()->setTypeId(
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            )->getMediaAttributes();
        }

        return $simple;
    }

    /**
     * Get place holder image
     *
     * @return string
     */
    public function getPlaceholderImageUrl()
    {
        return $this->_helperImage->getDefaultPlaceholderUrl('thumbnail');
    }

    /**
     * Get attribute image types
     *
     * @return array
     */
    public function getAttributeImageTypes()
    {
        $attrImageTypes = [];
        foreach ($this->_mediaConfig->getMediaAttributeCodes() as $attributeCode) {
            $attrImageTypes[$attributeCode] = [
                'code' => $attributeCode,
                'value' => '',
                'label' => $attributeCode,
                'scope' => '',
                'name' => $attributeCode,
            ];
        }

        return $attrImageTypes;
    }
}
