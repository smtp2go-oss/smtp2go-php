<?php

use GuzzleHttp\Client;
use SMTP2GO\ApiClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use SMTP2GO\Service\Service;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Exception\RequestException;

class GenericServiceTest extends TestCase
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
        $result  = $client->consume($service);
        $this->assertTrue($result);
    }
}
