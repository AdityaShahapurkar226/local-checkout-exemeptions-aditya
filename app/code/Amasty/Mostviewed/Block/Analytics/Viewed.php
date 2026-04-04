<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Block\Analytics;

use Magento\Framework\View\Element\Template;

class Viewed extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Mostviewed::analytics/viewed.phtml';

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    public function __construct(
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @return string
     */
    public function getViewedUrl()
    {
        return $this->getUrl('ammostviewed/analytics/view');
    }

    /**
     * @return string
     */
    public function getClickUrl()
    {
        return $this->getUrl('ammostviewed/analytics/click');
    }

    /**
     * @return string
     */
    public function getBlockId()
    {
        return $this->_data['block_id'];
    }

    /**
     * @return string
     */
    public function getProductsFilter()
    {
        return $this->jsonEncoder->encode($this->_data['products_filter']);
    }

    /**
     * @return string
     */
    public function getBlockSelector()
    {
        $selector = '.block.' . $this->_data['block_type'];
        if ($this->_data['products_filter'] === null) {
            $selector = '#amrelated-block-' . $this->getBlockId();
        }

        return $selector;
    }
}
