<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\Mui;

use Amasty\Mostviewed\Controller\Adminhtml\Product\Mui\Render as RenderController;
use Amasty\Mostviewed\Model\ResourceModel\RuleIndex;
use Magento\Framework\Exception\NoSuchEntityException;

class Render
{
    /**
     * @var \Amasty\Mostviewed\Model\Repository\GroupRepository
     */
    private $groupRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        \Amasty\Mostviewed\Model\Repository\GroupRepository $groupRepository,
        \Magento\Framework\Registry $registry
    ) {
        $this->groupRepository = $groupRepository;
        $this->registry = $registry;
    }

    /**
     * @param RenderController $renderController
     */
    public function beforeExecute(RenderController $renderController)
    {
        $request = $renderController->getRequest();
        if ($conditions = $request->getParam('rule', null)) {
            $relation = $request->getParam('relation') . '_show';
            $group = $this->getGroup($request);

            $group->setRelation($relation);
            $group->setShowForOutOfStock($request->getParam('for_out_of_stock', 0));
            $group->loadPost($conditions);

            $isConditionsExist = $group->getConditions()->getConditions()
                || $group->getWhereConditions()->getConditions();
            $this->registry->register(
                \Amasty\Mostviewed\Ui\DataProvider\Product\ProductDataProvider::PRODUCTS_KEY,
                $isConditionsExist ? $group->getMatchingProductIdsByGroup() : false
            );
        }
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface|\Amasty\Mostviewed\Model\Group
     */
    protected function getGroup($request)
    {
        try {
            /** @var \Amasty\Mostviewed\Model\Group $group */
            $group = $this->groupRepository->getById($request->getParam('group_id'));
        } catch (NoSuchEntityException $entityException) {
            $group = $this->groupRepository->getNew();
            $group->setStores('0');
        }

        return $group;
    }
}
