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
namespace Webkul\SellerSubAccount\Model\ResourceModel;

class SubAccount extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Store model.
     *
     * @var null|\Magento\Store\Model\Store
     */
    public $_store = null;

    /**
     * Initialize resource model.
     */
    public function _construct()
    {
        $this->_init('marketplace_sub_accounts', 'entity_id');
    }

    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param mixed $value
     * @param string $field
     *
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field === null) {
            $field = 'identifier';
        }

        return parent::load($object, $value, $field);
    }

    /**
     * Set store model.
     *
     * @param \Magento\Store\Model\Store $store
     *
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $store;

        return $this;
    }

    /**
     * Retrieve store model.
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->_store);
    }
}
