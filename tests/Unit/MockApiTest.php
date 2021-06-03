<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SMTP2GO\ApiClient;
use SMTP2GO\Service\Service;

class MockApiTest extends TestCase
{
    /**
     * @covers  \SMTP2GO\Service\Service
     * @covers \SMTP2GO\ApiClient
     * @return void
     */
    public function testSuccessfulServiceApiCall()
    {
        $mock = new MockHandler([
            new Response(200, [], '{"request_id": "65e68938-c332-11eb-8a00-f23c9216ceac", "data": {"emails": 56, "rejects": 0, "softbounces": 4, "hardbounces": 0, "bounce_percent": "7.14"}}'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient   = new Client(['handler' => $handlerStack]);

        $service = new Service('stats/email_bounces');
        $client  = new ApiClient(SMTP2GO_API_KEY);
        $client->setHttpClient($httpClient);
        $result = $client->consume($service);
        $this->assertTrue($result);
    }

    /**
     * @covers  \SMTP2GO\Service\Service
     * @covers \SMTP2GO\ApiClient
     * @return void
     */
    public function testFailingServiceApiCall()
    {
        $mock = new MockHandler([
            new Response(400, [], '{
                "request_id": "22e5acba-43bf-11e6-ae42-408d5cce2644",
                "data": {
                  "error": "You do not have permission to access this API endpoint",
                  "error_code": "E_ApiResponseCodes.ENDPOINT_PERMISSION_DENIED"
                }
              }'),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $httpClient   = new Client(['handler' => $handlerStack]);

        $service = new Service('stats/email_bounces');
        $client  = new ApiClient(SMTP2GO_API_KEY);

        $client->setHttpClient($httpClient);

        $result = $client->consume($service);

        $this->assertNotEmpty($client->getLastRequest());
        $this->assertFalse($result);
    }
}