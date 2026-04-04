<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class MassDelete extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;
    /**
     *
     * @var Filter
     */
    protected $_filter;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $_assignHelper;

    /**
     * @var \Webkul\MpAssignProduct\Model\ItemsFactory
     */
    protected $_items;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Initialization
     *
     * @param Context $context
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param \Webkul\MpAssignProduct\Model\ItemsFactory $itemsFactory
     * @param Registry $coreRegistry
     * @param ProductRepositoryInterface|null $productRepository
     * @param Filter|null $filter
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        \Webkul\MpAssignProduct\Model\ItemsFactory $itemsFactory,
        Registry $coreRegistry,
        ProductRepositoryInterface $productRepository = null,
        Filter $filter = null
    ) {
        $this->_url = $url;
        $this->_session = $session;
        $this->_assignHelper = $helper;
        $this->_items = $itemsFactory;
        $this->productRepository = $productRepository
            ?: \Magento\Framework\App\ObjectManager::getInstance()->create(ProductRepositoryInterface::class);
        $this->coreRegistry = $coreRegistry;
        $this->_filter = $filter ?: \Magento\Framework\App\ObjectManager::getInstance()->create(Filter::class);
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
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
     * Mass delete products
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $assignProductCollection = $this->_filter->getCollection($this->_items->create()->getCollection());
        $productDeleted = 0;
        $productDeletedError = 0;
        $this->coreRegistry->register('isSecureArea', true);
        // $assignProductCollection = $this->_items->create()
        //                           ->getCollection()
        //                           ->addFieldToFilter('id', ['in' => $data['wk_delete']]);
        $assignProductIds = $assignProductCollection->getAllIds();
        if (!empty($assignProductIds)) {
            foreach ($assignProductIds as $productId) {
                try {
                    if (!$this->getAssignId($productId, $this->_assignHelper->getCustomerId())) {
                        $productDeletedError++;
                    }
                    $product = $this->productRepository->getById($productId);
                    $this->productRepository->delete($product);
                    $productDeleted++;
                } catch (LocalizedException $exception) {
                    $productDeletedError++;
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }
        if ($productDeleted) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $productDeleted)
            );
        }
        if ($productDeletedError) {
            $this->messageManager->addErrorMessage(
                __(
                    'A total of %1 record(s) haven\'t been deleted.',
                    $productDeletedError
                )
            );
        }
        $this->coreRegistry->unregister('isSecureArea');
        return $this->resultRedirectFactory->create()->setPath('*/*/productlist');
    }
    /**
     * Csrf Validation
     *
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Get Assigned id
     *
     * @param [type] $assign_id
     * @param [type] $customer
     * @return void
     */
    protected function getAssignId($assign_id, $customer)
    {
        $model = $this->_items->create()->load($assign_id, 'assign_product_id');
        return $model->getSellerId() == $customer? true:false;
    }
}
