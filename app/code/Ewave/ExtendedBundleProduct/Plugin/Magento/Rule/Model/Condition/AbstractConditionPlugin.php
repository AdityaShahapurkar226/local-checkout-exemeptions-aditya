<?php
namespace Ewave\ExtendedBundleProduct\Plugin\Magento\Rule\Model\Condition;

use Ewave\ExtendedBundleProduct\Helper\Bundle;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Quote\Address;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\SalesRule\Model\Rule\Condition\Address as ConditionAddress;

/**
 * Class AbstractConditionPlugin
 * @package Ewave\ExtendedBundleProduct\Plugin\Magento\Rule\Model\Condition
 */
class AbstractConditionPlugin
{
    /**
     * @var Bundle
     */
    protected $helper;

    /**
     * @var array
     */
    protected $conditions;

    /**
     * AbstractConditionPlugin constructor.
     * @param Bundle $helper
     * @param array $conditions
     */
    public function __construct(
        Bundle $helper,
        array $conditions = []
    ) {
        $this->helper = $helper;
        $this->conditions = $conditions;
    }

    /**
     * @param AbstractCondition $subject
     * @param bool $result
     * @param AbstractModel $model
     * @return bool
     */
    public function afterValidate(AbstractCondition $subject, $result, AbstractModel $model)
    {
        if (!($subject instanceof ConditionAddress) || !($model instanceof Address)) {
            return $result;
        }
        if (!$this->isNeedfulCondition($subject->getAttribute())) {
            return $result;
        }

        $qty = $this->helper->recalculateQtyWithBundleSeparateCount($model->getQuote());
        return $subject->validateAttribute($qty);
    }

    /**
     * @param string $condition
     * @return bool
     */
    public function isNeedfulCondition($condition)
    {
        return in_array($condition, $this->conditions);
    }
}
