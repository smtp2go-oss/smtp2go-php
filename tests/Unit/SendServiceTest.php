<?php

use SMTP2GO\Service\Mail\Send;
use PHPUnit\Framework\TestCase;
use SMTP2GO\Types\Mail\Address;
use SMTP2GO\Types\Mail\Attachment;
use SMTP2GO\Types\Mail\InlineAttachment;
use SMTP2GO\Collections\Mail\AddressCollection;
use SMTP2GO\Collections\Mail\AttachmentCollection;
use SMTP2GO\Types\Mail\CustomHeader;


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
* @covers \SMTP2GO\Collections\Mail\CustomHeaderCollection
* @covers \SMTP2GO\Collections\Mail\AttachmentCollection
* @covers \SMTP2GO\Types\Mail\Attachment
* @covers \SMTP2GO\Types\Mail\CustomHeader
* @covers \SMTP2GO\Types\Mail\InlineAttachment
 */
class SendServiceTest extends TestCase
{
    private function createTestInstance()
    {
        $sendService = new Send(
            new Address(SMTP2GO_TEST_SENDER_EMAIL, SMTP2GO_TEST_SENDER_NAME),
            new AddressCollection([
                new Address('email1@example.test', 'Jane Doe'),
                new Address('email2@example.test', 'Mary Sue'),
            ]),
            SMTP2GO_TEST_SUBJECT,
            'Test Message'
        );

        $sendService->addCustomHeader(new CustomHeader('X-Test-Header', 'Testing'));
        $sendService->addCustomHeader(new CustomHeader('Reply-To', 'reply-to@example.test'));


        return $sendService;
    }

    public function testMultipleRecipientsCanBeAdded()
    {
        $sendService = $this->createTestInstance();
        $sendService->addAddress('to', new Address('test@test.test'));

        return $this->assertCount(3, $sendService->getRecipients());
    }

    public function testSubjectIsSetByConstructor()
    {
        $sendService = $this->createTestInstance();

        $this->assertEquals($sendService->getSubject(), SMTP2GO_TEST_SUBJECT);
    }

    public function testSenderIsSetByConstructor()
    {
        $sendService = $this->createTestInstance();

        $this->assertEquals($sendService->getSender(), '"' . SMTP2GO_TEST_SENDER_NAME . '" <' . SMTP2GO_TEST_SENDER_EMAIL . '>');
    }

    public function testMessageBodyIsSet()
    {
        $test_string = '<h1>Hello World</h1>';
        $sendService = $this->createTestInstance();
        $sendService->setHtmlBody($test_string);
        $this->assertEquals($sendService->getHtmlBody(), $test_string);
    }

    public function testBuildCustomHeaders()
    {
        $sendService = $this->createTestInstance();

        $formatted_headers = $sendService->buildCustomHeaders();

        $this->assertArrayHasKey('header', $formatted_headers[0]);
        $this->assertArrayHasKey('value', $formatted_headers[0]);
    }


    public function testbuildRequestBodyWithHTMLMessage()
    {
        $expected_json_body_string = '{"to":["' . SMTP2GO_TEST_RECIPIENT_NAME . ' <' . SMTP2GO_TEST_RECIPIENT_EMAIL . '>"],"sender":"\"' . SMTP2GO_TEST_SENDER_NAME . '\" <' . SMTP2GO_TEST_SENDER_EMAIL . '>","html_body":"<html><body><h1>Heading<\/h1><div>This is the message<\/div><\/body><\/html>","custom_headers":[{"header":"X-Test-Header","value":"Testing"},{"header":"Reply-To","value":"reply-to@example.test"}],"subject":"' . SMTP2GO_TEST_SUBJECT . '","version":1}';
        $sendService               = $this->createTestInstance();

        $sendService->setSubject(SMTP2GO_TEST_SUBJECT);
        $sendService->setBody('<html><body><h1>Heading</h1><div>This is the message</div></body></html>');
        $sendService->setRecipients(new AddressCollection([
            new Address(SMTP2GO_TEST_RECIPIENT_EMAIL, SMTP2GO_TEST_RECIPIENT_NAME)
        ]));
        $request_data = $sendService->buildRequestBody();

        $this->assertJsonStringEqualsJsonString($expected_json_body_string, json_encode(array_filter($request_data)));
    }

    public function testbuildRequestBodyWithPlainTextMessage()
    {
        $expected_json_body_string = '{"to":["' . SMTP2GO_TEST_RECIPIENT_NAME . ' <' . SMTP2GO_TEST_RECIPIENT_EMAIL . '>"],"sender":"\"' . SMTP2GO_TEST_SENDER_NAME . '\" <' . SMTP2GO_TEST_SENDER_EMAIL . '>","text_body":"A Plain Message","custom_headers":[{"header":"X-Test-Header","value":"Testing"},{"header":"Reply-To","value":"reply-to@example.test"}],"subject":"' . SMTP2GO_TEST_SUBJECT . '","version":1}';
        $sendService               = $this->createTestInstance();

        $sendService->setSubject(SMTP2GO_TEST_SUBJECT);
        $sendService->setBody('A Plain Message');
        $sendService->setRecipients(new AddressCollection([
            new Address(SMTP2GO_TEST_RECIPIENT_EMAIL, SMTP2GO_TEST_RECIPIENT_NAME)
        ]));
        $request_data = $sendService->buildRequestBody();

        $this->assertJsonStringEqualsJsonString($expected_json_body_string, json_encode(array_filter($request_data)));
    }

    /**
     *
     * @covers \SMTP2GO\Mime\Detector

     * @return void
     */

    public function testAddAttachment()
    {
        $sendService = $this->createTestInstance();

        $sendService->setAttachments(new AttachmentCollection([new Attachment(dirname(__FILE__, 2) . '/Attachments/cat.jpg')]));

        $request_data = $sendService->buildRequestBody();

        $this->assertArrayHasKey('attachments', $request_data);

        $this->assertEquals('image/jpeg', $request_data['attachments'][0]['mimetype']);
    }

    /**
     *
     * @covers \SMTP2GO\Mime\Detector
     * 
     * @return void
     */
    public function testAddInline()
    {
        $sendService = $this->createTestInstance();

        $sendService->setInlines(
            new AttachmentCollection([
                new InlineAttachment('cat', file_get_contents(dirname(__FILE__, 2) . '/Attachments/cat.jpg'), 'image/jpeg')
            ])
        );


        $request_data = $sendService->buildRequestBody();

        $this->assertArrayHasKey('inlines', $request_data);

        $this->assertEquals('image/jpeg', $request_data['inlines'][0]['mimetype']);
    }

    /**
     *
     * @covers \SMTP2GO\Service\Mail\Send
     * @return void
     */
    public function testInvalidAddressTypeArgument()
    {
        $sendService = $this->createTestInstance();
        $this->expectException(TypeError::class);
        $sendService->addAddress('invalid_arg', SMTP2GO_TEST_RECIPIENT_EMAIL, '');
    }
    /**
     *
     * @covers \SMTP2GO\Service\Mail\Send
     * @return void
     */
    public function testSetSenderWithoutName()
    {
        $sendService = $this->createTestInstance();
        $sendService->setSender(new Address(SMTP2GO_TEST_SENDER_EMAIL));
        $this->assertTrue($sendService->getSender() == SMTP2GO_TEST_SENDER_EMAIL);
    }
}
