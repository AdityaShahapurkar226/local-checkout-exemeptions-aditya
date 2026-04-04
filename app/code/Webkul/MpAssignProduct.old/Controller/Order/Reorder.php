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
namespace Webkul\MpAssignProduct\Controller\Order;

use Magento\Framework\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;

class Reorder extends Action\Action
{
    /**
     * @var OrderLoaderInterface
     */
    protected $_orderLoader;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Checkout\Model\CartFactory
     */
    protected $_cart;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_session;

    /**
     * @param Action\Context $context
     * @param OrderLoaderInterface $orderLoader
     * @param Registry $registry
     * @param \Magento\Checkout\Model\CartFactory $cartFactory
     * @param \Magento\Checkout\Model\Session $session
     */
    public function __construct(
        Action\Context $context,
        OrderLoaderInterface $orderLoader,
        Registry $registry,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Checkout\Model\Session $session
    ) {
        $this->_orderLoader = $orderLoader;
        $this->_coreRegistry = $registry;
        $this->_cart = $cartFactory;
        $this->_session = $session;
        parent::__construct($context);
    }

    /**
     * Action for reorder
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->_orderLoader->load($this->_request);
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }
        $cart = $this->_cart->create();
        $order = $this->_coreRegistry->registry('current_order');
        $redirect = $this->resultRedirectFactory->create();
        $orderItems = $order->getAllVisibleItems();
        
        foreach ($orderItems as $item) {
            try {
                $this->processItem($item, $cart);
            } catch (LocalizedException $e) {
                $msg = $e->getMessage();
                if ($this->_session->getUseNotice(true)) {
                    $this->messageManager->addNotice($msg);
                } else {
                    $this->messageManager->addError($msg);
                }
                return $redirect->setPath('sales/order/history');
            } catch (\Exception $e) {
                $msg = 'We can\'t add this item to your shopping cart right now.';
                $this->messageManager->addException($e, __($msg));
                return $redirect->setPath('checkout/cart');
            }
        }
        return $redirect->setPath('checkout/cart');
    }

    /**
     * Process item
     *
     * @param object $item
     * @return void
     */
    public function processItem($item, $cart)
    {
        $cart->addOrderItem($item);
        $cart->save();
        $info = $item->getProductOptionByCode('info_buyRequest');
        $this->_eventManager->dispatch(
            'checkout_cart_add_product_complete',
            [
                'product' => $item->getProductId(),
                'info' => $info,
                'request' => $this->getRequest(),
                'response' => $this->getResponse()
            ]
        );
    }
}
