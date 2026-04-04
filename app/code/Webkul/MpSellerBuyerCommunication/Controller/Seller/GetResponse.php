<?php
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MpSellerBuyerCommunication
 * @author      Webkul
 * @copyright   Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerBuyerCommunication\Controller\Seller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Controller\ResultFactory;

class GetResponse extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var FileSystem
     */
    protected $_filesystem;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository
     */
    protected $_communicationRepository;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Webkul\MpSellerBuyerCommunication\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param PageFactory $resultPageFactory
     * @param \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository $conversationFactory
     * @param \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository $communicationRepository
     * @param Data $data
     */
    public function __construct(
        Context $context,
        \Webkul\MpSellerBuyerCommunication\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        PageFactory $resultPageFactory,
        \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository $conversationFactory,
        \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository $communicationRepository,
        Data $data
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
        $this->conversationFactory = $conversationFactory;
        $this->communicationRepository = $communicationRepository;
        $this->data = $data;
        $this->helper = $helper;
        $this->mpHelper = $mpHelper;
    }

    /**
     * My Communication History Page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $responseRate = 0;
        $sellerId = 0;
        $product_id = $this->getRequest()->getPost('product_id');
        $sellerProduct = $this->mpHelper->getSellerProductDataByProductId($product_id);
        if ($sellerProduct->getSize()) {
            foreach ($sellerProduct as $value) {
                $sellerId = $value['seller_id'];
            }
        }

        $productQueryList = $this->communicationRepository->getAllCollectionBySeller($sellerId);

        if ($productQueryList->getSize()) {
            foreach ($productQueryList as $conversation) {
                $conversationIds[] = $conversation->getEntityId();
            }
        }

        if (!empty($conversationIds)) {
            $conversationCollection = $this->conversationFactory
                                    ->getCollectionByQueryIds($conversationIds);

            $queryCount = $this->conversationFactory->getQueryCount($conversationCollection);
            $replyCount = $this->conversationFactory->getReplyCount($conversationIds);
            if ($queryCount!=0) {
                $responseRate = ($replyCount / $queryCount) * 100;
                $responseRate = round($responseRate, 2);
            }
        }
        
        $avgTime = $this->getResponseTimeOfSeller($sellerId);

        $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(
            $this->data->jsonEncode(
                [
                    'responserate' => $responseRate,
                    'avgTime' => $avgTime,
                ]
            )
        );
        return $response;
    }

    /**
     * Get Response Time Of Seller
     *
     * @param int $sellerId
     * @return void
     */
    public function getResponseTimeOfSeller($sellerId)
    {
        $conversationIds = [];
        $responseRate = 0;
        $totalResponseTime = 0;
        $rounded = 0;
        $minutes_arr = [];
        $seconds_arr = [];
        $conversationIds = $this->helper->getQueryProductIdsBySellerId($sellerId);
        if (!empty($conversationIds)) {
            $conversationCollection = $this->conversationFactory
                                    ->getResponseCollectionByQueryIds($conversationIds);
            if ($conversationCollection->getSize()) {
                $count = $conversationCollection->getSize();
                foreach ($conversationCollection as $reply) {
                    $time_arr = $this->getInMinutes($reply->getResponseTime());
                    array_push($minutes_arr, $time_arr[0]);
                    array_push($seconds_arr, $time_arr[1]);
                }
                $responseInMinutes = floor(array_sum($minutes_arr) / $count);
                $responseInSeconds = round(((60 * (array_sum($minutes_arr) % $count))
                    + array_sum($seconds_arr)) / $count);
                if ($responseInSeconds > 60) {
                    $responseInMinutes += floor($responseInSeconds/60);
                    $responseInSeconds = $responseInSeconds % 60;
                }
                $rounded = $responseInMinutes.':'.$responseInSeconds;
            }
        }
        return $rounded;
    }

    /**
     * Convert hours to minutes
     *
     * @param  string $responseTime
     * @return int
     */
    private function getInMinutes($responseTime)
    {
        $inMinutes = 0;
        $totalMinutes = 0;
        $timeArray = explode(':', $responseTime);

        if ($timeArray[0]) {
            $inMinutes = $timeArray[0]*60;
        }
        if (isset($timeArray[1])) {
            $totalMinutes = $inMinutes + $timeArray[1];
        }
        return [$totalMinutes, $timeArray[2] ?? 00 ];
    }
}
