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
namespace Webkul\SellerSubAccount\Block\Adminhtml\Customer\Edit\Tab\Grid;

use Magento\Customer\Controller\RegistryConstants;
use Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\Grid\Collection;
use Webkul\SellerSubAccount\Model\SubAccount as SubAccountModel;
use Webkul\SellerSubAccount\Block\Adminhtml\Customer\Edit\Tab\Grid\Renderer\Permissions as RenderPermission;

class SubAccount extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry = null;
    /**
     * @var Collection
     */
    public $_collection;
    /**
     * @var SubAccountModel
     */
    public $_subAccount;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data            $backendHelper
     * @param \Magento\Framework\Registry             $coreRegistry
     * @param Collection                              $collection
     * @param SubAccountModel                         $subAccount
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        Collection $collection,
        SubAccountModel $subAccount,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_collection = $collection;
        $this->_subAccount = $subAccount;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritdoc
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('seller_product_grid');
        $this->setDefaultSort('entity_at');
        $this->setUseAjax(true);
    }

    /**
     * Get Customer Id
     *
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Apply various selection filters to prepare the sales order grid collection.
     *
     * @return $this
     */
    public function _prepareCollection()
    {
        $collection = $this->_collection
        ->addFieldToFilter(
            'seller_id',
            $this->getCustomerId()
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare Columns
     *
     * @return Extended
     */
    public function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'sortable' => true
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name'
            ]
        );
        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'index' => 'email',
            ]
        );
        $this->addColumn(
            'permission_type',
            [
                'header' => __('Permissions'),
                'index' => 'permission_type',
                'filter' => false,
                'renderer' => RenderPermission::class
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Active'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_subAccount->getStatuses()
            ]
        );
        $this->addColumn(
            'customer_created_at',
            [
                'header' => __('Account Since'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'customer_created_at'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Retrieve the Url for a specified sales order row.
     *
     * @param \Webkul\SellerSubAccount\Model\SubAccount|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('sellersubaccount/account/edit', ['id' => $row->getId()]);
    }

    /**
     * @inheritdoc
     */
    public function getGridUrl()
    {
        return $this->getUrl('sellersubaccount/account/grid', ['_current' => true]);
    }
}
