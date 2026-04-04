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
namespace Webkul\MpMassUpload\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\ImportExport\Model\Export\Adapter\Csv as AdapterCsv;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class SellerProducts extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Webkul\MpMassUpload\Helper\Data
     */
    protected $_massUploadHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;
    
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
   * @param \Webkul\MpMassUpload\Helper\Data $massUploadHelper
   * @param \Magento\Framework\Json\Helper\Data $jsonHelper
   * @param \Webkul\Marketplace\Model\Product $mpProduct
   * @param \Webkul\MpMassUpload\Helper\Export $helperExport
   * @param AdapterCsv $writer
   * @param FileFactory $fileFactory
   */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\MpMassUpload\Helper\Data $massUploadHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Marketplace\Model\Product $mpProduct,
        \Webkul\MpMassUpload\Helper\Export $helperExport,
        AdapterCsv $writer,
        FileFactory $fileFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_massUploadHelper = $massUploadHelper;
        $this->_jsonHelper = $jsonHelper;
        $this->_mpProduct = $mpProduct;
        $this->_helperExport = $helperExport;
        $this->_writer = $writer;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

   /**
    * Resource access
    *
    * @return bool
    */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpMassUpload::export');
    }

    /**
     * Save action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $helper = $this->_massUploadHelper;
            $data = $this->getRequest()->getParams();
            $sellerId = $data['seller_id'];
            $productType = $data['id'];
            $allowedAttributes = [];
            if (!empty($data['custom_attributes'])) {
                $allowedAttributes = $data['custom_attributes'];
                $allowedAttributes= explode(",", $allowedAttributes);
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
        } catch (\Exception $e) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addError(__('Operation Failed.'));
            return $resultRedirect->setPath('*/*/');
        }
    }
}
