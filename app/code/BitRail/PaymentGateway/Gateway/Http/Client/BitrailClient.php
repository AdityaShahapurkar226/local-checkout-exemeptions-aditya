<?php

namespace BitRail\PaymentGateway\Gateway\Http\Client;

class BitrailClient
{
    /**
     * @var string
     */
    private $environment;

    /**
     * @throws Exception
     */
    public function __construct(string $environment)
    {
        if (!in_array($environment, ['prod', 'sandbox', 'qa'])) {
            throw new \Exception('BitRail Gateway can\'t be run');
        }
        $this->environment = $environment;
    }

    public function getApiUrl(): string
    {
        switch ($this->environment) {
            case 'prod':
                return 'https://api.bitrail.io/v1/';
            case 'sandbox':
                return 'https://api.sandbox.bitrail.io/v1/';
            default:
                return 'https://api.qa.bitrail.io/v1/';
        }
    }

    protected function getCredentials(): array
    {
        switch ($this->environment) {
            case 'prod':
                return [
                    'client_id' => $_ENV['PROD_CLIENT_ID'],
                    'client_secret' => $_ENV['PROD_CLIENT_SECRET'],
                ];
            case 'sandbox':
                return [
                    'client_id' => $_ENV['SANDBOX_CLIENT_ID'],
                    'client_secret' => $_ENV['SANDBOX_CLIENT_SECRET'],
                ];
            default:
                return [
                    'client_id' => $_ENV['QA_CLIENT_ID'],
                    'client_secret' => $_ENV['QA_CLIENT_SECRET'],
                ];
        }
    }

    private function initCurl(string $request, string $endPoint, ?array $headers = [])
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->getApiUrl() . $endPoint);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array_merge($headers, [
            'Cache-Control: no-cache',
            'Content-Type: application/x-www-form-urlencoded'
        ]));

        return $curl;
    }

    /**
     * If there are any errors in the API response - notice will be added.
     * For another way - all OK, response as array is returned and work can be continued.
     *
     * @param mixed         $response API response
     * @param callable|null $callback Callback for additional checking of response.
     *                                Should accepts $response and returns bool.
     *
     * @return array Response as array
     *
     * @throws Exception
     */
    protected function checkResponse($response, ?callable $callback = null): array
    {
        if (!$response) {
            throw new \Exception('Couldn\'t connect to BitRail Digital Wallet. Please try again later or contact BitRail support');
        }

        $response = json_decode($response, true);
        if (!$response['success'] || $response['errors'] || ($callback && !$callback($response))) {
            throw new \Exception('Couldn\'t connect to BitRail Digital Wallet. Please try again later or contact BitRail support'); // Maybe need more information from $response['errors']
        }

        return $response;
    }

    /**
     * Token request to BitRail platform as service application.
     *
     * @return string auth token
     */
    public function oauth(): string
    {
        $credentials = $this->getCredentials();

        $curl = $this->initCurl('POST', '/auth/token');
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
            'scope' => 'everything', // todo other scope
            'grant_type' => 'client_credentials',
            'client_id' => $credentials['client_id'],
            'client_secret' => $credentials['client_secret'],
            'redirect_uri' => 'none',
        ]));

        $response = curl_exec($curl);
        curl_close($curl);
        $response = $this->checkResponse($response, function (array $response) {
            return isset($response['data'][0]['access_token']);
        });

        return $response['data'][0]['access_token'];
    }

    /**
     * Verify transaction with verification_token provided by the BitRail order form.
     *
     * @return string order_token provided by BitrailWoocommerceOrderTokenizer (if all is ok)
     */
    public function verifyTransaction(string $token): string
    {
        $curl = $this->initCurl('GET', '/transactions/verify?token=' . urlencode($token), ['Authorization: Bearer ' . $this->oauth()]);

        $response = curl_exec($curl);
        curl_close($curl);
        $response = $this->checkResponse($response, function (array $response) {
            return isset($response['data'][0]['order_token']);
        });

        return $response['data'][0]['order_token'];
    }
}