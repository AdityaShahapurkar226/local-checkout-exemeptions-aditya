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
namespace Webkul\MpMassUpload\Controller\ProfileListing;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\View\Result\PageFactory;

class ProfileList extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

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
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpMassUpload\Helper\Data $massUploadHelper
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param \Webkul\MpMassUpload\Api\ProfileRepositoryInterface $ProfileRepository
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpMassUpload\Helper\Data $massUploadHelper,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\MpMassUpload\Api\ProfileRepositoryInterface $ProfileRepository,
        FileFactory $fileFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_url = $url;
        $this->_session = $session;
        $this->_massUploadHelper = $massUploadHelper;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->fileFactory = $fileFactory;
        $this->_profileRepository = $ProfileRepository;
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
     * Return execute
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $isPartner = $this->_massUploadHelper->isSeller();
        if ($isPartner == 1) {
            if (!empty($this->getRequest()->getParams()) && !empty($this->getRequest()->getParam('profile_list'))) {
                try {
                    $data = $this->getRequest()->getParams();
                    $countRecord = 0;
                    foreach ($data['profile_list'] as $profileId) {
                        if ($this->_profileRepository->get($profileId)->delete()) {
                            $countRecord++;
                        }
                    }
                    $this->messageManager->addSuccess(
                        __(
                            'A total of %1 record(s) have been deleted.',
                            $countRecord
                        )
                    );
                    return $this->resultRedirectFactory->create()->setPath(
                        'mpmassupload/profilelisting/profilelist',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/profilelist',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                $resultPage = $this->_resultPageFactory->create();
                if ($this->_marketplaceHelper->getIsSeparatePanel()) {
                    $resultPage->addHandle('mpmassupload_layout2_profilelisting_profilelist');
                }
                $resultPage->getConfig()->getTitle()->set(
                    __('Mass Uploaded Profile List')
                );
                return $resultPage;
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
