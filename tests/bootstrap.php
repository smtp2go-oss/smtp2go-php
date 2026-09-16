<?php

/**
 * Bootstrap for both PHPUnit and PHPStan.
 *
 * The test constants used to live in the <php><const> block of phpunit.xml,
 * but PHPStan does not read that file and reported every use as an unknown
 * constant. Defining them here instead keeps a single source of truth: PHPUnit
 * loads this via its `bootstrap` attribute, PHPStan via `bootstrapFiles`.
 */

require_once __DIR__ . '/../vendor/autoload.php';

defined('SMTP2GO_API_KEY') || define('SMTP2GO_API_KEY', 'api-123456789ABCDE');
defined('SMTP2GO_TEST_RECIPIENT') || define('SMTP2GO_TEST_RECIPIENT', 'Test Recipient <recipient@local>');
defined('SMTP2GO_TEST_RECIPIENT_NAME') || define('SMTP2GO_TEST_RECIPIENT_NAME', 'Test Recipient');
defined('SMTP2GO_TEST_RECIPIENT_EMAIL') || define('SMTP2GO_TEST_RECIPIENT_EMAIL', 'recipient@local');
defined('SMTP2GO_TEST_SENDER') || define('SMTP2GO_TEST_SENDER', 'Automated Test Sender <sender@local>');
defined('SMTP2GO_TEST_SENDER_NAME') || define('SMTP2GO_TEST_SENDER_NAME', 'Automated Test Sender');
defined('SMTP2GO_TEST_SENDER_EMAIL') || define('SMTP2GO_TEST_SENDER_EMAIL', 'sender@local');
defined('SMTP2GO_TEST_SUBJECT') || define('SMTP2GO_TEST_SUBJECT', 'PHPUnit Test Email');
