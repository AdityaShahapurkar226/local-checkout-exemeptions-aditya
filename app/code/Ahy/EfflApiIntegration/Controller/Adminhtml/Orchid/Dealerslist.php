<?php
namespace Ahy\EfflApiIntegration\Controller\Adminhtml\Orchid;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

class Dealerslist extends Action
{
    const ADMIN_RESOURCE = 'Ahy_FFL::orchid_ffl_dealers';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute action
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ahy_FFL::orchid_ffl_dealers');
        $resultPage->getConfig()->getTitle()->prepend(__('Orchid FFL Dealers'));

        return $resultPage;
    }
}
