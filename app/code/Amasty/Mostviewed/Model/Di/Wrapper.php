<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Di;

use Magento\Framework\Module\Manager;

class Wrapper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManagerInterface;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManagerInterface,
        Manager $moduleManager,
        $name = ''
    ) {
        $this->objectManagerInterface = $objectManagerInterface;
        $this->name = $name;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return bool|mixed
     */
    public function __call($name, $arguments)
    {
        $result = false;
        if ($this->isAvailable()) {
            $object = $this->objectManagerInterface->get($this->name);

            // @codingStandardsIgnoreLine
            $result = call_user_func_array([$object, $name], $arguments);
        }

        return $result;
    }

    public function isAvailable(): bool
    {
        return $this->name
            && class_exists($this->name)
            && $this->moduleManager->isEnabled($this->getModuleName($this->name));
    }

    private function getModuleName(string $class): string
    {
        $class = ltrim($class, '\\');
        $parts = preg_split('@[\\\_]@', $class);
        $parts = array_filter($parts);

        if (count($parts) < 2) {
            throw new \InvalidArgumentException(
                (string)__('Provided argument is not in PSR-0 or underscore notation.')
            );
        }

        return sprintf(
            '%1s_%2s',
            ucfirst($parts[0]),
            ucfirst($parts[1])
        );
    }
}
