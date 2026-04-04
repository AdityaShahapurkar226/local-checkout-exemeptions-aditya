<?php

namespace Ahy\EfflApiIntegration\Block\Adminhtml\Orchid\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData()
    {
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'on_click' => 'require(["uiRegistry"], function(registry){
                var form = registry.get("orchid_ffl_dealers_form.orchid_ffl_dealers_form");
                if (form) {
                    form.submit();
                } else {
                    console.error("Form component not found");
                }
            });',
            'sort_order' => 90
        ];
    }
}
