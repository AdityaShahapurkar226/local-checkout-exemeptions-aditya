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
namespace Webkul\MpMassUpload\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Export\Adapter\Csv as AdapterCsv;
use Magento\Framework\View\Result\PageFactory;

class Export extends \Magento\Framework\App\Action\Action
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
     * @var \Webkul\MpMassUpload\Helper\Export
     */
    protected $_helperExport;

    /**
     * @var \Webkul\Marketplace\Model\Product
     */
    protected $_mpProduct;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var AdapterCsv
     */
    protected $_writer;

   /**
    * @param Context $context
    * @param PageFactory $resultPageFactory
    * @param \Magento\Customer\Model\Url $url
    * @param \Magento\Customer\Model\Session $session
    * @param \Webkul\MpMassUpload\Helper\Data $massUploadHelper
    * @param \Webkul\MpMassUpload\Helper\Export $helperExport
    * @param \Webkul\Marketplace\Model\Product $mpProduct
    * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
    * @param AdapterCsv $writer
    * @param FileFactory $fileFactory
    */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpMassUpload\Helper\Data $massUploadHelper,
        \Webkul\MpMassUpload\Helper\Export $helperExport,
        \Webkul\Marketplace\Model\Product $mpProduct,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        AdapterCsv $writer,
        FileFactory $fileFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_url = $url;
        $this->_session = $session;
        $this->_massUploadHelper = $massUploadHelper;
        $this->_helperExport = $helperExport;
        $this->_mpProduct = $mpProduct;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->fileFactory = $fileFactory;
        $this->_writer = $writer;
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
     * Export Exceute
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $isPartner = $this->_massUploadHelper->isSeller();
        if ($isPartner == 1) {
            if (!empty($this->getRequest()->getParams())) {
                try {
                    $helper = $this->_massUploadHelper;
                    $sellerId = $this->marketplaceHelper->getCustomerId();
                    $data = $this->getRequest()->getParams();
                    $productsRow = [];
                    if (empty($data) || empty($data['product_type'])) {
                        $this->messageManager->addError(
                            __("Product type should be selected.")
                        );
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/export',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                  
                    $productType = $data['product_type'];
                    $allowedAttributes = [];
                    if (!empty($data['custom_attributes'])) {
                        $allowedAttributes = $data['custom_attributes'];
                    }
                    $fileName = $productType.'_product.csv';
                    $products = $this->_mpProduct
                    ->getCollection()
                    ->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    )->addFieldToSelect(
                        ['mageproduct_id']
                    );
                    $productIds = $products->getAllIds();
                    $productsRow = $this->_helperExport->exportProducts(
                        $productType,
                        $productIds,
                        $allowedAttributes
                    );
                   
                    $this->setHeaderColumns($productsRow, $fileName, $productType);
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/export',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                $resultPage = $this->_resultPageFactory->create();
                if ($this->marketplaceHelper->getIsSeparatePanel()) {
                    $resultPage->addHandle('mpmassupload_layout2_product_export');
                }
                $resultPage->getConfig()->getTitle()->set(
                    __('Marketplace MassUpload Product Export')
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

    /**
     * Set header columns
     *
     * @param array $productsRow
     * @param string $fileName
     * @param string $productType
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function setHeaderColumns($productsRow, $fileName, $productType)
    {
        if (!empty($productsRow)) {
            $writer = $this->_writer;
            $writer->setHeaderCols($productsRow[0]);
            foreach ($productsRow[1] as $dataRow) {
                if (!empty($dataRow)) {
                    $writer->writeRow($dataRow);
                }
            }
            $productsRow = $writer->getContents();
            return $this->fileFactory->create(
                $fileName,
                $productsRow,
                DirectoryList::VAR_DIR,
                'text/csv'
            );
        } else {
            $this->messageManager->addError(
                __("There is no product with product type: %1 to export.", $productType)
            );
        }
        return $this->resultRedirectFactory->create()->setPath(
            '*/*/export',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
}
