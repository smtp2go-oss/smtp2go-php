<?php

use SMTP2GO\Service\Service;
use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    public function testSettingEndpointWithLeadingSlashStripsSlash()
    {
        $service = new Service('/email/send',[]);
        
        $this->assertEquals('email/send', $service->getEndpoint());
    }
}
