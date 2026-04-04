<?php

namespace Webkul\Marketplace\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Backend\Block\Template;
use Magento\Framework\Registry;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
class PaymentInfo extends Template
{
    protected $_coreRegistry;
    protected $paymentTokenManagement;
    protected $countryCollectionFactory;
    protected $regionCollectionFactory;

    public function __construct(
        Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        \Webkul\Marketplace\Block\Adminhtml\Customer\Edit $customerEdit,
        PaymentTokenManagementInterface $paymentTokenManagement,
        CountryCollectionFactory $countryCollectionFactory,
        RegionCollectionFactory $regionCollectionFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->customerEdit = $customerEdit;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->regionCollectionFactory = $regionCollectionFactory;


        parent::__construct($context, $data);
    }
    public function getCountryOptions()
    {
        $countries = $this->countryCollectionFactory->create();
        $options = [];

        foreach ($countries as $country) {
            $options[] = [
                'value' => $country->getCountryId(),
                'label' => $country->getName()
            ];
        }

        return $options;
    }

    public function getRegionsByCountry()
    {
        $regions = $this->regionCollectionFactory->create()->load();
        $output = [];

        foreach ($regions as $region) {
            $countryId = $region->getCountryId();
            $output[$countryId][] = [
                'region_id' => $region->getRegionId(),
                'code' => $region->getCode(),
                'default_name' => $region->getName(),
            ];
        }

        return $output;
    }

    /**
     * Returns visible and available vault tokens for current customer.
     *
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface[]
     */
    public function getVaultPaymentTokens()
    {
        $customerId = $this->getCustomerId();
        if (!$customerId) {
            return [];
        }

        return $this->paymentTokenManagement->getVisibleAvailableTokens($customerId);
    }
        

    /**
     * Gets the current admin customer ID from registry
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('customer/paymentinfo.phtml');
        }
        return $this;
    }
}
