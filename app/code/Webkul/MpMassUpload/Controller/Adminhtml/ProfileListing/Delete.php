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

namespace Webkul\MpMassUpload\Controller\Adminhtml\ProfileListing;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpMassUpload\Api\ProfileRepositoryInterface;

/**
 * Webkul MassUpload Uploaded Profile Delete Controller.
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var ProfileRepositoryInterface
     */
    protected $_profileRepository;
    
    /**
     * @param Context $context
     * @param Filter $filter
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        ProfileRepositoryInterface $profileRepository
    ) {
        $this->_filter = $filter;
        $this->_profileRepository = $profileRepository;
        parent::__construct($context);
    }
    /**
     * MassUpload Uploaded Profile Delete action.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $profileId = $this->getRequest()->getParam('id');
        $collection = $this->_profileRepository->get($profileId);
        if (!$collection) {
            $this->messageManager->addError(
                __(
                    'No profile exists with id %1.',
                    $profileId
                )
            );
        } else {
            $collection->delete();
            $this->messageManager->addSuccess(
                __(
                    'Mass Uploaded Profile having id  %1 have been deleted.',
                    $profileId
                )
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('mpmassupload/profilelisting/index/');
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpMassUpload::profileListing');
    }
}
