<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Helper;

use Amasty\Mostviewed\Block\Product\BundlePackCustom;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Layout;

class Render extends AbstractHelper
{
    /**
     * @var Layout
     */
    private $layout;

    public function __construct(
        Layout $layout,
        Context $context
    ) {
        parent::__construct($context);
        $this->layout = $layout;
    }

    /**
     * @return string
     */
    public function renderCurrentPack()
    {
        return $this->layout->createBlock(BundlePackCustom::class)->toHtml();
    }
}
