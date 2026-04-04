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
namespace Webkul\MpAssignProduct\Block\Adminhtml\Upload;

class View extends \Magento\Backend\Block\Template
{
    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $helper;
    
    /**
     * Initialization
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Webkul\MpAssignProduct\Model\Config\Source\Sellerlist $sellerlist
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Webkul\MpAssignProduct\Model\Config\Source\Sellerlist $sellerlist,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->sellerlist = $sellerlist;
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
    /**
     * Get Seller list
     *
     * @return void
     */
    public function getSellerList()
    {
        return $this->sellerlist->toOptionArray();
    }
}
