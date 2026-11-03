<?php

use PHPUnit\Framework\TestCase;
use SMTP2GO\Types\Mail\CustomHeader;

/**
 * @covers \SMTP2GO\Types\Mail\CustomHeader
 */
class CustomHeaderTest extends TestCase
{
    public function testControlCharactersAreStrippedFromValue()
    {
        $header = new CustomHeader('Reply-To', "reply-to@example.test\r\nBcc: attacker@evil.test");

        $this->assertEquals('reply-to@example.testBcc: attacker@evil.test', $header->getValue());
    }

    public function testValueIsTrimmed()
    {
        $header = new CustomHeader('X-Mailer', "  MyApp  ");

        $this->assertEquals('MyApp', $header->getValue());
    }

    public function testValueIsSanitizedBySetter()
    {
        $header = new CustomHeader('X-Mailer', 'MyApp');
        $header->setValue("\tMyApp\n");

        $this->assertEquals('MyApp', $header->getValue());
    }

    public function testValidHeaderNameIsAccepted()
    {
        $header = new CustomHeader('X-Test-Header', 'Testing');
        $this->assertEquals('X-Test-Header', $header->getHeader());
    }
}
