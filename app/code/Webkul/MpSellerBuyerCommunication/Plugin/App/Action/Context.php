<?php
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MpSellerBuyerCommunication
 * @author      Webkul
 * @copyright   Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerBuyerCommunication\Plugin\App\Action;
 
class Context
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
 
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;
 
    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
    }
 
   /**
    * Around Dispatch
    *
    * @param \Magento\Framework\App\ActionInterface $subject
    * @param \Closure $proceed
    * @param \Magento\Framework\App\RequestInterface $request
    * @return void
    */
    public function aroundDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->httpContext->setValue(
            'customer_id',
            $this->customerSession->getCustomerId(),
            false
        );
 
        return $proceed($request);
    }
}
