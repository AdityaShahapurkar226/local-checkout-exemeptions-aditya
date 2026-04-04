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

class Run extends Action
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
        \Webkul\MpAssignProduct\Controller\Product\Save $saveConstroller,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_url = $url;
        $this->_session = $session;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->saveController = $saveConstroller;
        $this->jsonHelper = $jsonHelper;
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
        if (!empty($this->getRequest()->getPost())) {
            try {
                $sellerId = $this->marketplaceHelper->getCustomerId();
                $profileId = $this->getRequest()->getParam('profile_id');
                $wholeData = $this->getRequest()->getParams();
                if (isset($wholeData['row'])) {
                    $row = $wholeData['row'];
                    $res = $wholeData[$row];
                    if (!isset($res['error'])) {
                        $result = $this->saveController->saveAssignedProduct($res);
                    } else {
                        $result['error'] = 1;
                        $result['msg'] = $wholeData[$row]['error'];
                    }
                      
                    $result['next_row_data'] = $row + 1;
                } else {
                    $result['error'] = 1;
                    $result['msg'] = $wholeData['error'];
                }
                if (empty($result['error'])) {
                    $result['error'] = 0;
                }
                
                if ($result['error']) {
                    $result['msg'] = '<div class="wk-mu-error wk-mu-box">'.$result['msg'].'</div>';
                }
                $result = $this->jsonHelper->jsonEncode($result);
                $this->getResponse()->representJson($result);
            } catch (\Exception $e) {
                $result = $this->jsonHelper->jsonEncode($e->getMessage());
                $this->getResponse()->representJson($result);
            }
        }
    }
}
