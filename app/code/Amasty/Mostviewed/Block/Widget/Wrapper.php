<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Block\Widget;

class Wrapper extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleManager = $moduleManager;
    }

    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        $html = '';
        if ($this->moduleManager->isEnabled('Amasty_Mostviewed')) {
            $relateds = $this->getLayout()->createBlock(
                Related::class
            )->setData(
                'position',
                $this->getPosition()
            )->setTemplate($this->getTemplate());

            $html = $relateds->toHtml();
            $this->setTitle($relateds->getTitle() ?: __('Related Products')); //need for tab content position
        }

        return $html;
    }
}
