<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Block;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $helper;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Prepare layout.
     *
     * @return this
     */
    public function _prepareLayout()
    {
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle(__('Mass Upload'));
        }
        return parent::_prepareLayout();
    }

    /**
     * Get Sample Csv File Urls.
     *
     * @return array
     */
    public function getSampleCsv()
    {
        return $this->helper->getSampleCsv();
    }

    /**
     * Get Sample XLS File Urls.
     *
     * @return array
     */
    public function getSampleXls()
    {
        return $this->helper->getSampleXls();
    }

    /**
     * Get Profiles
     *
     * @return array
     */
    public function getProfiles()
    {
        return $this->helper->getProfiles();
    }
}
