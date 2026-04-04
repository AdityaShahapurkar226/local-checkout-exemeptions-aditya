<?php

namespace Ahy\SavedCC\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Ahy\SavedCC\Helper\Config;

class ConfigProvider implements ArgumentInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }
}
