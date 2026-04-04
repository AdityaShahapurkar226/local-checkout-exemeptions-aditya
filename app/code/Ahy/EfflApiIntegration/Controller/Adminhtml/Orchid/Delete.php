<?php

declare(strict_types=1);

namespace Ahy\EfflApiIntegration\Controller\Adminhtml\Orchid;

use Magento\Backend\App\Action;
use Ahy\EfflApiIntegration\Model\OrchidFflDealerFactory;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'Ahy_EfflApiIntegration::dealer_delete';

    protected OrchidFflDealerFactory $dealerFactory;

    public function __construct(
        Action\Context $context,
        OrchidFflDealerFactory $dealerFactory
    ) {
        $this->dealerFactory = $dealerFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        // Redirect object
        $resultRedirect = $this->resultRedirectFactory->create();

        // Get entity_id from URL
        $id = (int) $this->getRequest()->getParam('entity_id');

        if ($id) {
            try {
                $dealer = $this->dealerFactory->create()->load($id);
                if (!$dealer->getId()) {
                    $this->messageManager->addErrorMessage(__('Dealer does not exist.'));
                    return $resultRedirect->setPath('ahy_efflapiintegration/orchid/dealerslist');
                }

                // Soft delete → set is_ffl_active = 0
                $dealer->setData('is_ffl_active', 0);
                $dealer->save();
                $this->messageManager->addSuccessMessage(__('Dealer has been deactivated successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Error while deactivating dealer: %1', $e->getMessage()));
                return $resultRedirect->setPath('ahy_efflapiintegration/orchid/dealerslist');
            }
        } else {
            $this->messageManager->addErrorMessage(__('Invalid dealer ID.'));
        }

        // Redirect back to listing page
        return $resultRedirect->setPath('ahy_efflapiintegration/orchid/dealerslist');
    }
}
