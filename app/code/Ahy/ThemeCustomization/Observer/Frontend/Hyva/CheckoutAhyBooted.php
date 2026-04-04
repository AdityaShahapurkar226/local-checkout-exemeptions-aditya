<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\ThemeCustomization\Observer\Frontend\Hyva;

use Magento\Checkout\Model\Session as CheckoutSession;
use Hyva\Checkout\Model\Session as SessionCheckoutConfig;
use Ahy\ThemeCustomization\Logger\Logger as KlaviyoLogger;

class CheckoutAhyBooted implements \Magento\Framework\Event\ObserverInterface
{

    private $checkoutSession;
    protected SessionCheckoutConfig $sessionCheckoutConfig;
    private $_listId = 'QYgiGn'; //Your Klaviyo list ID here
    private $_apiKey = 'pk_9cdbbd62f7b844b630b96a601ee3606c19'; //Your Klaviyo private API key here
    private $_klaviyoLogger;

    public function __construct(
        CheckoutSession $checkoutSession,
        SessionCheckoutConfig $sessionCheckoutConfig,
        KlaviyoLogger $klaviyoLogger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->sessionCheckoutConfig = $sessionCheckoutConfig;
        $this->_klaviyoLogger = $klaviyoLogger;
    }
    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $quote = $this->checkoutSession->getQuote();
        $klaviyoOptIn = $this->checkoutSession->getData('klaviyoOptIn') ?? false;
        $isKlaviyoApiRequestSent = $this->checkoutSession->getData('isKlaviyoApiRequestSent') ?? false;
        if ($quote->getCustomerEmail()) {
            $email = $quote->getCustomerEmail();
            $current = $this->sessionCheckoutConfig->getCurrentStep();
            $currentStepName = $current['name'];
            if ($currentStepName == 'verify' || $currentStepName == 'payment') {
                if($klaviyoOptIn && !$isKlaviyoApiRequestSent){
                    $result = $this->sendProfileToKlaviyo($email);
                    $this->checkoutSession->setData('isKlaviyoApiRequestSent', true);
                }
            }
        }
    }

    public function sendProfileToKlaviyo($email){
        $curl = curl_init();

        // Correctly formatted JSON payload
        $jsonPayload = json_encode([
            "data" => [
                "type" => "profile-subscription-bulk-create-job",
                "attributes" => [
                    "profiles" => [
                        "data" => [
                            [
                                "type" => "profile",
                                "attributes" => [
                                    "email" => $email,
                                    "subscriptions" => [
                                        "email" => [
                                            "marketing" => [
                                                "consent" => "SUBSCRIBED"
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    "custom_source" => "everest.com"
                ],
                "relationships" => [
                    "list" => [
                        "data" => [
                            "type" => "list",
                            "id" => $this->_listId
                        ]
                    ]
                ]
            ]
        ]);

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://a.klaviyo.com/api/profile-subscription-bulk-create-jobs/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonPayload, // Use the correctly formatted JSON payload
            CURLOPT_HTTPHEADER => array(
                'revision: 2024-05-15',
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Klaviyo-API-Key ' . $this->_apiKey,
            ),
        ));

        $response = curl_exec($curl);
        $this->_klaviyoLogger->info($response);
        curl_close($curl);
    }

}
