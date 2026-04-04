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
namespace Webkul\MpAssignProduct\Controller\Cart;

use Magento\Framework\Exception\LocalizedException;

class Addgroup extends \Magento\Checkout\Controller\Cart
{
    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Sales\Model\Order\ItemFactory $itemFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Sales\Model\Order\ItemFactory  $itemFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->itemFactory = $itemFactory;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $itemIds = $this->getRequest()->getParam('order_items', []);
        if (is_array($itemIds)) {
            $items = $this->itemFactory->create()
                            ->getCollection()
                            ->addIdFilter($itemIds)
                            ->load();
            foreach ($items as $item) {
                try {
                    $this->processItem($item);
                } catch (LocalizedException $e) {
                    $msg = $e->getMessage();
                    if ($this->_checkoutSession->getUseNotice(true)) {
                        $this->messageManager->addNotice($msg);
                    } else {
                        $this->messageManager->addError($msg);
                    }
                } catch (\Exception $e) {
                    $msg = 'We can\'t add this item to your shopping cart right now.';
                    $this->messageManager->addException($e, __($msg));
                    $this->logger->critical($e);
                    return $this->_goBack();
                }
            }
        }
        return $this->_goBack();
    }

    /**
     * Process item
     *
     * @param object $item
     * @return void
     */
    public function processItem($item)
    {
        $this->cart->addOrderItem($item, 1);
        $this->cart->save();
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
