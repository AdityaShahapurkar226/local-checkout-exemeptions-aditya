<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Controller\Upload;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

class Profile extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\MpAssignProduct\Model\ProfileFactory $profileFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        \Webkul\MpAssignProduct\Controller\Product\Save $saveConstroller,
        \Webkul\MpAssignProduct\Model\ItemsFactory $assignItems
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_url = $url;
        $this->_session = $session;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->profileFactory  = $profileFactory;
        $this->jsonHelper = $jsonHelper;
        $this->productRepository = $productRepository;
        $this->mpProductFactory = $mpProductFactory;
        $this->saveController = $saveConstroller;
        $this->assignItemsFactory = $assignItems;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Run action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        if ($this->marketplaceHelper->getIsSeparatePanel()) {
            $resultPage->addHandle('mpassignproduct_upload_profile_layout2');
        }
        try {
            $params = $this->getRequest()->getParams();
            
            $profileId = $params['profile'];
            if ($profileId == '' || !isset($profileId)) {
                $this->messageManager->addError(__('Profile doesn\'t exists.'));
                return $resultRedirect->setPath('*/*/', ['_secure'=>$this->getRequest()->isSecure()]);
            }
            $profileData = $this->profileFactory->create()->getCollection()
            ->addFieldToFilter('entity_id', $profileId)->getFirstItem();
            if (!empty($profileData)) {
                $data = $this->jsonHelper->jsonDecode($profileData->getDataRow());
                $header = $data[0];
                $this->validateData($data);
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('*/*/view');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }
        
        $resultPage->getConfig()->getTitle()->set(__('Marketplace Mass Upload'));
        return $resultPage;
    }

    public function validateData($data)
    {
        $vendorAttributeCode = [];
        $requiredKeys = [
            'Sku',
            'Product Condition',
            'Price',
            'Quantity',
            'Description',
            'Product Images',
            'Product Type',
            'Parent Product Sku'
        ];

        foreach ($data[0] as $key => $val) {
            if (!in_array($val, $requiredKeys)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Something wrong with the uploaded file format")
                );
            }
        }
    }
}
