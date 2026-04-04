<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Controller\Product\Attribute;

use Webkul\Marketplace\Helper\Data as HelperData;
use Magento\Eav\Api\AttributeRepositoryInterface;

/**
 * Webkul Marketplace Product Attribute Delete Controller.
 */
class Delete extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param AttributeRepositoryInterface $attributeRepository
     * @param HelperData $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        AttributeRepositoryInterface $attributeRepository,
        HelperData $helper
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->messageManager= $messageManager;
        $this->attributeRepository = $attributeRepository;
        $this->helper = $helper;
    }

    /**
     * Create attribute pageFactory
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->helper;
        $isPartner = $helper->isSeller();
        $type = 'configurable';
        $attributeId = $this->getRequest()->getParam('attribute_id');
        if (empty($attributeId)) {
            $this->messageManager->addSuccess(__('The data do not exist'));
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/product_attribute/new',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }

        $allowedTypes = explode(',', $helper->getAllowedProductType());
        if ($isPartner == 1 && in_array($type, $allowedTypes)) {
            $this->attributeRepository->deleteById($attributeId);
            $this->messageManager->addSuccess(__('Attribute has been successfully deleted'));
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/product_attribute/new',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
