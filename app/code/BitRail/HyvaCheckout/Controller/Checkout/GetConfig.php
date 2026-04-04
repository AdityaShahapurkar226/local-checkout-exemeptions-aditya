<?php

namespace Bitrail\HyvaCheckout\Controller\Checkout;

use Bitrail\HyvaCheckout\Gateway\Http\Client\BitrailClient;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use BitRail\PaymentGateway\Gateway\Http\Client\ClientMock;
use Bitrail\HyvaCheckout\Model\Config\ConfigProvider;
use Magento\Framework\HTTP\Client\Curl;

class GetConfig extends Action
{
  /**
   * @var JsonFactory
   */
  protected $resultJsonFactory;

  /**
   * @var ScopeConfigInterface
   */
  protected $scopeConfig;
  protected $configProvider;
  protected $curl;
  protected $bitrailClient;


  public function __construct(
    Context $context,
    JsonFactory $resultJsonFactory,
    ScopeConfigInterface $scopeConfig,
    Curl $curl,
    ConfigProvider $configProvider,
    BitrailClient $bitrailClient
  ) {
    $this->configProvider = $configProvider;
    $this->resultJsonFactory = $resultJsonFactory;
    $this->scopeConfig = $scopeConfig;
    $this->curl = $curl;
    $this->bitrailClient = $bitrailClient;

    parent::__construct($context);
  }

  public function execute()
  {
    $resultJson = $this->resultJsonFactory->create();
    $reqParams = $this->getRequest()->getParams();

    if (!isset($reqParams['nonceCode']) || $reqParams['nonceCode'] !== ClientMock::getNonceCode()) {
      return $resultJson->setData(['success' => false, 'error' => 'Invalid nonce code.']);
    }

    $apiUrl = $this->bitrailClient->getApiUrl();
    $authToken = $this->bitrailClient->getApiAuthToken();
    $clientInfo = $this->fetchClientInfo($apiUrl, $authToken);

    $paymentMethodTitle = __("Pay with ") . $clientInfo['clientAppName'];
    $poweredBy = __("Powered by ") . $clientInfo['appName'];

    try {
      $data = [
        'paymentMethodTitle' => $paymentMethodTitle,
        'poweredBy' => $poweredBy,
        'logo' => $clientInfo['logo'],
        'appName' => $clientInfo['appName'],
        'clientAppName' => $clientInfo['clientAppName'],
        'apiUrl' => $apiUrl,
        'authToken' => $authToken,
        'destinationVaultHandle' => $this->configProvider->getDestinationVaultHandle(),
        'description' => $this->configProvider->getOrderDescription(),
      ];

      return $resultJson->setData(['success' => true, 'data' => $data]);

    } catch (\Exception $e) {
      return $resultJson->setData(['success' => false, 'error' => $e->getMessage()]);
    }
  }

  private function fetchClientInfo($apiUrl, $authToken)
  {
    $clientId = $this->getClientIdFromJwt($authToken);
    $url = rtrim($apiUrl, '/') . "/clients/" . $clientId . "/options";

    $this->curl->get($url);
    $response = $this->curl->getBody();
    $payload = json_decode($response, true);
    $data = $payload['data'];
    $ui = $data[0]['ui'] ?? [];

    return [
      'appName' => $ui['app_name'] ?? "",
      'clientAppName' => $ui['client_app_name'],
      'logo' => [
        'large' => $ui['logo_url']['large']['svg'] ?? "",
        'small' => $ui['logo_url']['small']['svg']
      ]
    ];
  }

  private function getClientIdFromJwt($token)
  {
    $payload = $this->getJwtPayload($token);
    return $payload['cid'] ?? null;
  }

  private function getJwtPayload(string $token): array
  {
    $parts = explode('.', $token);

    if (count($parts) !== 3) {
      throw new \Exception("Invalid JWT token");
    }

    $payload = $this->decodeJwtPayload($parts[1]);

    if ($payload === null) {
      throw new \Exception("Failed to decode JSON payload");
    }

    return $payload;
  }

  private function decodeJwtPayload(string $payload): ?array
  {
    $decodedPayload = $this->decodeBase64Url($payload);
    return json_decode($decodedPayload, true) ?: null;
  }

  private function decodeBase64Url($base64Url)
  {
    $base64 = strtr($base64Url, '-_', '+/');
    $padding = strlen($base64) % 4;
    if ($padding) {
      $base64 .= str_repeat('=', 4 - $padding);
    }
    return base64_decode($base64);
  }

}
