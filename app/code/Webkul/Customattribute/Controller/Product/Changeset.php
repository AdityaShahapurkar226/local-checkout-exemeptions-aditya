<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customattribute
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Customattribute\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul Customattribute Change Attributeset of Product controller.
 */
class Changeset extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultJsonFactory;
    
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_urlModel;

    /**
     * @param Context                                           $context
     * @param Session                                           $customerSession
     * @param PageFactory                                       $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory  $resultJsonFactory
     * @param \Magento\Customer\Model\Url                       $urlModel
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Url $urlModel
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_urlModel = $urlModel;
        parent::__construct(
            $context
        );
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
        $loginUrl = $this->_urlModel->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
     * Seller Product Create page.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $setId = $this->getRequest()->getParam('setid');
            $type = $this->getRequest()->getParam('type');
            $url = $this->getRequest()->getParam('url');
            if ($type != '') {
                $newUrl = $url.'set/'.$setId.'/type/'.$type.'/';
                return $this->_resultJsonFactory->create()->setData([
                    'url' => $newUrl
                ]);
            } else {
                $productId = $this->getRequest()->getParam('productid');
                $newUrl = $url.'id/'.$productId.'/set/'.$setId.'/';
                return $this->_resultJsonFactory->create()->setData([
                    'url' => $newUrl
                ]);
            }
        }
    }
}
