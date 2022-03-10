# SMTP2GO PHP API

This library provides a simple way to send email via the SMTP2GO API and also access other endpoints in the API in a standard way.

## Examples

### Sending an Email
```php
use SMTP2GO\ApiClient;
use SMTP2GO\Service\Mail\Send;
use SMTP2GO\Types\Mail\Address;
use SMTP2GO\Collections\Mail\AddressCollection;

$sendService = new MailSend(
    new Address('sender@email.test', 'Sender Name'),
    new AddressCollection([
        new Address('recipient@email.test', 'Recipient Name'),
        new Address('recipient2@email.test', 'Recipient Name 2'),
    ]),
    'Test Email',
    '<h1>Hello World</h1>'
);

$sendService->addAddress('cc', new Address('cc@email.test'));
$sendService->addAddress('bcc', new Address('bcc@email.test'));

$sendService->setAttachments(new AttachmentCollection([ new Attachment('/path/to/attachment'), new Attachment('/path/to/another_attachment')]);



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