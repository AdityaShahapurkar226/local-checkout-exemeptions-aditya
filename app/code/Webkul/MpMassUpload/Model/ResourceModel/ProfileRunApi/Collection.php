<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpMassUpload
 * @author Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\MpMassUpload\Model\ResourceModel\ProfileRunApi;

/**
 * Collection Class CURD
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Webkul\MpMassUpload\Model\ProfileRunApi::class,
            \Webkul\MpMassUpload\Model\ResourceModel\ProfileRunApi::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.id';
    }
}
