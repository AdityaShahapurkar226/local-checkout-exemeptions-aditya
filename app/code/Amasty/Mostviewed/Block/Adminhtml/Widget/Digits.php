<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Block\Adminhtml\Widget;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\Data\Form\Element\Text;

class Digits extends Template
{
    /**
     * @var ElementFactory
     */
    private $elementFactory;

    public function __construct(
        Context $context,
        ElementFactory $elementFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->elementFactory = $elementFactory;
    }

    /**
     * @param AbstractElement $element
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element): AbstractElement
    {
        /** @var Text $input */
        $input = $this->elementFactory->create('text', ['data' => $element->getData()]);
        $input->setName($element->getName());
        $input->setId($element->getId());
        $input->setForm($element->getForm());
        if ($element->getRequired()) {
            $input->addClass('required-entry');
        }
        $input->addClass('validate-digits validate-greater-than-zero');
        $html = $input->getElementHtml();

        $element->setData('after_element_html', $html);
        $element->setValue('');

        return $element;
    }
}
