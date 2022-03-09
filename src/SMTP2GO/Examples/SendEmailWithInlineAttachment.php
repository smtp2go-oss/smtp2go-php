<?php

use SMTP2GO\ApiClient;
use SMTP2GO\Service\Mail\Send;
use SMTP2GO\Types\Mail\Address;
use SMTP2GO\Types\Mail\InlineAttachment;
use SMTP2GO\Collections\Mail\AddressCollection;

return;

$basePath = dirname(__FILE__, 4);

require $basePath . '/vendor/autoload.php';

$message = <<<EOF

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
</head>
<body>
<h1>This should contain an image of a cat. It should appear as an inline attachment.</h1>
<img src="cid:a-cat" alt="a cat"/>
</body>
</html>
EOF;

$sender = new Address('kris@thefold.co.nz', 'A Sender');
$recipients = new AddressCollection([new Address('kris@2050.nz', 'Kris at 2050')]);

$sendService = new Send($sender, $recipients, 'A Subject', $message);

$inline = new InlineAttachment('a-cat', file_get_contents($basePath . '/tests/attachments/cat.jpg'), 'image/jpeg');

$sendService->addAttachment($inline);

$apiClient = new ApiClient('api-46A74340EFB311E98B49F23C91C88F4E');

$success = $apiClient->consume($sendService);

//$body = $sendService->buildRequestBody();

//echo '<pre style="border:1px solid #dbdbdb">' . print_r($body, 1) . '</pre>';

$responseBody = $apiClient->getResponseBody();

echo '<pre style="border:1px solid #dbdbdb">' . print_r($responseBody, 1) . '</pre>';
