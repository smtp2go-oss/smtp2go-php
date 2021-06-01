<?php

namespace SMTP2GO\Service\Mail;

use League\MimeTypeDetection\FinfoMimeTypeDetector;
use SMTP2GO\Service\Concerns\BuildsRequests;

/**
 * Constructs the payload for sending email through the SMTP2GO Api
 */
class Send implements BuildsRequests
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
    protected $recipients = array();

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
     * The email message
     *
     * @var string
     */
    protected $message;

    /**
     * The plain text part of a multipart email
     *
     * @var string
     */
    protected $alt_message;

    /**
     * Custom email headers
     *
     * @var string|array
     */
    protected $headers;

    /**
     * Attachments not added through the $attachments variable
     *
     * @var string|array
     */
    protected $attachments;

    /**
     * Inline attachments, only supported through this class
     *
     * @var string|array
     */
    protected $inlines;

    /**
     * The content type of the email, can be either text/plain or text/html
     *
     * @var string
     */
    protected $content_type = '';

    /**
     * endpoint to send to
     *
     * @var string
     */
    private const ENDPOINT = 'email/send';

    private const HTTP_METHOD = 'POST';

    /**
     * Create instance - arguments mirror those of the mail function
     *
     * @param mixed $recipients
     * @param mixed $subject
     * @param mixed $message
     * @param mixed $headers
     * @param mixed $attachments
     */
    public function __construct(array $sender, $recipients, $subject, $message)
    {
        $this->setSender(...$sender)->setRecipients($recipients)->setSubject($subject)->setMessage($message);
    }

    /**
     * Builds the JSON to send to the SMTP2GO API
     *
     * @return array
     */
    public function buildRequestPayload(): array
    {
        /** the body of the request which will be sent as json */
        $body = array();

        $body['to']  = $this->buildRecipients();
        $body['cc']  = $this->buildCC();
        $body['bcc'] = $this->buildBCC();

        $body['sender'] = $this->getSender();


        if ($this->content_type === 'multipart/alternative') {
            $body['html_body'] = $this->getMessage();
            $body['text_body'] = $this->getAltMessage();
        } elseif ($this->content_type === 'text/html') {
            $body['html_body'] = $this->getMessage();
        } else {
            $body['html_body'] = $this->getMessage();
            $body['text_body'] = $this->getMessage();
        }

        $body['subject']        = $this->getSubject();
        $body['attachments']    = $this->buildAttachments();
        $body['inlines']        = $this->buildInlines();

        return $body;
    }

    public function buildAttachments()
    {
        $detector = new FinfoMimeTypeDetector();

        $attachments = array();

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

        $inlines = array();

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
     * @since 1.0.1
     * @return array
     */

    public function buildCC()
    {
        $cc_recipients = array();
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
     * @since 1.0.1
     * @return array
     */
    public function buildBCC()
    {
        $bcc_recipients = array();
        foreach ((array) $this->bcc as $bcc_recipient) {
            if (!empty($bcc_recipient)) {
                $bcc_recipients[] = $this->rfc822($bcc_recipient);
            }
        }

        return $bcc_recipients;
    }

    private function rfc822($email)
    {
        //if its just a plain old email wrap it up
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '<' . $email . '>';
        }
        return $email;
    }

    /**
     * create an array of recipients to send to the api
     * @todo check how these are formatted and parse appropriately
     * @return void
     */
    public function buildRecipients()
    {
        $recipients = array();

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
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set the email message
     *
     * @param  string  $message  The email message
     *
     * @return  self
     */
    public function setMessage(string $message)
    {
        $this->message = $message;

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
     * Set the email recipients
     *
     * @param  string|array  $recipients  the email recipients
     *
     * @return  self
     */
    public function setRecipients($recipients)
    {
        if (!empty($recipients)) {
            if (is_string($recipients)) {
                $this->recipients = array($recipients);
            } else {
                $this->recipients = $recipients;
            }
        }

        return $this;
    }

    public function addRecipient($email, $name = '')
    {
        if (!empty($name)) {
            $email              = str_replace(['<', '>'], '', $email);
            $this->recipients[] = "$name <$email>";
        } else {
            $this->recipients[] = "$email";
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
    public function setBcc($bcc)
    {
        $this->bcc = $bcc;

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
     * Get the email content type
     */
    public function getContentType()
    {
        return $this->content_type;
    }

    /**
     * Set the email content type
     * @param string
     * @return  self
     */
    public function setContentType($content_type)
    {
        $content_type = trim(strtolower($content_type));

        if (in_array($content_type, array('text/plain', 'text/html', 'multipart/alternative'))) {
            $this->content_type = $content_type;
        }

        return $this;
    }

    /**
     * Get the plain text part of a multipart email
     *
     * @return  string
     */
    public function getAltMessage()
    {
        return $this->alt_message;
    }

    /**
     * Set the plain text part of a multipart email
     *
     * @param  string  $alt_message  The plain text part of a multipart email
     *
     * @return  self
     */
    public function setAltMessage(string $alt_message)
    {
        $this->alt_message = $alt_message;

        return $this;
    }




}
