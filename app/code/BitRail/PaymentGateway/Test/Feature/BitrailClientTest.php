<?php

namespace BitRail\PaymentGateway\Test\Feature;

use PHPUnit\Framework\TestCase;

use BitRail\PaymentGateway\Gateway\Http\Client\BitrailClient;
use BitRail\PaymentGateway\Test\Stubs\BitrailClientStub;

class BitrailClientTest extends TestCase
{
    /**
     * @var BitrailClientStub
     */
    private $prodClient;
    /**
     * @var BitrailClientStub
     */
    private $sandboxClient;
    /**
     * @var BitrailClientStub
     */
    private $qaClient;

    public function setUp(): void
    {
        $this->prodClient = new BitrailClientStub('prod');
        $this->sandboxClient = new BitrailClientStub('sandbox');
        $this->qaClient = new BitrailClientStub('qa');

        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->prodClient = null;
        $this->sandboxClient = null;
        $this->qaClient = null;

        parent::tearDown();
    }

    public function testFailedConstruct()
    {
        $this->expectException(\Exception::class);
        new BitrailClient('some environment');
    }

    public function testGetApiUrl()
    {
        $this->assertEquals($this->prodClient->getApiUrl(), 'https://api.bitrail.io/v1/');
        $this->assertEquals($this->sandboxClient->getApiUrl(), 'https://api.sandbox.bitrail.io/v1/');
        $this->assertEquals($this->qaClient->getApiUrl(), 'https://api.qa.bitrail.io/v1/');
    }

    public function testGetCredentials()
    {
        $prodClientCredentials = $this->prodClient->getCredentials();
        $this->assertEquals(count($prodClientCredentials), 2);
        $this->assertArrayHasKey('client_id', $prodClientCredentials);
        $this->assertArrayHasKey('client_secret', $prodClientCredentials);
        $this->assertEquals($prodClientCredentials['client_id'], 'rjwo4vfwhyo899xadjc6kolal7mdte');
        $this->assertEquals($prodClientCredentials['client_secret'], '8Mf0o1vr4IyY9VjoZQYOu4ciF3XzRUYq3H6X4rUQE3lFOAlhFWohjA4IEpa7Lqqm');

        $sandboxClientCredentials = $this->sandboxClient->getCredentials();
        $this->assertEquals(count($sandboxClientCredentials), 2);
        $this->assertArrayHasKey('client_id', $sandboxClientCredentials);
        $this->assertArrayHasKey('client_secret', $sandboxClientCredentials);
        $this->assertEquals($sandboxClientCredentials['client_id'], 'sbnrhkic4il79kcn6lrigytj9wh2w9');
        $this->assertEquals($sandboxClientCredentials['client_secret'], 'L8N1Vfq_JLELMAw_0RPktZV_IQEfgytc5bMtl2j_4Ii05HksgE8Zb0KWasUJ-otX');

        $qaClientCredentials = $this->qaClient->getCredentials();
        $this->assertEquals(count($qaClientCredentials), 2);
        $this->assertArrayHasKey('client_id', $qaClientCredentials);
        $this->assertArrayHasKey('client_secret', $qaClientCredentials);
        $this->assertEquals($qaClientCredentials['client_id'], 'u17m43ejdnq12k1m2dntay63c0md71');
        $this->assertEquals($qaClientCredentials['client_secret'], 'XbJo2cdLGlaMG0OoSYly5q0ND1BuaBOjGGBSNOu0VujM5Q_gyAkE3Z-Csx8Avvk9');
    }

    public function testCheckResponse()
    {
        $response = '{"success": true, "errors": []}';
        $response = $this->prodClient->checkResponse($response);
        $this->assertTrue(is_array($response));
        $this->assertEquals(count($response), 2);
        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('errors', $response);
        $this->assertEquals(count($response['errors']), 0);

        $oauthResponse = '{"success": true, "errors": [], "data": [{"access_token": "test"}]}';
        $oauthResponse = $this->prodClient->checkResponse($oauthResponse, function (array $response) {
            return isset($response['data'][0]['access_token']);
        });
        $this->assertTrue(is_array($oauthResponse));
        $this->assertEquals(count($oauthResponse), 3);
        $this->assertArrayHasKey('success', $oauthResponse);
        $this->assertArrayHasKey('errors', $oauthResponse);
        $this->assertEquals(count($oauthResponse['errors']), 0);
        $this->assertArrayHasKey('data', $oauthResponse);
        $this->assertEquals(count($oauthResponse['data']), 1);
        $this->assertArrayHasKey('access_token', $oauthResponse['data'][0]);
    }

    public function testFailedCheckResponse_1()
    {
        $this->expectException(\Exception::class);
        $response = '{"success": false, "errors": ["some error"]}';
        $this->prodClient->checkResponse($response);
    }

    public function testFailedCheckResponse_2()
    {
        $this->expectException(\Exception::class);
        $response = '{"success": true, "errors": ["some error"]}'; // But what if
        $this->prodClient->checkResponse($response);
    }

    public function testFailedCheckResponse_3()
    {
        $this->expectException(\Exception::class);
        $response = '{"success": true, "errors": []}';
        $this->prodClient->checkResponse($response, function (array $response) { return false; });
    }
}
