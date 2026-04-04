<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\Checkout\Controller\Cart;

use Amasty\Mostviewed\Model\ConfigProvider;
use Magento\Checkout\Controller\Cart\CouponPost;
use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;

class CouponPostPlugin
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Session $checkoutSession,
        ManagerInterface $messageManager,
        ConfigProvider $configProvider,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        $this->configProvider = $configProvider;
        $this->logger = $logger;
    }

    /**
     * @param CouponPost $subject
     * @param ResultInterface $result
     * @return ResultInterface
     */
    public function afterExecute(CouponPost $subject, ResultInterface $result)
    {
        try {
            if (!$this->configProvider->isApplyCartRule()
                && $this->checkoutSession->getQuote()->getCouponCode()
                && $this->checkoutSession->getAppliedPackIds()
            ) {
                $this->messageManager->addNoticeMessage(__('No additional discounts (including coupons)
                            can be applied to products from bundle pack.'));
            }
        } catch (NoSuchEntityException $e) {
            $this->logError($e->getMessage());
        } catch (LocalizedException $e) {
            $this->logError($e->getMessage());
        }

        return $result;
    }

    /**
     * @param string $message
     */
    private function logError(string $message)
    {
        $this->logger->error($message);
    }
}
