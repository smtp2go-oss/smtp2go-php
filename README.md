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

$sendService->setAttachments(new AttachmentCollection([ new Attachment('/path/to/attachment'), new Attachment('/path/to/another_attachment')]);

$inline = new InlineAttachment('a-cat-picture', file_get_contents('attachments/cat.jpg'), 'image/jpeg');

$sendService->addAttachment($inline);

$sendService->addCustomHeader(new CustomHeader('Reply-To', 'replyto@email.test'));

$apiClient = new ApiClient('api-YOURAPIKEY');

$success = $apiClient->consume($sendService);

$responseBody = $apiClient->getResponseBody();

```

### Consuming an endpoint in the API using the generic Service class
```php
$apiClient = new ApiClient('api-YOURAPIKEY');

$success = $client->consume((new Service('domain/verify', ['domain' => 'mydomain.tld'])));
```

## License

The package is available as open source under the terms of the [MIT License](http://opensource.org/licenses/MIT).