<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Ui\DataProvider\SubAccount;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SessionManagerInterface;
use Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\Collection;
use Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\CollectionFactory;
use Webkul\SellerSubAccount\Helper\Data as Helper;

/**
 * Class DataProvider data for subAccount
 *
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    public $collection;

    /**
     * @var array
     */
    public $loadedData;

    /**
     * @var Helper
     */
    public $helper;

    /**
     * @var SessionManagerInterface
     */
    public $session;

    /**
     * Constructor
     *
     * @param mixed $name
     * @param mixed $primaryFieldName
     * @param mixed $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Helper $helper
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        Helper $helper,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->_helper = $helper;
        $this->sessionManager = $sessionManager;
        $this->collection = $collectionFactory->create();
        $this->collection->addFieldToSelect('*');
    }

    /**
     * Get session object.
     *
     * @return SessionManagerInterface
     */
    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = $this->sessionManager;
        }

        return $this->session;
    }

    /**
     * Get data.
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var SubAccount $subAccount */
        foreach ($items as $subAccount) {
            $result['sub_account'] = $subAccount->getData();
            $this->loadedData[$subAccount->getId()] = $result;
            $id = $subAccount->getId();
            $customerData = $this->_helper->getCustomerById($subAccount->getCustomerId());
            $firstname = $customerData->getFirstname();
            $lastname = $customerData->getLastname();
            $email = $customerData->getEmail();
            $this->loadedData[$id]['sub_account']['firstname'] = $firstname;
            $this->loadedData[$id]['sub_account']['lastname'] = $lastname;
            $this->loadedData[$id]['sub_account']['email'] = $email;
            
        }
        return $this->loadedData;
    }

    /**
     * Get Meta
     *
     * @return void
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        $p_s=$this->_helper->isCustomerFieldReq("prefix_show");
        $s_s=$this->_helper->isCustomerFieldReq("suffix_show");
        $mn_s=$this->_helper->isCustomerFieldReq("middlename_show");
        $dob_s=$this->_helper->isCustomerFieldReq("dob_show");
        $gn_s=$this->_helper->isCustomerFieldReq("gender_show");
        $tv_s=$this->_helper->isCustomerFieldReq("taxvat_show");
        $tl_s=$this->_helper->isCustomerFieldReq("telephone_show");
        $cm_s=$this->_helper->isCustomerFieldReq("company_show");
        $fx_s=$this->_helper->isCustomerFieldReq("fax_show");
       
           $meta['sub_account']['children']['prefix']['arguments']['data']['config']['visible'] = $p_s;
           $meta['sub_account']['children']['suffix']['arguments']['data']['config']['visible'] = $s_s;
           $meta['sub_account']['children']['middlename']['arguments']['data']['config']['visible'] = $mn_s;
           $meta['sub_account']['children']['dob']['arguments']['data']['config']['visible'] = $dob_s;
           $meta['sub_account']['children']['gender']['arguments']['data']['config']['visible'] = $gn_s;
           $meta['sub_account']['children']['taxvat']['arguments']['data']['config']['visible'] = $tv_s;
        if ($this->_helper->isReqAddressEnable()) {
           
            $meta['sub_account']['children']['telephone']['arguments']['data']['config']['visible'] = $tl_s;
            $meta['sub_account']['children']['company']['arguments']['data']['config']['visible'] = $cm_s;
            $meta['sub_account']['children']['fax']['arguments']['data']['config']['visible'] = $fx_s;

            $meta['sub_account']['children']['country_id']['arguments']['data']['config']['visible'] = 1;
            $meta['sub_account']['children']['region_id']['arguments']['data']['config']['visible'] = 1;
            $meta['sub_account']['children']['city']['arguments']['data']['config']['visible'] = 1;
            $meta['sub_account']['children']['postalcode']['arguments']['data']['config']['visible'] = 1;
            $meta['sub_account']['children']['vat_id']['arguments']['data']['config']['visible'] = 1;
           
        } else {

            $meta['sub_account']['children']['telephone']['arguments']['data']['config']['visible'] = 0;
            $meta['sub_account']['children']['company']['arguments']['data']['config']['visible'] =0;
            $meta['sub_account']['children']['fax']['arguments']['data']['config']['visible'] = 0;

            $meta['sub_account']['children']['country_id']['arguments']['data']['config']['visible'] = 0;
            $meta['sub_account']['children']['region_id']['arguments']['data']['config']['visible'] = 0;
            $meta['sub_account']['children']['city']['arguments']['data']['config']['visible'] = 0;
            $meta['sub_account']['children']['postalcode']['arguments']['data']['config']['visible'] = 0;
            $meta['sub_account']['children']['vat_id']['arguments']['data']['config']['visible'] = 0;
       
        }

        return $meta;
    }
}
