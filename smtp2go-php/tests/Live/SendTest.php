<?php

use PHPUnit\Framework\TestCase;
use SMTP2GO\ApiClient;
use SMTP2GO\Service\Mail\Send as MailSend;

class SendTest extends TestCase
{
    /** Sends a real test email through SMTP2GO API
     *
     * @covers \SMTP2GO\ApiClient
     * @covers \SMTP2GO\Service\Mail\Send
     * @return void
     */
    public function testSendService()
    {
        $sendService = new MailSend([SMTP2GO_TEST_SENDER_EMAIL, SMTP2GO_TEST_SENDER_NAME], [[SMTP2GO_TEST_RECIPIENT_EMAIL, SMTP2GO_TEST_RECIPIENT_NAME]], SMTP2GO_TEST_SUBJECT, '<h1>Hello World</h1><p>If you are seeing this then it works!</p>');

        $sendService->addAddress('cc', 'kris.johansen@gmail.com');
        $sendService->addAddress('bcc', 'kris.r.johansen@icloud.com');

        $sendService->setAttachments(dirname(__FILE__, 2) . '/Attachments/cat.jpg');

        
        $apiClient = new ApiClient(SMTP2GO_API_KEY);

        $response = $apiClient->consume($sendService);

        $this->assertTrue($response);
    }
}
