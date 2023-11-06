# SMTP2GO PHP API

This library provides a simple way to send email via the SMTP2GO API and also access other endpoints in the API in a standard way.

## Installation

```composer require smtp2go-oss/smtp2go-php```
## Examples

### Sending an Email
```php
use SMTP2GO\ApiClient;
use SMTP2GO\Service\Mail\Send as MailSend;
use SMTP2GO\Types\Mail\Address;
use SMTP2GO\Collections\Mail\AddressCollection;
use SMTP2GO\Collections\Mail\AttachmentCollection;
use SMTP2GO\Types\Mail\Attachment;
use SMTP2GO\Types\Mail\InlineAttachment;
use SMTP2GO\Types\Mail\CustomHeader;

$message = <<<EOF

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
<h1>This should contain an image of a cat. It should appear as an inline attachment.</h1>
<img src="cid:a-cat-picture" alt="a cat"/>
</body>
</html>
EOF;

$sendService = new MailSend(
    new Address('sender@email.test', 'Sender Name'),
    new AddressCollection([
        new Address('recipient@email.test', 'Recipient Name'),
        new Address('recipient2@email.test', 'Recipient Name 2'),
    ]),
    'Test Email',
   $message,
);

$sendService->addAddress('cc', new Address('cc@email.test'));
$sendService->addAddress('bcc', new Address('bcc@email.test'));

$sendService->setAttachments(new AttachmentCollection([ new Attachment('/path/to/attachment'), new Attachment('/path/to/another_attachment')]));

$inline = new InlineAttachment('a-cat-picture', file_get_contents('attachments/cat.jpg'), 'image/jpeg');

$sendService->addAttachment($inline);

$sendService->addCustomHeader(new CustomHeader('Reply-To', 'replyto@email.test'));

$apiClient = new ApiClient('api-YOURAPIKEY');

#set a custom region
$apiClient->setApiRegion('us');

#set the client to retry using a different server ip if possible
$apiClient->setMaxSendAttempts(5);

#set the number of seconds to increase the request timeout with each attempt
$apiClient->setTimeoutIncrement(5);

$success = $apiClient->consume($sendService);

$responseBody = $apiClient->getResponseBody();

```

### Sending email using a template
https://app-us.smtp2go.com/settings/templates/

This example is for the example template "User Welcome"

```php
use SMTP2GO\ApiClient;
use SMTP2GO\Types\Mail\Address;
use SMTP2GO\Service\Mail\Send as MailSend;
use SMTP2GO\Collections\Mail\AddressCollection;

$client = new ApiClient('api-46A74340EFB311E98B49F23C91C88F4E');
$sendService = new MailSend(
    new Address('sender@site.test', 'Sender Name'),
    new AddressCollection([
        new Address('recipient@example.test', 'Bob Recipient'),
    ]),
    '', //subject is empty as this is defined in the template
    '', //body is empty as this is generated from the template
);
$sendService->setTemplateId(6040276);
$sendService->setTemplateData([
    "username" => "Steve",
    "product_name" => "Widgets",
    "action_url" => "https://website.localhost",
    "login_url" => "https://website.localhost/login",
    "guide_url" => "https://website.localhost/guide",
    "support_email" => "support@website.localhost",
    "sender_name" => "Bob Widgets"
]);

$res = $client->consume($sendService);
```
### Consuming an endpoint in the API using the generic Service class
```php
$apiClient = new ApiClient('api-YOURAPIKEY');

$success = $client->consume((new Service('domain/verify', ['domain' => 'mydomain.tld'])));
```

## License

The package is available as open source under the terms of the [MIT License](http://opensource.org/licenses/MIT).
