<?php

namespace Ahy\EfflApiIntegration\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class DealersActions extends Column
{
    const URL_PATH_EDIT = 'ahy_efflapiintegration/orchid/edit';
    const URL_PATH_DELETE = 'ahy_efflapiintegration/orchid/delete';

    protected $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
{
    foreach ($dataSource['data']['items'] as &$item) {
        if (isset($item['entity_id'])) {
            $item[$this->getData('name')] = [
                'edit' => [
                    'href' => $this->urlBuilder->getUrl(self::URL_PATH_EDIT, ['entity_id' => $item['entity_id']]),
                    'label' => __('Edit')
                ],
                'delete' => [
                    'href' => $this->urlBuilder->getUrl(self::URL_PATH_DELETE, ['entity_id' => $item['entity_id']]),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete Dealer'),
                        'message' => __('Are you sure you want to delete this dealer?')
                    ]
                ]
            ];
        }
    }

    return $dataSource;
}

}
