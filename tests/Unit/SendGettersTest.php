<?php

use SMTP2GO\Service\Mail\Send;
use PHPUnit\Framework\TestCase;
use SMTP2GO\Types\Mail\Address;
use SMTP2GO\Collections\Mail\AddressCollection;
use SMTP2GO\Collections\Mail\AttachmentCollection;
use SMTP2GO\Collections\Mail\CustomHeaderCollection;

/**
 * @covers \SMTP2GO\Service\Mail\Send
* @covers \SMTP2GO\Collections\Collection::current
* @covers \SMTP2GO\Collections\Collection::next
* @covers \SMTP2GO\Collections\Collection::rewind
* @covers \SMTP2GO\Collections\Collection::valid
* @covers \SMTP2GO\Collections\Mail\AddressCollection::__construct
* @covers \SMTP2GO\Collections\Mail\AddressCollection::add
* @covers \SMTP2GO\Collections\Mail\AttachmentCollection::__construct
* @covers \SMTP2GO\Collections\Mail\CustomHeaderCollection::__construct
* @covers \SMTP2GO\Types\Mail\Address::__construct
* @covers \SMTP2GO\Types\Mail\Address::getEmail
* @covers \SMTP2GO\Types\Mail\Address::getName
 */
class SendGettersTest extends TestCase
{

    private $sender;

    public function setUp(): void
    {
        $this->sender = new Send(new Address('sender@test.test'), new AddressCollection([new Address('recipient@test.test')]), 'Testing!', 'Test Message');
    }

    public function testGetAttachmentsReturnsArray()
    {
        $this->assertInstanceOf(AttachmentCollection::class, $this->sender->getAttachments());
    }

    public function testGetCustomHeadersReturnsArray()
    {
        $this->assertInstanceOf(CustomHeaderCollection::class, $this->sender->getCustomHeaders());
    }

    public function testGetInlinesReturnsArray()
    {
        $this->assertInstanceOf(AttachmentCollection::class, $this->sender->getInlines());
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
