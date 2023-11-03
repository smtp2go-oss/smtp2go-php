<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SMTP2GO\ApiClient;
use SMTP2GO\Service\Service;

class RetrySendTest extends TestCase
{
    /**
     * @covers  \SMTP2GO\Service\Service
     * @covers \SMTP2GO\ApiClient
     * @return void
     */
    public function test_setting_retry()
    {
        $service = new Service('stats/email_summary',[]);
        $apiClient = new ApiClient(SMTP2GO_API_KEY);
        $apiClient->setMaxSendAttempts(5);
        $apiClient->setTimeout(2);
        $apiClient->setTimeoutIncrement(1);
        $this->assertTrue($apiClient->consume($service));

        print_r($apiClient->getDebug());
    }
}
