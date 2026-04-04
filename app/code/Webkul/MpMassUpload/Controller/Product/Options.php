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
use Magento\Framework\App\RequestInterface;

class Options extends \Magento\Framework\App\Action\Action
{
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
     * @param Context $context
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpMassUpload\Helper\Data $massUploadHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJson
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpMassUpload\Helper\Data $massUploadHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson
    ) {
        $this->_url = $url;
        $this->_session = $session;
        $this->_massUploadHelper = $massUploadHelper;
        $this->_resultJson = $resultJson;
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
    * Options Execute
    *
    * @return string
    */
    public function execute()
    {
        $attributeCode = $this->getRequest()->getParam("code");
        $helper = $this->_massUploadHelper;
        $result = $helper->getAttributeOptions($attributeCode);
        return $this->_resultJson->create()->setData($result);
    }
}
