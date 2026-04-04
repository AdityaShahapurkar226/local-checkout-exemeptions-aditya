<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Cart\Add\Message;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;

class PackSuccessMessage implements MessageInterface
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(ManagerInterface $messageManager, UrlInterface $urlBuilder)
    {
        $this->messageManager = $messageManager;
        $this->urlBuilder = $urlBuilder;
    }

    public function send(): void
    {
        $this->messageManager->addComplexSuccessMessage(
            'addPackSuccessMessage',
            ['cart_url' => $this->getCartUrl()]
        );
    }

    private function getCartUrl(): string
    {
        return $this->urlBuilder->getUrl('checkout/cart', ['_secure' => true]);
    }
}
