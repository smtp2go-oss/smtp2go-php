<?php

use PHPUnit\Framework\TestCase;
use SMTP2GO\Types\Mail\Address;

class AddressTest extends TestCase

{
    public function testControlCharactersAreStrippedFromAddress()
    {
        $address = new Address("test@mail.local\r", "Bob,\" CEO\r\n");
        $addressString = $address->toString();
        $this->assertEquals('"Bob,\" CEO" <test@mail.local>', $addressString);
    }
}
