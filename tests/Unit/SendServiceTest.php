<?php

use PHPUnit\Framework\TestCase;
use SMTP2GO\Service\Mail\Send;

class SendServiceTest extends TestCase
{
    private function createTestInstance()
    {
        $sendService = new Send(
            [SMTP2GO_TEST_SENDER_EMAIL, SMTP2GO_TEST_SENDER_NAME],
            [
                ['email1@example.test', 'Jane Doe'],
                ['email2@example.test', 'Mary Sue'],
            ],
            SMTP2GO_TEST_SUBJECT,
            'Test Message'
        );
        $raw_headers = unserialize('a:2:{s:6:"header";a:1:{i:0;s:13:"X-Test-Header";}s:5:"value";a:1:{i:0;s:7:"Testing";}}');

        $sendService->setCustomHeaders($raw_headers);

        return $sendService;
    }
    /**
     * @covers \SMTP2GO\Service\Mail\Send
     *
     * @return void
     */
    public function testMultipleRecipientsCanBeAdded()
    {
        $sendService = $this->createTestInstance();
        $sendService->addAddress('to', 'test@test.test');

        return $this->assertCount(3, $sendService->getRecipients());
    }
    /**
     * @covers \SMTP2GO\Service\Mail\Send
     *
     * @return void
     */
    public function testSubjectIsSetByConstructor()
    {
        $sendService = $this->createTestInstance();

        $this->assertEquals($sendService->getSubject(), SMTP2GO_TEST_SUBJECT);
    }
    /**
     * @covers \SMTP2GO\Service\Mail\Send
     *
     * @return void
     */
    public function testSenderIsSetByConstructor()
    {
        $sendService = $this->createTestInstance();

        $this->assertEquals($sendService->getSender(), '"' . SMTP2GO_TEST_SENDER_NAME . '" <' . SMTP2GO_TEST_SENDER_EMAIL . '>');
    }
    /**
     * @covers \SMTP2GO\Service\Mail\Send
     *
     * @return void
     */
    public function testMessageBodyIsSet()
    {
        $test_string = '<h1>Hello World</h1>';
        $sendService = $this->createTestInstance();
        $sendService->setHtmlBody($test_string);
        $this->assertEquals($sendService->getHtmlBody(), $test_string);
    }

    /**
     * Tests custom headers are built into the correct format for the api
     * @covers \SMTP2GO\Service\Mail\Send
     * @return void
     */
    public function testBuildCustomHeaders()
    {
        $sendService = $this->createTestInstance();

        $formatted_headers = $sendService->buildCustomHeaders();

        $this->assertArrayHasKey('header', $formatted_headers[0]);
        $this->assertArrayHasKey('value', $formatted_headers[0]);
    }

    /**
     *
     * @covers \SMTP2GO\Service\Mail\Send
     * @return void
     */
    public function testbuildRequestBodyWithHTMLMessage()
    {
        $expected_json_body_string = '{"to":["' . SMTP2GO_TEST_RECIPIENT_NAME . ' <' . SMTP2GO_TEST_RECIPIENT_EMAIL . '>"],"sender":"\"' . SMTP2GO_TEST_SENDER_NAME . '\" <' . SMTP2GO_TEST_SENDER_EMAIL . '>","html_body":"<html><body><h1>Heading<\/h1><div>This is the message<\/div><\/body><\/html>","custom_headers":[{"header":"X-Test-Header","value":"Testing"}],"subject":"' . SMTP2GO_TEST_SUBJECT . '"}';
        $sendService               = $this->createTestInstance();

        $sendService->setSubject(SMTP2GO_TEST_SUBJECT);
        $sendService->setBody('<html><body><h1>Heading</h1><div>This is the message</div></body></html>');
        $sendService->setRecipients([[SMTP2GO_TEST_RECIPIENT_EMAIL, SMTP2GO_TEST_RECIPIENT_NAME]]);
        $request_data = $sendService->buildRequestBody();

        $this->assertJsonStringEqualsJsonString($expected_json_body_string, json_encode(array_filter($request_data)));
    }

    /**
     *
     * @covers \SMTP2GO\Service\Mail\Send
     * @return void
     */
    public function testbuildRequestBodyWithPlainTextMessage()
    {
        $expected_json_body_string = '{"to":["' . SMTP2GO_TEST_RECIPIENT_NAME . ' <' . SMTP2GO_TEST_RECIPIENT_EMAIL . '>"],"sender":"\"' . SMTP2GO_TEST_SENDER_NAME . '\" <' . SMTP2GO_TEST_SENDER_EMAIL . '>","text_body":"A Plain Message","custom_headers":[{"header":"X-Test-Header","value":"Testing"}],"subject":"' . SMTP2GO_TEST_SUBJECT . '"}';
        $sendService               = $this->createTestInstance();

        $sendService->setSubject(SMTP2GO_TEST_SUBJECT);
        $sendService->setBody('A Plain Message');
        $sendService->setRecipients([[SMTP2GO_TEST_RECIPIENT_EMAIL, SMTP2GO_TEST_RECIPIENT_NAME]]);
        $request_data = $sendService->buildRequestBody();

        $this->assertJsonStringEqualsJsonString($expected_json_body_string, json_encode(array_filter($request_data)));
    }

    /**
     *
     * @covers \SMTP2GO\Service\Mail\Send
     * @covers \SMTP2GO\Mime\Detector

     * @return void
     */

    public function testAddAttachment()
    {
        $sendService = $this->createTestInstance();

        $sendService->setAttachments(dirname(__FILE__, 2) . '/Attachments/cat.jpg');

        $request_data = $sendService->buildRequestBody();

        $this->assertArrayHasKey('attachments', $request_data);

        $this->assertEquals('image/jpeg', $request_data['attachments'][0]['mimetype']);
    }

    /**
     *
     * @covers \SMTP2GO\Service\Mail\Send
     * @covers \SMTP2GO\Mime\Detector
     * 
     * @return void
     */
    public function testAddInline()
    {
        $sendService = $this->createTestInstance();

        $sendService->setInlines(dirname(__FILE__, 2) . '/Attachments/cat.jpg');

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
        $this->expectException(InvalidArgumentException::class);
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
        $sendService->setSender(SMTP2GO_TEST_SENDER_EMAIL);
        $this->assertTrue($sendService->getSender() == SMTP2GO_TEST_SENDER_EMAIL);
    }
}
