<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Ui\Component\Listing\Columns;

use Amasty\Mostviewed\Model\Pack\Analytic\GetZoneNumber;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class OrdersCount extends Column
{
    /**
     * @var GetZoneNumber
     */
    private $getZoneNumber;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        GetZoneNumber $getZoneNumber,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->getZoneNumber = $getZoneNumber;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                $item[$name] = sprintf(
                    '<span class="amrelated-level-block -level-%d">%d</span>',
                    $this->getZoneNumber->execute((int) $item[$name]),
                    $item[$name]
                );
            }
        }

        return $dataSource;
    }
}
