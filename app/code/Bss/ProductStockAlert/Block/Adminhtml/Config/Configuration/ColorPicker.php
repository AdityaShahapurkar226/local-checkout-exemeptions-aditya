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
namespace Bss\ProductStockAlert\Block\Adminhtml\Config\Configuration;

use Magento\Framework\Data\Form\Element\Text as ElementText;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;

class ColorPicker extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var ElementText
     */
    public $text;

    /**
     * ColorPicker constructor.
     *
     * @param Context $context
     * @param ElementText $text
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Text $text
    ) {
        $this->text = $text;
        parent::__construct($context);
    }

    /**
     * Get Element HTML
     *
     * @param AbstractElement $element
     * @return string
     * @throws LocalizedException
     */
    protected function _getElementHtml(
        \Magento\Framework\Data\Form\Element\AbstractElement $element
    ) {
        $input = $this->text;

        $input->setForm($element->getForm())
            ->setElement($element)
            ->setValue($element->getValue())
            ->setHtmlId($element->getHtmlId())
            ->setClass('bss-colpicker')
            ->setName($element->getName());
        $html = $input->getHtml();
        $html .= $this->getLayout()
            ->createBlock(\Bss\ProductStockAlert\Block\Adminhtml\Config\Configuration\JsBlock::class)
            ->toHtml();

        return $html;
    }
}
