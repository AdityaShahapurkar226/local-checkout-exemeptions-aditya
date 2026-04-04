<?php

namespace Ahy\EfflApiIntegration\Controller\Adminhtml\Orchid;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\RedirectFactory;
use Ahy\EfflApiIntegration\Model\OrchidFflDealerFactory;
use Ahy\EfflApiIntegration\Logger\Logger;

class Save extends Action
{
    protected $orchidFflDealerFactory;
    protected $resultRedirectFactory;
    protected $logger;

    public function __construct(
        Action\Context $context,
        OrchidFflDealerFactory $orchidFflDealerFactory,
        RedirectFactory $resultRedirectFactory,
        Logger $logger 
    ) {
        parent::__construct($context);
        $this->orchidFflDealerFactory = $orchidFflDealerFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $id   = (int) $this->getRequest()->getParam('entity_id');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            try {
                $model = $this->orchidFflDealerFactory->create();

                if ($id) {
                    $model->load($id);
                } else {
                    // Remove entity_id for new record
                    unset($data['entity_id']);
                }

                $model->addData($data);
                $model->save();
                $this->logger->info('Dealer saved', ['data' => $data]);
                $this->messageManager->addSuccessMessage(__('Dealer saved successfully.'));

                // If "Save and Continue Edit" is used
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/dealerslist');
            } catch (\Exception $e) {
                $this->logger->error('Error saving dealer', ['exception' => $e]);
                $this->messageManager->addErrorMessage(__('Error saving dealer: %1', $e->getMessage()));
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('No data to save.'));
        return $resultRedirect->setPath('*/*/dealerslist');
    }
}
