<?php

namespace SMTP2GO\Service\Mail;


use SMTP2GO\Mime\Detector;

use InvalidArgumentException;
use SMTP2GO\Types\Mail\Address;
use SMTP2GO\Types\Mail\Attachment;
use SMTP2GO\Contracts\BuildsRequest;
use SMTP2GO\Collections\Mail\CustomHeaderCollection;
use SMTP2GO\Types\Mail\InlineAttachment;
use SMTP2GO\Collections\Mail\AddressCollection;
use SMTP2GO\Collections\Mail\AttachmentCollection;
use SMTP2GO\Types\Mail\CustomHeader;

/**
 * Constructs the payload for sending email through the SMTP2GO Api
 */
class Send implements BuildsRequest
{


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
     * The template id to use
     * @link https://app-us.smtp2go.com/settings/templates/
     * @var string|null
     */

    protected ?string $template_id = null;

    /**
     * The template data to use which is key value pairs of [placeholder => replacement]
     * @link https://app-us.smtp2go.com/settings/templates/
     * @var array
     */
    protected $template_data;

    /**
     * An optional set of custom headers to be applied to the email.
     *
     * @var CustomHeaderCollection
     */
    protected $custom_headers;

    /**
     * An array of attachment objects to be attached to the email.
     *
     * @var AttachmentCollection
     */
    protected $attachments;

    /**
     * An array of images to be inlined into the email.
     *
     * @var AttachmentCollection
     */
    protected $inlines;


    /**
     * @var int version
     * The version parameter specifies which version (structure) to use when generating the email
     * @see https://apidoc.smtp2go.com/documentation/#/POST%20/email/send
     * 
     */
    protected $version = 1;

    /**
     * @var int scheduleAt
     * A unix timestamp to schedule the email for sending in the future.
     * 
     */
    protected $scheduleAt = null;

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
     *
     * @param Address $sender may contain 1 or 2 values with email address and name
     * @param AddressCollection $recipients a collection of Address Objects
     * @param string $subject the email subject line
     * @param string $message the body of the email either HTML or Plain Text
     *
     */
    public function __construct(Address $sender, AddressCollection $recipients, string $subject, string $message)
    {
        $this->setSender($sender)
            ->setRecipients($recipients)
            ->setSubject($subject)
            ->setBody($message);

        $this->attachments = new AttachmentCollection;
        $this->inlines = new AttachmentCollection;
        $this->custom_headers = new CustomHeaderCollection;
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

        $body['template_id'] = $this->template_id ?? null;
        $body['template_data'] = $this->template_data ?? null;
        $body['version'] = $this->version;
        $body['schedule'] = $this->scheduleAt;


        return array_filter($body);
    }

    public function scheduleAt(int $timestamp)
    {
        if ($timestamp < time() || $timestamp > time() + (3 * 24 * 60 * 60)) {
            throw new \InvalidArgumentException('The timestamp must be a valid unix timestamp in the future, and no more than 3 days from now.');
        }
        $this->scheduleAt = $timestamp;
        return $this;
    }



    public function buildCustomHeaders()
    {
        $headers = [];
        foreach ($this->getCustomHeaders() as $customHeader) {
            /** @var \SMTP2GO\Types\Mail\CustomHeader $customHeader */
            $headers[] = ['header' => $customHeader->getheader(), 'value' => $customHeader->getvalue()];
        }
        return $headers;
    }

    /**
     * Build Attachment Structure
     *
     * @return array
     */
    public function buildAttachments(): array
    {
        if (empty($this->attachments)) {
            return [];
        }

        $attachments = [];

        foreach ($this->attachments as $attachment) {
            $attachments[] = $attachment->toArray();
        }

        return $attachments;
    }

    /**
     * Build inline attachment structure
     *
     * @return array
     */
    public function buildInlines(): array
    {
        if (empty($this->inlines)) {
            return [];
        }

        $inlines = [];

        foreach ($this->inlines as $inlineAttachment) {
            $inlines[] = $inlineAttachment->toArray();
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
     * Set custom headers - This clears any previously set headers
     *
     * @param  CustomHeaderCollection $headers
     * @return Send
     */
    public function setCustomHeaders(CustomHeaderCollection $headers): Send
    {

        $this->custom_headers = $headers;

        return $this;
    }

    /**
     * Add a custom header
     *
     * @param string $headerName
     * @param string $headerValue
     * @return void
     */
    public function addCustomHeader(CustomHeader $header): Send
    {
        $this->custom_headers->add($header);
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
     * @param Address $address
     *
     * @return Send
     */
    public function setSender(Address $address): Send
    {
        $name = $address->getName();
        $email = $address->getEmail();
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
    public function getHtmlBody(): string
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
    public function getRecipients(): array
    {
        return $this->to;
    }

    /**
     * Set the email recipients - this clears any previously added recipients
     *
     * @param  AddressCollection  $recipients the array should contain multiple arrays with 1 or 2 values with email address and optional name
     *
     * @return  Send
     */
    public function setRecipients(AddressCollection $recipients): Send
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
    public function addAddresses(string $addressType, AddressCollection $addresses): Send
    {
        foreach ($addresses as $addressesItem) {

            $this->addAddress($addressType, $addressesItem);
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

    public function addAddress(string $addressType, Address $address): Send
    {
        if (!in_array($addressType, ['to', 'cc', 'bcc'])) {
            throw new InvalidArgumentException('$addressType must be one of either "to", "cc" or "bcc"');
        }
        $name = $address->getName();
        $email = $address->getEmail();

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
     * @return  array
     */
    public function getBcc(): array
    {
        return $this->bcc;
    }

    /**
     * Set the BCC'd recipients. This clears any previously added BCC addresses
     *
     * @param  AddressCollection  $bcc  The BCC'd recipients may contain multiple arrays with 1 or 2 values with email address and optional name
     *
     * @return  Send
     */
    public function setBcc(AddressCollection $bcc): Send
    {
        $this->bcc = [];
        $this->addAddresses('bcc', $bcc);

        return $this;
    }

    /**
     * Get the CC'd recipients. This clears any previously added CC addresses
     *
     * @return  AddressCollection
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * Set the CC'd recipients. This clears any previously added CC addresses.
     *
     * @param  AddressCollection  $cc  The CC'd recipients may contain multiple arrays with 1 or 2 values with email address and optional name
     *
     * @return  Send
     */
    public function setCc(AddressCollection $cc): Send
    {
        $this->cc = [];

        $this->addAddresses('cc', $cc);

        return $this;
    }

    /**
     * Get attachments
     *
     * @return  AttachmentCollection
     */
    public function getAttachments(): AttachmentCollection
    {
        return $this->attachments;
    }

    public function addAttachment(Attachment $attachment): Send
    {
        if (is_a($attachment, InlineAttachment::class)) {
            $this->inlines[] = $attachment;
        } else {
            $this->attachments[] = $attachment;
        }
        return $this;
    }

    /**
     * Set attachments
     *
     * @param  AttachmentCollection  $attachments
     *
     * @return  Send
     */
    public function setAttachments(AttachmentCollection $attachments): Send
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * Get inline attachments
     *
     * @return AttachmentCollection
     */
    public function getInlines(): AttachmentCollection
    {
        return $this->inlines;
    }

    /**
     * Set inline attachments
     *
     * @param  AttachmentCollection  $inlines  Inline attachments
     *
     * @return  Send
     */
    public function setInlines(AttachmentCollection $inlines): Send
    {
        $this->inlines = $inlines;

        return $this;
    }

    /**
     * Get the plain text part of a multipart email
     *
     * @return  string
     */
    public function getTextBody(): string
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
     * @return  CustomHeaderCollection
     */
    public function getCustomHeaders(): CustomHeaderCollection
    {
        return $this->custom_headers;
    }

    /**
     * Set the value of template_id
     *
     * @param  string  $template_id
     * @return  self
     */
    public function setTemplateId(string $template_id)
    {
        $this->template_id = $template_id;

        return $this;
    }

    /**
     * Set the value of template_data
     *
     * @param  array  $template_data
     *
     * @return  self
     */
    public function setTemplateData(array $template_data)
    {
        $this->template_data = $template_data;

        return $this;
    }

    /**
     * Get version
     *
     * @return  int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set version
     *
     * @param  int  $version  version
     *
     * @return  self
     */
    public function setVersion(int $version)
    {
        $this->version = $version;

        return $this;
    }
}
