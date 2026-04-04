<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Model\ResourceModel\Option;

use Magento\Catalog\Model\Product\Attribute\Source\Status;

/**
 * Bundle Options Resource Collection
 */
class Collection extends \Magento\Bundle\Model\ResourceModel\Option\Collection
{
    /**
     * All item ids cache
     *
     * @var array
     */
    protected $itemIds;

    /**
     * True when selections appended
     *
     * @var bool
     */
    protected $selectionsAppended = false;

    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Http\Context $httpContext,
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->httpContext = $httpContext;
        $this->helper = $helper;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * Init model and resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Bundle\Model\Option::class,
            \Magento\Bundle\Model\ResourceModel\Option::class
        );
    }

    /**
     * Append selection to options
     * stripBefore - indicates to reload
     * appendAll - indicates do we need to filter by saleable and required custom options
     *
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $selectionsCollection
     * @param bool $stripBefore
     * @param bool $appendAll
     * @return \Magento\Framework\DataObject[]
     */
    public function appendSelections($selectionsCollection, $stripBefore = false, $appendAll = true)
    {
        if ($stripBefore) {
            $this->_stripSelections();
        }

        if (!$this->_selectionsAppended) {
            foreach ($selectionsCollection->getItems() as $key => $selection) {
                //get foreach collection
                $this->foreachSelectionCollection(
                    $selectionsCollection,
                    $key,
                    $selection,
                    $appendAll
                );
            }
            $this->_selectionsAppended = true;
        }

        return $this->getItems();
    }

    /**
     * @param \Magento\Bundle\Model\ResourceModel\Selection\Collection $selectionsCollection
     * @param string $key
     * @param \Magento\Framework\DataObject $selection
     * @param bool $appendAll
     */
    private function foreachSelectionCollection(
        $selectionsCollection,
        $key,
        $selection,
        $appendAll
    ) {
        $option = $this->getItemById($selection->getOptionId());
        if ($option) {
            $checkCustomer = $this->helper->checkCustomer(
                $this->httpContext->getValue(
                    \Bss\ProductStockAlert\Model\Customer\Context::CONTEXT_CUSTOMER_GROUP_ID
                )
            );
            $allowStock = $this->helper->isStockAlertAllowed();
            if ($checkCustomer && $allowStock) {
                if ($appendAll || !$selection->getRequiredOptions()) {
                    $selection->setOption($option);
                    $option->addSelection($selection);
                } else {
                    $selectionsCollection->removeItemByKey($key);
                }
            } else {
                if ($appendAll ||
                    ((int) $selection->getStatus()) === Status::STATUS_ENABLED && !$selection->getRequiredOptions()
                ) {
                    $selection->setOption($option);
                    $option->addSelection($selection);
                } else {
                    $selectionsCollection->removeItemByKey($key);
                }
            }
        }
    }
}
