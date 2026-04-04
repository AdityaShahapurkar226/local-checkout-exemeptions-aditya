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
namespace Webkul\MpMassUpload\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

class Run extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Webkul\MpMassUpload\Helper\Data
     */
    protected $_massUploadHelper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpMassUpload\Helper\Data $massUploadHelper
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpMassUpload\Helper\Data $massUploadHelper,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_url = $url;
        $this->_session = $session;
        $this->_massUploadHelper = $massUploadHelper;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
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
     * Run Execute
     *
     * @return string
     */
    public function execute()
    {
        if (!empty($this->getRequest()->getPost())) {
            try {
                $helper = $this->_massUploadHelper;
                $sellerId = $this->marketplaceHelper->getCustomerId();
                $profileId = $this->getRequest()->getParam('profile_id');
                $wholeData = $this->getRequest()->getParams();
                if (!empty($wholeData['row'])) {
                    $row = $wholeData['row'];
                    $result = $helper->saveProduct($sellerId, $row, $wholeData);
                } else {
                    $result['error'] = 1;
                    $result['msg'] = __('Product data not exists.');
                }
                if (empty($result['error'])) {
                    $result['error'] = 0;
                }
                if (empty($result['config_error'])) {
                    $result['config_error'] = 0;
                }
                if ($result['error']) {
                    $result['msg'] = '<div class="wk-mu-error wk-mu-box">'.$result['msg'].'</div>';
                }
                $result = $this->_jsonHelper->jsonEncode($result);
                $this->getResponse()->representJson($result);
            } catch (\Exception $e) {
                $result = $this->_jsonHelper->jsonEncode($e->getMessage());
                $this->getResponse()->representJson($result);
            }
        }
    }
}
