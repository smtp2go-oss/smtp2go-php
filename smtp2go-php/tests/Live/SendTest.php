<?php

use SMTP2GO\Service\Mail\Send as MailSend;
use PHPUnit\Framework\TestCase;
use SMTP2GO\ApiClient;

class SendTest extends TestCase
{
    /** Actually perfom an actual send through the actual SMTP2GO API :)
     * 
     * @covers \SMTP2GO\ApiClient
     * @covers \SMTP2GO\Service\Mail\Send
     * @return void
     */
    public function testSendService()
    {
        $sendService = new MailSend([SMTP2GO_TEST_SENDER_EMAIL, SMTP2GO_TEST_SENDER_NAME], [[SMTP2GO_TEST_RECIPIENT_EMAIL, SMTP2GO_TEST_RECIPIENT_NAME]], SMTP2GO_TEST_SUBJECT, 'Hello World');

        $apiClient = new ApiClient(SMTP2GO_API_KEY);

        $response = $apiClient->consume($sendService);

        $this->assertTrue($response);
    }
}