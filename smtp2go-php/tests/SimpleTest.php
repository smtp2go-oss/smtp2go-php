<?php

use PHPUnit\Framework\TestCase;

class SimpleTest extends TestCase
{
    /**
     * @covers SMTP2GO\Client
     *
     * @return void
     */
    public function testCanCreateApiClientInstance()
    {
        $client = new \SMTP2GO\ApiClient('API-ABC123');
        return $this->assertTrue(is_object($client));
    }

    /**
     * @covers \SMTP2GO\Service\Mail\Send
     *
     * @return void
     */
    public function testCanCreateSendServiceInstance()
    {
        $service = new \SMTP2GO\Service\Mail\Send(['fake@sender.test'],'fake@recipient.test', 'Test Subject', 'Test Message');
        
        return $this->assertTrue(is_object($service));
    }

    
}