<?php

use PHPUnit\Framework\TestCase;
use SMTP2GO\Service\Mail\Send;

/**
 * @covers \SMTP2GO\Service\Mail\Send
 */
class SendGettersTest extends TestCase
{

    private $sender;
    public function setUp(): void
    {
        $this->sender = new Send(['test@test.test'], ['recipient@test.test'], 'Testing!', 'Test Message');;
    }

    public function testGetAttachmentsReturnsArray()
    {
        $this->assertIsArray($this->sender->getAttachments());
    }

    public function testGetCustomHeadersReturnsArray()
    {
        $this->assertIsArray($this->sender->getCustomHeaders());
    }

    public function testGetInlinesReturnsArray()
    {
        $this->assertIsArray($this->sender->getInlines());
    }

    public function testGetRecipientsReturnsArray()
    {
        $this->assertIsArray($this->sender->getRecipients());
    }

    public function testGetCCReturnsArray()
    {
        $this->assertIsArray($this->sender->getCC());
    }  
    
    public function testGetBCCReturnsArray()
    {
        $this->assertIsArray($this->sender->getBCC());
    }

    public function testGetSubjectReturnsString()
    {
        $this->assertIsString($this->sender->getSubject());
    }    
}
