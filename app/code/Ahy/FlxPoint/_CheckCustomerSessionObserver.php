<?php
namespace Ahy\Estate\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session;
use Magento\Framework\View\LayoutInterface;

class CheckCustomerSessionObserver implements ObserverInterface
{
    protected $customerSession;

    public function __construct(Session $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    public function execute(Observer $observer)
    {
        /** @var LayoutInterface $layout */
        $layout = $observer->getLayout();

        $isLoggedIn = $this->customerSession->isLoggedIn() ? 'Yes' : 'No';
        $js = '<script>console.log("User logged in: ' . $isLoggedIn . '");</script>';

        $block = $layout->createBlock(\Magento\Framework\View\Element\Text::class);
        $block->setText($js);

        // Append to 'head.additional' or fallback to 'before.body.end'
        if ($layout->getBlock('head.additional')) {
            $layout->getBlock('head.additional')->append($block);
        } elseif ($layout->getBlock('before.body.end')) {
            $layout->getBlock('before.body.end')->append($block);
        }
    }
}
