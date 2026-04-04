<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMassUpload\Block\ProfileListing;

use Magento\Framework\UrlInterface;

class ProfileList extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_massUploadHelper;
    
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Webkul\MpMassUpload\Api\ProfileRepositoryInterface
     */
    protected $_profileRepository;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\MpMassUpload\Helper\Data $massUploadHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\MpMassUpload\Api\ProfileRepositoryInterface $ProfileRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpMassUpload\Helper\Data $massUploadHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpMassUpload\Api\ProfileRepositoryInterface $ProfileRepository,
        array $data = []
    ) {
        $this->_storeManager = $context->getStoreManager();
        $this->_massUploadHelper = $massUploadHelper;
        $this->_jsonHelper = $jsonHelper;
        $this->_customerSession = $customerSession;
        $this->_profileRepository = $ProfileRepository;
        parent::__construct($context, $data);
    }

    /**
     * Prepare layout.
     *
     * @return $this
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
     * Return the Customer seller status.
     *
     * @return bool|0|1
     */
    public function isSeller()
    {
        return $this->_massUploadHelper->isSeller();
    }

    /**
     * Uploaded Profile Name
     *
     * @return array
     */
    public function uploadedProfileName()
    {
        $customerId = $this->_customerSession->getCustomerId();
        if (!($customerId)) {
            return false;
        }
        $profileCollection = $this->_profileRepository->getBySellerId($customerId);
        $profileListArray = [];
        foreach ($profileCollection as $data) {
            $profileListArray[$data->getId()] = $data->getProfileName();
            
        }
        return $profileListArray;
    }
}
