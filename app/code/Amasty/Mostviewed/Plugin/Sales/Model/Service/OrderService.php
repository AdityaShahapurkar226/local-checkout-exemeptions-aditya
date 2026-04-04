<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\Sales\Model\Service;

use Amasty\Mostviewed\Api\ClickRepositoryInterface;
use Amasty\Mostviewed\Api\Data\ClickInterface;
use Amasty\Mostviewed\Api\AnalyticRepositoryInterface;
use Amasty\Mostviewed\Api\Data\AnalyticInterface;
use Amasty\Mostviewed\Model\Analytics\Collector\ClickCollector;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Customer\Model\Visitor;
use Magento\Sales\Model\Service\OrderService as NativeOrderService;

class OrderService
{
    public const ORDERS_MADE = 'orders_made';

    public const REVENUE = 'revenue';

    /**
     * @var ClickRepositoryInterface
     */
    private $clickRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Visitor
     */
    private $visitor;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var array
     */
    private $analytics = [
        self::ORDERS_MADE => [],
        self::REVENUE     => []
    ];

    /**
     * @var AnalyticRepositoryInterface
     */
    private $analyticRepository;

    /**
     * @var array
     */
    private $visitorData;

    /**
     * @var ClickCollector
     */
    private $collector;

    public function __construct(
        ClickRepositoryInterface $clickRepository,
        AnalyticRepositoryInterface $analyticRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        Visitor $visitor,
        SessionManagerInterface $session,
        ClickCollector $collector
    ) {
        $this->clickRepository = $clickRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->visitor = $visitor;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->analyticRepository = $analyticRepository;
        $this->visitorData = $session->getVisitorData();
        $this->collector = $collector;
    }

    /**
     * @param NativeOrderService $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterPlace($subject, OrderInterface $order)
    {
        if (isset($this->visitorData['visitor_id'])) {
            $visitorId = $this->visitorData['visitor_id'];
            foreach ($order->getItems() as $orderItem) {
                $productIds = [$orderItem->getProductId()];
                $productOptions = $orderItem->getData('product_options');
                if ($productOptions && isset($productOptions['super_product_config']['product_id'])) {
                    $productIds[] = $productOptions['super_product_config']['product_id'];
                }

                if ($clickEvent = $this->getClickEvent($productIds, $visitorId)) {
                    $analytic = $this->loadAnalytic($clickEvent->getBlockId(), self::ORDERS_MADE);
                    $analytic->setCounter($analytic->getCounter() + 1);
                    $analytic = $this->loadAnalytic($clickEvent->getBlockId(), self::REVENUE);
                    $analytic->setCounter($analytic->getCounter() + $orderItem->getRowTotalInclTax());
                }
            }
            if ($this->clickRepository->getCountLoaded() > 0) {
                $this->collector->execute();
                $this->saveAnalytics();
            }
        }

        return $order;
    }

    /**
     * @param array $productIds
     * @param $visitorId
     *
     * @return ClickInterface|null
     */
    private function getClickEvent($productIds, $visitorId)
    {
        $clickEvent = null;
        $this->searchCriteriaBuilder
            ->addFilter(ClickInterface::PRODUCT_ID, $productIds, 'in')
            ->addFilter(ClickInterface::VISITOR_ID, $visitorId);
        /** @var SortOrder $sortOrder */
        $sortOrder = $this->sortOrderBuilder->setField(ClickInterface::ID)
            ->setDirection(SortOrder::SORT_DESC)
            ->create();
        $this->searchCriteriaBuilder->setSortOrders([$sortOrder]);
        $clickEvents = $this->clickRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();
        if (isset($clickEvents[0])) {
            $clickEvent = $clickEvents[0];
        }

        return $clickEvent;
    }

    /**
     * @param int $blockId
     * @param string $type
     *
     * @return \Amasty\Mostviewed\Model\Analytics\Analytic
     */
    private function loadAnalytic($blockId, $type)
    {
        if (!isset($this->analytics[$type][$blockId])) {
            $view = $this->analyticRepository->getList(
                $this->searchCriteriaBuilder
                    ->addFilter(AnalyticInterface::BLOCK_ID, $blockId)
                    ->addFilter(AnalyticInterface::TYPE, $type)
                    ->setPageSize(1)
                    ->create()
            )->getItems();
            if (isset($view[0])) {
                $view = $view[0];
            } else {
                $view = $this->analyticRepository->getNew();
            }
            $this->analytics[$type][$blockId] = $view;
        }

        return $this->analytics[$type][$blockId];
    }

    /**
     *
     */
    private function saveAnalytics()
    {
        foreach ($this->analytics as $type => $analytics) {
            /** @var \Amasty\Mostviewed\Model\Analytics\Analytic $analytic */
            foreach ($analytics as $blockId => $analytic) {
                $analytic->setBlockId($blockId);
                $analytic->setType($type);
                $this->analyticRepository->save($analytic);
            }
        }
    }
}
