<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Ui\Component\Listing\Columns;

use Amasty\Mostviewed\Model\OptionSource\BlockPosition as BlockPositionModel;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class RuleType extends Column
{
    /**
     * @var BlockPositionModel
     */
    private $blockPosition;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        BlockPositionModel $blockPosition,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->blockPosition = $blockPosition;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['block_position'])) {
                    $ruleType = $this->blockPosition->getTypeByValue($item['block_position']);
                    $item[$this->getData('name')] = $ruleType['label'];
                }
            }
        }

        return $dataSource;
    }
}
