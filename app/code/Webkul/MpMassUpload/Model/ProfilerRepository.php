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
namespace Webkul\MpMassUpload\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\MpMassUpload\Api\Data\ProfileInterface;
use Webkul\MpMassUpload\Model\ResourceModel\Profile\CollectionFactory;
use Webkul\MpMassUpload\Model\ResourceModel\Profile as ResourceModelProfile;
use Webkul\MpMassUpload\Api\Data\ProfilerDataInterface;
use Webkul\MpMassUpload\Api\ProfileRepositoryInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory as SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Webkul\MpMassUpload\Api\ProfilerRepositoryInterface;
use Webkul\MpMassUpload\Model\ProfileRunApiFactory;
use Webkul\MpMassUpload\Model\ProfileRunApiRepository;
use Webkul\MpMassUpload\Api\Data\ResponseInterface;

class ProfilerRepository implements ProfilerRepositoryInterface
{

    public const PENDING = 0;
    public const COMPLETE = 1;
    public const INPROGRESS = 2;

    public const PENDING_LABEL = 'Pending';
    public const COMPLETE_LABEL = 'Complete';
    public const INPROGRESS_LABEL = 'In Progressing';

    /**
     * @var string
     */
    protected $errorData;

    /**
     * @var string
     */
    protected $completeProcess= false;
    
    /**
     * @var ProfileFactory
     */
    protected $_profileFactory;

    /**
     * @var Profile[]
     */
    protected $_instancesById = [];

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var ResourceModelProfile
     */
    protected $_resourceModel;

    /**
     * @param \Webkul\MpMassUpload\Model\ProfileFactory $profileFactory
     * @param CollectionFactory $collectionFactory
     * @param ResourceModelProfile $resourceModel
     * @param \Webkul\MpMassUpload\Helper\Data $helper
     * @param ProfileRunApiFactory $profileRunFactory
     * @param ProfileRunApiRepository $profileRun
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param ResponseInterface $responseInterface
     * @param ProfileRepositoryInterface $profileRepository
     * @param SearchResultFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Webkul\MpMassUpload\Model\ProfileFactory $profileFactory,
        CollectionFactory $collectionFactory,
        ResourceModelProfile $resourceModel,
        \Webkul\MpMassUpload\Helper\Data $helper,
        ProfileRunApiFactory $profileRunFactory,
        ProfileRunApiRepository $profileRun,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        ResponseInterface $responseInterface,
        ProfileRepositoryInterface $profileRepository,
        SearchResultFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->_profileFactory      = $profileFactory;
        $this->_collectionFactory   = $collectionFactory;
        $this->_resourceModel       = $resourceModel;
        $this->helper               = $helper;
        $this->profileRunFactory    = $profileRunFactory;
        $this->profileRun           = $profileRun;
        $this->registry             = $registry;
        $this->_date                = $date;
        $this->responseInterface    = $responseInterface;
        $this->profileRepository    = $profileRepository;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor  = $collectionProcessor;
    }

    /**
     * Retrieve MpMassUpload Profile Data By seller id.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResults
     */
    public function getBySeller(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        try {
            $customerId = $this->helper->getCustomerLoggedId();
            if (!$this->helper->isSellerStatus($customerId)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid seller')
                );
            }
            
            $profileCollection = $this->_collectionFactory->create()
                    ->addFieldToSelect(['profile_name','id'])
                    ->addFieldToFilter('customer_id', $customerId);

            $this->collectionProcessor->process($searchCriteria, $profileCollection);
            
            $profileCollection->load();
            $searchResult = $this->searchResultsFactory->create();
            $searchResult->setSearchCriteria($searchCriteria);
            $searchResult->setItems($profileCollection->getData());
            $searchResult->setTotalCount($profileCollection->getSize());
            return $searchResult;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * Run Profile.
     *
     * @param \Webkul\MpMassUpload\Api\Data\ProfilerDataInterface $profileData
     * @return \Webkul\MpMassUpload\Api\Data\ProfilerDataInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function runProfile(\Webkul\MpMassUpload\Api\Data\ProfilerDataInterface $profileData)
    {
        $profileId = $profileData->getId();

        try {
            if ($profileId) {
                $customerId = $this->helper->getCustomerLoggedId();
                if (!$this->helper->isSellerStatus($customerId)) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Invalid seller')
                    );
                }
                
                $profileRunModel = $this->profileRunFactory->create()->getCollection()
                ->addFieldToFilter('profile_id', ['eq'=>$profileId]);
                if ($profileRunModel->getSize()) {
                    $profileRunData = $profileRunModel->getFirstItem();
                    $status = $this->getProfileStatus($profileRunData->getStatus());
                    $profileData->setErrorMsg(__("Profile Already in %1 state", $status));
                    
                    $profileData->setProfilerName($this->getProfilerNameByProfileId($profileId));
                    return $profileData->setStatus($status);
                }
                
                $profileRunModel = $this->profileRunFactory->create();
                $row = 1;
                $productCount   = $this->helper->getTotalCount($profileId);
                $postData       = $this->helper->getProductPostData($profileId, $row);
                $postData['seller_id'] = $customerId;
                $postData['profile_id'] = $profileId;
                $postData['row'] = $row;
                $postData['total_row_count'] = $productCount;
                $sellerId = $customerId;
                $error = [];
                $profileRunModel->setProfileId($profileId);
                $profileRunModel->setStatus(self::INPROGRESS);
                $profileRunModel->setProfilerName($this->getProfilerNameById($profileId));
                $profileSaveId = $this->profileRun->save($profileRunModel)->getId();

                $result = $this->importProduct($row, $postData);
                
                if ($this->errorData || $this->completeProcess) {
                    $profileRunModel = $this->profileRunFactory->create()->load($profileSaveId);
                    $profileRunModel->setId($profileSaveId);
                    $profileRunModel->setErrorMessage(
                        $this->helper->getJsonEncode($this->errorData)
                    );
                    $profileRunModel->setStatus(self::COMPLETE);
                    $profileRunModel->setCreatedDate($this->_date->gmtDate());

                    $profileData->setErrorMsg(
                        $this->helper->getJsonEncode($this->errorData)
                    );
                    $profileData->setStatus(self::COMPLETE_LABEL);
                    $profileData->setProfilerName($this->getProfilerNameByProfileId($profileId));

                    $this->profileRun->save($profileRunModel);
                }
                return $profileData;
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * Get Profile Status
     *
     * @param int $status
     * @return string
     */
    public function getProfileStatus($status)
    {
        if ($status == self::INPROGRESS) {
            $statusLabel = self::INPROGRESS_LABEL;
        } elseif ($status == self::COMPLETE) {
            $statusLabel = self::COMPLETE_LABEL;
        } else {
            $statusLabel = self::PENDING_LABEL;
        }

        $statusLabel = __($statusLabel);
        return $statusLabel;
    }

    /**
     * Product Import Function
     *
     * @param int $row
     * @param array $postData
     * @return array
     */
    public function importProduct($row, $postData)
    {
        if (!empty($postData['row'])) {
            $row = $postData['row'];
            $result = $this->helper->saveProduct($postData['seller_id'], $row, $postData);
            $this->registry->unregister('mp_flat_catalog_flag');
        } else {
            $result['error'] = 1;
            $result['msg'] = __('Product data not exists.');
        }
        if (empty($result['error'])) {
            $result['error'] = 0;
        }
        if (empty($result['config_error'])) {
            $result['config_error'] = 0;
        }
        if ($result['error']) {
            $result['msg']  = $result['msg'];
            $this->errorData[] = $result['msg'];
        }
        $row++;

        if (isset($result['next_row_data'])) {
            if ($result['next_row_data']['row'] <= $result['next_row_data']['total_row_count']) {
                $result= $this->importProduct($row, $result['next_row_data']);
            }
        }
        
        if ($row == $result['total_row_count']) {
            $this->completeProcess = true;
            $this->helper->deleteProfile($postData['profile_id']);
        }
        return $result;
    }

    /**
     * Get Profile Name
     *
     * @param int $profileId
     * @return string
     */
    public function getProfilerNameById($profileId)
    {
        $profileData = $this->helper->getProfileData($profileId);
        return $profileData->getProfileName();
    }

    /**
     * Get Profile Name for Frofile APi Table
     *
     * @param int $profileId
     * @return string
     */
    public function getProfilerNameByProfileId($profileId)
    {
        $profileRunModel = $this->profileRunFactory->create()->getCollection()
            ->addFieldToFilter('profile_id', ['eq'=>$profileId]);
        if ($profileRunModel->getSize()) {
            $profileRunModel = $profileRunModel->getFirstItem();
            return $profileRunModel->getProfilerName();
        }
        return false;
    }

    /**
     * GetJsonResponse returns json response.
     *
     * @param array $responseContent
     *
     * @return JSON
     */
    protected function getJsonResponse($responseContent = [])
    {
        $res = $this->responseInterface;
        $res->setItem($responseContent);
        return $res->getData();
    }

    /**
     * Delete Profiler
     *
     * @param string $profilerIds
     * @return void
     */
    public function deleteProfiler($profilerIds)
    {
        $result = [];
        try {
            $customerId = $this->helper->getCustomerLoggedId();
            if (!$this->helper->isSellerStatus($customerId)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid seller')
                );
            }
            if ($profilerIds) {
                $countRecord = 0;
                $profilerIds = $this->helper->getJsonDecode($profilerIds);
                if (count($profilerIds)==0) {
                    $result['error'] = true;
                    $result['message'] = __('Please Select record');
                }
                foreach ($profilerIds as $profilerId) {
                    $id = $this->profileRepository->get($profilerId)->getId();
                    if ($id && $this->profileRepository->get($profilerId)->delete()) {
                        $countRecord++;
                    }
                }
                if ($countRecord>0) {
                    $result['error'] = false;
                    $result['message'] = __('A total of %1 record(s) have been deleted.', $countRecord);
                } else {
                    $result['error'] = true;
                    $result['message'] = __('Selected Items not longer existing');
                }

                return $this->getJsonResponse($result);
            }
        } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($e->getMessage())
                );
        }
    }
}
