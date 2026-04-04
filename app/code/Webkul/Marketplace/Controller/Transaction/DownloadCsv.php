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

namespace Webkul\Marketplace\Controller\Transaction;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Webkul\Marketplace\Model\SellertransactionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Webkul Marketplace Transaction DownloadCsv Controller.
 */
class DownloadCsv extends Action
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var CustomerUrl
     */
    protected $customerUrl;

    /**
     * @var SellertransactionFactory
     */
    protected $sellertransactionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $fileCsv;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * Construct
     *
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerUrl $customerUrl
     * @param SellertransactionFactory $sellertransactionFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\File\Csv $fileCsv
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerUrl $customerUrl,
        SellertransactionFactory $sellertransactionFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\File\Csv $fileCsv,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->customerUrl = $customerUrl;
        $this->sellertransactionFactory = $sellertransactionFactory;
        $this->logger = $logger;
        $this->fileCsv = $fileCsv;
        $this->directoryList = $directoryList;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
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
        $loginUrl = $this->customerUrl->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Add product to shopping cart action.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        try {
            if (!($customerId = $this->getCustomerId())) {
                return false;
            }

            $ids = [];
            $orderids = [];

            $trId = '';
            $filterDataTo = '';
            $filterDataFrom = '';
            $from = null;
            $to = null;
            if (isset($params['tr_id'])) {
                $trId = $params['tr_id'] != '' ? $params['tr_id'] : '';
            }
            if (isset($params['from_date'])) {
                $filterDataFrom = $params['from_date'] != '' ? $params['from_date'] : '';
            }
            if (isset($params['to_date'])) {
                $filterDataTo = $params['to_date'] != '' ? $params['to_date'] : '';
            }

            $collection = $this->sellertransactionFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $customerId
            );

            if ($filterDataTo) {
                $todate = date_create($filterDataTo);
                $to = date_format($todate, 'Y-m-d 23:59:59');
            }
            if ($filterDataFrom) {
                $fromdate = date_create($filterDataFrom);
                $from = date_format($fromdate, 'Y-m-d H:i:s');
            }

            if ($trId) {
                $collection->addFieldToFilter(
                    'transaction_id',
                    $trId
                );
            }
            if ($from || $to) {
                $collection->addFieldToFilter(
                    'created_at',
                    ['datetime' => true, 'from' => $from, 'to' => $to]
                );
            }

            $collection->setOrder(
                'created_at',
                'desc'
            );

            $data = [];
            /** Add yout header name here */
            $data[] = [
                'created_at' => __('Date'),
                'transaction_id' => __('Transaction Id'),
                'custom_note' => __('Comment Message'),
                'transaction_amount' => __('Transaction Amount')
            ];
            foreach ($collection as $transactioncoll) {
                $data1 = [];
                $data1['Date'] = $transactioncoll->getCreatedAt();
                $data1['Transaction Id'] = $transactioncoll->getTransactionId();
                if ($transactioncoll->getCustomNote()) {
                    $data1['Comment Message'] = $transactioncoll->getCustomNote();
                } else {
                    $data1['Comment Message'] = __('None');
                }
                $data1['Transaction Amount'] = $transactioncoll->getTransactionAmount();
                $data[] = $data1;
            }

            if (isset($data[0])) {
                $fileName = 'transactionlist.csv';
                $filePath =  $this->directoryList->getPath(DirectoryList::MEDIA) . "/" . $fileName;

                $this->fileCsv
                    ->setEnclosure('"')
                    ->setDelimiter(',')
                    ->saveData($filePath, $data);

                return $this->fileFactory->create(
                    $fileName,
                    [
                        'type'  => "filename",
                        'value' => $fileName,
                        'rm'    => true,
                    ],
                    DirectoryList::MEDIA,
                    'text/csv',
                    null
                );
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/transaction/history',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->logger->critical($e);

            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/transaction/history',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
