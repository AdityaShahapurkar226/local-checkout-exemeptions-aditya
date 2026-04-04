<?php
namespace Ahy\EstateApiIntegration\Plugin\Checkout;

use Psr\Log\LoggerInterface;

class FflStepPlugin
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        $this->logger->info('=== LAYOUT PROCESSOR PLUGIN ===');
        
        // Check if observer set the flag
        $isDisabled = $this->checkoutSession->getFflStepDisabled();
        $this->logger->info('Session flag ffl_step_disabled: ' . ($isDisabled ? 'YES' : 'NO/NULL'));

        if ($isDisabled) {
            $this->logger->info('REMOVING FFL STEP');
            $this->removeFflStep($jsLayout);
        }

        return $jsLayout;
    }

    private function removeFflStep(array &$jsLayout): void
    {
        // Try all possible paths
        $paths = [
            ['components', 'checkout', 'children', 'steps', 'children', 'ffl'],
            ['components', 'checkout', 'children', 'ffl'],
            ['components', 'checkout', 'steps', 'children', 'ffl'],
        ];

        foreach ($paths as $keys) {
            if ($this->unsetNestedKey($jsLayout, $keys)) {
                $this->logger->info('FFL removed from: ' . implode('/', $keys));
                return;
            }
        }
        
        $this->logger->error('Could not find FFL step to remove');
    }

    private function unsetNestedKey(array &$array, array $keys): bool
    {
        $current = &$array;
        $lastKey = array_pop($keys);
        
        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                return false;
            }
            $current = &$current[$key];
        }
        
        if (isset($current[$lastKey])) {
            unset($current[$lastKey]);
            return true;
        }
        
        return false;
    }
}