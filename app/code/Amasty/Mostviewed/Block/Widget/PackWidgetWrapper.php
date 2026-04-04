<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Block\Widget;

use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;

class PackWidgetWrapper extends Template implements BlockInterface
{
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(
        ModuleManager $moduleManager,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return string
     */
    public function _toHtml(): string
    {
        $html = '';
        if ($this->moduleManager->isEnabled('Amasty_Mostviewed')) {
            $originalBlock = $this->getLayout()->createBlock(
                Pack::class,
                '',
                ['data' => $this->getData()]
            );
            $originalBlock->setTemplate($this->getTemplate());

            $html = $originalBlock->toHtml();
        }

        return $html;
    }
}
