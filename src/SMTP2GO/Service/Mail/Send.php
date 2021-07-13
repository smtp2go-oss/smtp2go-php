<?php

namespace SMTP2GO\Service\Mail;

use InvalidArgumentException;

use SMTP2GO\Mime\Detector;
use SMTP2GO\Contracts\BuildsRequest;

/**
 * Constructs the payload for sending email through the SMTP2GO Api
 */
class Send implements BuildsRequest
{
    /**
     * Custom headers
     *
     * @var array
     */
    protected $custom_headers = [];

    /**
     * Sender RFC-822 formatted email "John Smith <john@example.com>"
     *
     * @var string
     */
    protected $sender;

    /**
     * the email recipients
     *
     * @var array
     */
    protected $to = [];

    /**
     * The CC'd recipients
     *
     * @var array
     */
    protected $cc = [];

    /**
     * The BCC'd recipients
     *
     * @var array
     */
    protected $bcc = [];

    /**
     * The email subject
     *
     * @var string
     */
    protected $subject;

    /**
     * The html email message
     *
     * @var string
     */
    protected $html_body;

    /**
     * The plain text part of a multipart email
     *
     * @var string
     */
    protected $text_body;

    /**
     * Custom email headers
     *
     * @var array
     */
    protected $headers;

    /**
     * Attachments
     *
     * @var array
     */
    protected $attachments;

    /**
     * Inline attachments
     *
     * @var array
     */
    protected $inlines;

    /**
     * endpoint to send to
     *
     * @var string
     */
    private const ENDPOINT = 'email/send';

    /**
     * The HTTP Method to use
     */
    private const HTTP_METHOD = 'POST';

    /**
     * ```$sendService = new Send(['example@email.com','John Doe'], [['email1@example.test','Jane Doe']], 'My Subject', 'My Message');```
     *
     * @param array $sender may contain 1 or 2 values with email address and name
     * @param array $recipients the array should contain multiple arrays with 1 or 2 values with email address and optional name
     * @param string $subject the email subject line
     * @param string $message the body of the email either HTML or Plain Text
     *
     */
    public function __construct(array $sender, array $recipients, string $subject, string $message)
    {
        $this->setSender(...$sender)
            ->setRecipients($recipients)
            ->setSubject($subject)
            ->setBody($message);
    }

    /**
     * Builds the JSON to send to the SMTP2GO API
     *
     * @return array
     */
    public function buildRequestBody(): array
    {
        /** the body of the request which will be sent as json */
        $body = [];

        $body['to']  = $this->to;
        $body['cc']  = $this->cc;
        $body['bcc'] = $this->bcc;

        $body['sender'] = $this->getSender();

        $body['html_body'] = $this->getHtmlBody();
        $body['text_body'] = $this->getTextBody();

        $body['custom_headers'] = $this->buildCustomHeaders();

        $body['subject']     = $this->getSubject();
        $body['attachments'] = $this->buildAttachments();
        $body['inlines']     = $this->buildInlines();

        return array_filter($body);
    }

    public function buildCustomHeaders()
    {
        $raw_custom_headers = $this->getCustomHeaders();

        $custom_headers = [];

        if (!empty($raw_custom_headers['header'])) {
            foreach ($raw_custom_headers['header'] as $index => $header) {
                if (!empty($header) && !empty($raw_custom_headers['value'][$index])) {
                    $custom_headers[] = array(
                        'header' => $header,
                        'value'  => $raw_custom_headers['value'][$index],
                    );
                }
            }
        }

        return $custom_headers;
    }

    public function buildAttachments()
    {
        $detector = new Detector();

        $attachments = [];

        foreach ((array) $this->attachments as $path) {
            $file_contents = file_get_contents($path);
            $attachments[] = array(
                'filename' => basename($path),
                'fileblob' => base64_encode($file_contents),
                'mimetype' => $detector->detectMimeType($path),
            );
        }

        return $attachments;
    }

    public function buildInlines()
    {
        $detector = new Detector();

        $inlines = [];

        foreach ((array) $this->inlines as $path) {
            $file_contents = file_get_contents($path);

            $inlines[] = array(
                'filename' => basename($path),
                'fileblob' => base64_encode(file_get_contents($path)),
                'mimetype' => $detector->detectMimeType($path),
            );
        }
        return $inlines;
    }

    /**
     * Get endpoint to send to
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        return Send::ENDPOINT;
    }

    /**
     * Get endpoint to send to
     *
     * @return string
     */
    public function getMethod(): string
    {
        return Send::HTTP_METHOD;
    }

    /**
     * Set custom headers - expected format is the unserialized array
     * from the stored smtp2go_custom_headers option
     *
     * @param  array  $custom_headers  Custom headers
     * @return Send
     */
    public function setCustomHeaders($custom_headers): Send
    {
        if (is_array($custom_headers)) {
            $this->custom_headers = $custom_headers;
        }

        return $this;
    }

    /**
     * Get sender
     *
     * @return  string
     */
    public function getSender(): string
    {
        return $this->sender;
    }

    /**
     * Set sender as RFC-822 formatted email "John Smith <john@example.com>"
     *
     * @param string $email
     * @param string $name
     *
     * @return Send
     */
    public function setSender($email, $name = ''): Send
    {
        if (!empty($name)) {
            $email        = str_replace(['<', '>'], '', $email);
            $this->sender = "\"$name\" <$email>";
        } else {
            $this->sender = "$email";
        }

        return $this;
    }

    /**
     * Get the email subject
     *
     * @return  string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Set the email subject
     *
     * @param  string  $subject  The email subject
     *
     * @return Send
     */
    public function setSubject(string $subject): Send
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the email message
     *
     * @return  string
     */
    public function getHtmlBody()
    {
        return $this->html_body;
    }

    /**
     * Set the email message
     *
     * @param  string  $message  The email message
     *
     * @return Send
     */
    public function setHtmlBody(string $htmlBody): Send
    {
        $this->html_body = $htmlBody;

        return $this;
    }

    /**
     * Get the email recipients
     *
     * @return  array
     */
    public function getRecipients()
    {
        return $this->to;
    }

    /**
     * Set the email recipients - this clears any previously added recipients
     *
     * @param  array  $recipients the array should contain multiple arrays with 1 or 2 values with email address and optional name
     *
     * @return  Send
     */
    public function setRecipients(array $recipients): Send
    {
        $this->to = [];

        $this->addAddresses('to', $recipients);

        return $this;
    }

    /**
     * add multiple addresses of a specified type
     *
     * @param string $addressType either 'to', 'cc', 'bcc'
     * @param array $addresses the array should contain multiple arrays with 1 or 2 values with email address and optional name
     * @return Send
     */
    public function addAddresses(string $addressType, array $addresses): Send
    {
        foreach ($addresses as $addressesItem) {
            if (is_iterable($addressesItem)) {
                $this->addAddress($addressType, ...$addressesItem);
            }
        }
        return $this;
    }

    /**
     * add a single addresses of a specified type
     *
     * @param string $addressType either 'to', 'cc', 'bcc'
     * @param string $email
     * @param string $name
     * @return Send
     */

    public function addAddress(string $addressType, $email, $name = ''): Send
    {
        if (!in_array($addressType, ['to', 'cc', 'bcc'])) {
            throw new InvalidArgumentException('$addressType must be one of either "to", "cc" or "bcc"');
        }
        if (!empty($name)) {
            $email                = str_replace(['<', '>'], '', $email);
            $this->$addressType[] = "$name <$email>";
        } else {
            $this->$addressType[] = "$email";
        }
        return $this;
    }

    /**
     * Get the BCC'd recipients
     *
     * @return  string|array
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * Set the BCC'd recipients. This clears any previously added BCC addresses
     *
     * @param  array  $bcc  The BCC'd recipients may contain multiple arrays with 1 or 2 values with email address and optional name
     *
     * @return  self
     */
    public function setBcc(array $bcc)
    {
        $this->bcc = [];
        $this->addAddresses('bcc', $bcc);

        return $this;
    }

    /**
     * Get the CC'd recipients. This clears any previously added CC addresses
     *
     * @return  string|array
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * Set the CC'd recipients. This clears any previously added CC addresses.
     *
     * @param  array  $cc  The CC'd recipients may contain multiple arrays with 1 or 2 values with email address and optional name
     *
     * @return  Send
     */
    public function setCc($cc): Send
    {
        $this->cc = [];

        $this->addAddresses('cc', $cc);

        return $this;
    }

    /**
     * Get attachments
     *
     * @return  string|array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set attachments as an array of filepaths e.g ```['/path/to/file1.txt','/path/to/file2.jpg']```
     *
     * @param  array  $attachments
     *
     * @return  Send
     */
    public function setAttachments($attachments): Send
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * Get inline attachments
     *
     * @return array
     */
    public function getInlines()
    {
        return $this->inlines;
    }

    /**
     * Set inline attachments as an array of filepaths e.g ```['/path/to/file1.txt','/path/to/file2.jpg']```
     *
     * @param  array  $inlines  Inline attachments
     *
     * @return  Send
     */
    public function setInlines($inlines): Send
    {
        $this->inlines = $inlines;

        return $this;
    }

    /**
     * Get the plain text part of a multipart email
     *
     * @return  string
     */
    public function getTextBody()
    {
        return $this->text_body;
    }

    /**
     * Set the plain text part of a multipart email
     *
     * @param  string  $text_body  The plain text part of a multipart email
     *
     * @return  Send
     */
    public function setTextBody(string $text_body): Send
    {
        $this->text_body = $text_body;

        return $this;
    }

    /**
     * Sets the appropriate body field based on content - this clears
     * any previously set body fields
     *
     * @param string $body
     * @return Send
     */
    public function setBody(string $body): Send
    {
        $this->html_body = '';
        $this->text_body = '';

        if (preg_match('/(\<(\/?[^\>]+)\>)/', $body)) {
            $this->html_body = $body;
        } else {
            $this->text_body = $body;
        }
        return $this;
    }

    /**
     * Get custom headers
     *
     * @return  array
     */
    public function getCustomHeaders(): array
    {
        return $this->custom_headers;
    }
}
