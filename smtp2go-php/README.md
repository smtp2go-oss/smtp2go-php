# SMTP2GO PHP API

This library provides a simple way to send email via the SMTP2GO Api and also access other endpoints in the API in a standard way.

## Examples

### Sending an Email
```php

use SMTP2GO\ApiClient;
use SMTP2GO\Service\Mail\Send as MailSend;

$sendService = new MailSend(
    ['sender@email.test', 'Sender Name'],
    [
        ['recipient@email.test','Recipient Name'],
        ['recipient2@email.test','Recipient Name 2']
    ],
    'Test Email',
    '<h1>Hello World</h1>'
);

$sendService->addAddress('cc', 'cc@email.test');
$sendService->addAddress('bcc', 'bcc@email.test');

$sendService->setAttachments(['/path/to/attachment']);

$apiClient = new ApiClient('api-YOURAPIKEY');

$success = $apiClient->consume($sendService);

$responseBody = $apiClient->getResponseBody();
```

### Consuming an endpoint in the API
```php

 $success = $client->consume(
    (new Service('domain/verify', ['domain' => $this_host]))
);
```