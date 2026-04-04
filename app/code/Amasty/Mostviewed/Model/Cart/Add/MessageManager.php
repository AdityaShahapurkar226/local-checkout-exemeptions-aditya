<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Cart\Add;

use Amasty\Mostviewed\Model\Cart\Add\Message\MessageInterface;
use InvalidArgumentException;
use Magento\Framework\Message\ManagerInterface;

class MessageManager
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var MessageInterface[]
     */
    private $messagePool;

    public function __construct(ManagerInterface $messageManager, array $messagePool)
    {
        $this->validateMessagePool($messagePool);
        $this->messageManager = $messageManager;
        $this->messagePool = $messagePool;
    }

    public function execute(array $messages): void
    {
        $messages = array_unique($messages);
        foreach ($messages as $message) {
            if (isset($this->messagePool[$message])) {
                $this->messagePool[$message]->send();
            } else {
                $this->messageManager->addSuccessMessage($message);
            }
        }
    }

    /**
     * @param MessageInterface[] $messagePool
     * @throws InvalidArgumentException
     */
    private function validateMessagePool(array $messagePool): void
    {
        foreach ($messagePool as $message) {
            if (!$message instanceof MessageInterface) {
                throw new InvalidArgumentException(
                    sprintf('%s does not implement %s.', get_class($message), MessageInterface::class)
                );
            }
        }
    }
}
