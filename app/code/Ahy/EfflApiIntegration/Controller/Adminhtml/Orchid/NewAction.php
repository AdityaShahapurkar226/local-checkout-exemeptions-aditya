<?php
declare(strict_types=1);

namespace Ahy\EfflApiIntegration\Controller\Adminhtml\Orchid;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class NewAction extends Action
{
    public const ADMIN_RESOURCE = 'Ahy_EfflApiIntegration::orchid_ffl_dealers';

    protected PageFactory $resultPageFactory;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ahy_EfflApiIntegration::orchid_ffl_dealers');
        $resultPage->getConfig()->getTitle()->prepend(__('Add New Dealer'));
        return $resultPage;
    }
}
