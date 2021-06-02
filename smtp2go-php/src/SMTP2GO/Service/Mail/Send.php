<?php

namespace SMTP2GO\Service\Mail;

use League\MimeTypeDetection\FinfoMimeTypeDetector;
use SMTP2GO\Service\Concerns\BuildsRequest;

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
    protected $custom_headers;

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
    protected $recipients = [];

    /**
     * The CC'd recipients
     *
     * @var string|array
     */
    protected $cc;

    /**
     * The BCC'd recipients
     *
     * @var string|array
     */
    protected $bcc;

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
     * @var string|array
     */
    protected $headers;

    /**
     * Attachments
     *
     * @var string|array
     */
    protected $attachments;

    /**
     * Inline attachments
     *
     * @var string|array
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
     * @param array $recipients may contain multiple arrays with 1 or 2 values with email address and optional name
     * @param string $subject the email subject line
     * @param string $message the body of the email either HTML or Plain Text
     *
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

        $body['to']  = $this->buildRecipients();
        $body['cc']  = $this->buildCC();
        $body['bcc'] = $this->buildBCC();

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
        $detector = new FinfoMimeTypeDetector();

        $attachments = [];

        foreach ((array) $this->attachments as $path) {
            $file_contents = file_get_contents($path);
            $attachments[] = array(
                'filename' => basename($path),
                'fileblob' => base64_encode($file_contents),
                'mimetype' => $detector->detectMimeType($path, $file_contents),
            );
        }

        return $attachments;
    }

    public function buildInlines()
    {
        $detector = new FinfoMimeTypeDetector();

        $inlines = [];

        foreach ((array) $this->inlines as $path) {
            $file_contents = file_get_contents($path);

            $inlines[] = array(
                'filename' => basename($path),
                'fileblob' => base64_encode(file_get_contents($path)),
                'mimetype' => $detector->detectMimeType($path, $file_contents),
            );
        }
        return $inlines;
    }

    /**
     * Build an array of bcc recipients by combining ones natively set
     * or passed through the $headers constructor variable
     *
     * @since 1.0.0
     * @return array
     */

    public function buildCC()
    {
        $cc_recipients = [];
        foreach ((array) $this->cc as $cc_recipient) {
            if (!empty($cc_recipient)) {
                $cc_recipients[] = $this->rfc822($cc_recipient);
            }
        }

        return $cc_recipients;
    }

    /**
     * Build an array of bcc recipients by combining ones natively set
     * or passed through the $headers constructor variable
     *
     * @since 1.0.0
     * @return array
     */
    public function buildBCC()
    {
        $bcc_recipients = [];
        foreach ((array) $this->bcc as $bcc_recipient) {
            if (!empty($bcc_recipient)) {
                $bcc_recipients[] = $this->rfc822($bcc_recipient);
            }
        }

        return $bcc_recipients;
    }

    /**
     * Wrap plain emails in the rfc822 format
     *
     * @param string $email
     * @return string
     */
    private function rfc822(string $email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '<' . $email . '>';
        }
        return $email;
    }

    /**
     * create an array of recipients to send to the api
     *
     * @return array
     */
    public function buildRecipients(): array
    {
        $recipients = [];

        if (!is_array($this->recipients)) {
            $recipients[] = $this->rfc822($this->recipients);
        } else {
            foreach ($this->recipients as $recipient_item) {
                if (!empty($recipient_item)) {
                    $recipients[] = $this->rfc822($recipient_item);
                }
            }
        }
        return $recipients;
    }

    /**
     * Get endpoint to send to
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        return static::ENDPOINT;
    }

    /**
     * Get endpoint to send to
     *
     * @return string
     */
    public function getMethod(): string
    {
        return static::HTTP_METHOD;
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
     * @return static
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
     * @return static
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
        return $this->recipients;
    }

    /**
     * Set the email recipients - this clears any previously added recipients
     *
     * @param  array  $recipients the email recipients
     *
     * @return  Send
     */
    public function setRecipients(array $recipients): Send
    {
        $this->recipients = [];

        $this->addAddresses('recipients', $recipients);

        return $this;
    }

    private function addAddresses(string $addressType, array $addresses)
    {
        foreach ($addresses as $addressesItem) {
            if (is_iterable($addressesItem)) {
                $this->addAddress($addressType, ...$addressesItem);
            }
        }
    }

    private function addAddress(string $addressType, $email, $name = '')
    {
        if (!empty($name)) {
            $email                = str_replace(['<', '>'], '', $email);
            $this->$addressType[] = "$name <$email>";
        } else {
            $this->$addressType[] = "$email";
        }
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
     * Set the BCC'd recipients
     *
     * @param  string|array  $bcc  The BCC'd recipients
     *
     * @return  self
     */
    public function setBcc(array $bcc)
    {
        $this->bcc = [];

        return $this;
    }

    /**
     * Get the CC'd recipients
     *
     * @return  string|array
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * Set the CC'd recipients
     *
     * @param  string|array  $cc  The CC'd recipients
     *
     * @return  self
     */
    public function setCc($cc)
    {
        $this->cc = $cc;

        return $this;
    }

    /**
     * Get attachments not added through the $attachments variable
     *
     * @return  string|array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set attachments not added through the $attachments variable
     *
     * @param  string|array  $attachments Attachments not added through the $attachments variable
     *
     * @return  self
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * Get inline attachments
     *
     * @return  string|array
     */
    public function getInlines()
    {
        return $this->inlines;
    }

    /**
     * Set inline attachments, only supported through this class
     *
     * @param  string|array  $inlines  Inline attachments
     *
     * @return  self
     */
    public function setInlines($inlines)
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
     * @return  self
     */
    public function setTextBody(string $text_body)
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
    public function getCustomHeaders()
    {
        return $this->custom_headers;
    }
}
