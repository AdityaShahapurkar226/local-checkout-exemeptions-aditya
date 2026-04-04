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

use Magento\Backend\Block\Template;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;

class JsBlock extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Bss_ProductStockAlert::system/config/color_picker.phtml';

    /**
     * @var Random
     */
    protected $mathRandom;

    /**
     * JsBlock constructor.
     *
     * @param Template\Context $context
     * @param Random $mathRandom
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Random $mathRandom,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->mathRandom = $mathRandom;
    }

    /**
     * Get Additional Classes
     *
     * @return string
     * @throws LocalizedException
     */
    public function getAdditionalClasses()
    {
        return $this->mathRandom->getRandomString(4);
    }
}
