<?php

namespace Bitrail\HyvaCheckout\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class Environment implements ArrayInterface
{
  /**
   * Return options as an array
   *
   * @return array
   */
  public function toOptionArray()
  {
    return [
      ['value' => 'prod', 'label' => __('Production')],
      ['value' => 'sandbox', 'label' => __('Sandbox')],
      ['value' => 'qa', 'label' => __('QA')],
    ];
  }
}
