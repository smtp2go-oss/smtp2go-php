# SMTP2GO PHP API

This library provides a simple way to send email via the SMTP2GO API and also access other endpoints in the API in a standard way.

## Requirements

- PHP >= 7.4
- [Guzzle](https://github.com/guzzle/guzzle) ^7.0 (installed automatically via Composer)

## Installation

```
composer require smtp2go-oss/smtp2go-php
```

You can obtain an API key from [app.smtp2go.com/settings/apikeys](https://app-us.smtp2go.com/sending/apikeys/).

## Examples

### Sending an Email
```php
use SMTP2GO\ApiClient;
use SMTP2GO\Service\Mail\Send as MailSend;
use SMTP2GO\Types\Mail\Address;
use SMTP2GO\Collections\Mail\AddressCollection;
use SMTP2GO\Collections\Mail\AttachmentCollection;
use SMTP2GO\Types\Mail\FileAttachment;
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

$sendService->setAttachments(new AttachmentCollection([ new FileAttachment('attachment-data','file1.txt'), new FileAttachment('another-attachment-data','file2.txt')]));

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

### Handling the Response

`consume()` returns `true` if the API responded with HTTP 200, `false` otherwise. Use the response methods to inspect the result or diagnose failures:

```php
$success = $apiClient->consume($sendService);

if ($success) {
    $body = $apiClient->getResponseBody(); // stdClass
    echo $body->data->succeeded; // number of messages queued
} else {
    $body = $apiClient->getResponseBody();
    echo $body->data->error;      // human-readable error message
    echo $body->data->error_code; // machine-readable error code

    // If an exception was thrown (e.g. connection failure):
    echo $apiClient->getLastRequest();          // the outgoing request as a string
    echo $apiClient->getLastResponseStatusCode(); // HTTP status code, or null
}
```

To get the raw response headers:

```php
$headers = $apiClient->getResponseHeaders(); // array
```

### Scheduling an Email

Pass a Unix timestamp up to 3 days in the future to `scheduleAt()`:

```php
$sendService->scheduleAt(strtotime('+2 hours'));
$apiClient->consume($sendService);
```

### Retry / Failover

When `setMaxSendAttempts()` is greater than 1, the client will automatically resolve alternative IP addresses for `api.smtp2go.com` and retry failed requests against them, increasing the timeout by `setTimeoutIncrement()` seconds on each attempt. Useful for high-reliability sending.

```php
$apiClient->setMaxSendAttempts(5);   // try up to 5 different IPs
$apiClient->setTimeoutIncrement(5);  // add 5 seconds to timeout per attempt
```

After a failed send you can inspect what happened:

```php
foreach ($apiClient->getFailedAttemptInfo() as $attempt) {
    echo $attempt['ip'];    // IP that was tried
    echo $attempt['error']; // exception message
}
echo $apiClient->getFailedAttempts(); // total number of failed attempts
```

### Attachments

**`FileAttachment`** — attach a file by passing its raw content and a filename. The MIME type is detected automatically from the filename extension:

```php
use SMTP2GO\Types\Mail\FileAttachment;
use SMTP2GO\Collections\Mail\AttachmentCollection;

$sendService->setAttachments(new AttachmentCollection([
    new FileAttachment(file_get_contents('/path/to/report.pdf'), 'report.pdf'),
]));
```

**`InlineAttachment`** — embed an image directly in the HTML body using a content ID:

```php
use SMTP2GO\Types\Mail\InlineAttachment;

// Reference in your HTML as: <img src="cid:my-image">
$inline = new InlineAttachment('my-image', file_get_contents('/path/to/image.jpg'), 'image/jpeg');
$sendService->addAttachment($inline);
```

### Custom Headers

```php
use SMTP2GO\Types\Mail\CustomHeader;

$sendService->addCustomHeader(new CustomHeader('Reply-To', 'replyto@email.test'));
$sendService->addCustomHeader(new CustomHeader('X-Mailer', 'MyApp'));
```

### Sending email using a template
https://app-us.smtp2go.com/settings/templates/

This example is for the example template "User Welcome"

```php
use SMTP2GO\ApiClient;
use SMTP2GO\Types\Mail\Address;
use SMTP2GO\Service\Mail\Send as MailSend;
use SMTP2GO\Collections\Mail\AddressCollection;

$client = new ApiClient('api-YOURAPIKEY');
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

### Consuming an endpoint in the API using the Service class
```php
$apiClient = new ApiClient('api-YOURAPIKEY');

$success = $client->consume((new Service('domain/verify', ['domain' => 'mydomain.tld'])));
```

## Testing

Copy `phpunit.xml` to `phpunit-dev.xml` and fill in your credentials in the `<php>` block, then run:

```bash
vendor/bin/phpunit -c phpunit-dev.xml
```

To generate an HTML coverage report (requires Xdebug or PCOV):

```bash
vendor/bin/phpunit -c phpunit-dev.xml --coverage-html /path/to/output
```

## License

The package is available as open source under the terms of the [MIT License](http://opensource.org/licenses/MIT).
