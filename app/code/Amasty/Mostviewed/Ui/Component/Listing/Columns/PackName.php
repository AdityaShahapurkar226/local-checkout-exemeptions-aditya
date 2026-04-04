<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Ui\Component\Listing\Columns;

use Amasty\Mostviewed\Model\Pack\IsExist as IsPackExist;
use Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\PackSales\Table;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class PackName extends Column
{
    /**
     * @var IsPackExist
     */
    private $isPackExist;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        IsPackExist $isPackExist,
        UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->isPackExist = $isPackExist;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($this->isPackExist->execute((int) $item[Table::PACK_ID_COLUMN])) {
                    $item[$this->getData('name')] = sprintf(
                        '<a href="%s">%s</a>',
                        $this->urlBuilder->getUrl('amasty_mostviewed/pack/edit', [
                            'id' => (int) $item[Table::PACK_ID_COLUMN]
                        ]),
                        $item[$this->getData('name')]
                    );
                } else {
                    $item[$this->getData('name')] = __('%1 (Removed)', $item[$this->getData('name')]);
                }
            }
        }

        return $dataSource;
    }
}
