# SMTP2GO PHP API

This library provides a simple way to send email via the SMTP2GO API and also access other endpoints in the API in a standard way.

## Examples

### Sending an Email
```php
use SMTP2GO\ApiClient;
use SMTP2GO\Service\Mail\Send as MailSend;

$sendService = new MailSend(
    ['sender@email.test', 'Sender Name'],
    [
        ['recipient@email.test', 'Recipient Name'],
        ['recipient2@email.test', 'Recipient Name 2'],
    ],
    'Test Email',
    '<h1>Hello World</h1>'
);

$sendService->addAddress('cc', 'cc@email.test');
$sendService->addAddress('bcc', 'bcc@email.test');

$sendService->setAttachments(['/path/to/attachment', '/path/to/another_attachment']);
$sendService->setInlines(['/path/to/inline_attachment', '/path/to/another_inline_attachment']);

$sendService->setCustomHeaders(['CUSTOM_HEADER_NAME' => 'CUSTOM_HEADER_VALUE']);

$apiClient = new ApiClient('YOURAPIKEY');

$success = $apiClient->consume($sendService);

$responseBody = $apiClient->getResponseBody();

```

### Consuming an endpoint in the API using the generic Service class
```php
$apiClient = new ApiClient('api-YOURAPIKEY');

$success = $client->consume((new Service('domain/verify', ['domain' => 'mydomain.tld'])));
```