<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SMTP2GO\ApiClient;
use SMTP2GO\Service\Service;

class SetRegionTest extends TestCase
{
    /**
     * @covers  \SMTP2GO\Service\Service
     * @covers \SMTP2GO\ApiClient
     * @return void
     */
    public function testSetRegion()
    {
        $client = new ApiClient(SMTP2GO_API_KEY);
        $client->setApiRegion('eu');
        $this->assertEquals('eu', $client->getApiRegion());
    }

    /**
     * @covers  \SMTP2GO\Service\Service
     * @covers \SMTP2GO\ApiClient
     * @return void
     */
    public function testGetApiUrlUsesTheRegionSetByTheUser()
    {
        $client = new ApiClient(SMTP2GO_API_KEY);
        $client->setApiRegion('eu');
        $this->assertEquals('https://eu-api.smtp2go.com/v3/', $client->getApiUrl());
    }

    /**
     * @covers  \SMTP2GO\Service\Service
     * @covers \SMTP2GO\ApiClient
     * @return void
     */
    public function testGetApiUrlUsesTheDefaultUrlWhenNoRegionIsSet()
    {
        $client = new ApiClient(SMTP2GO_API_KEY);
        $this->assertEquals(ApiClient::API_URL, $client->getApiUrl());
    }

    /**
     * @covers  \SMTP2GO\Service\Service
     * @covers \SMTP2GO\ApiClient
     * @return void
     */
    public function testSetRegionThrowsExceptionWhenInvalidRegionIsPassed()
    {
        $client = new ApiClient(SMTP2GO_API_KEY);
        $this->expectException(\InvalidArgumentException::class);
        $client->setApiRegion('nz');
    }
}