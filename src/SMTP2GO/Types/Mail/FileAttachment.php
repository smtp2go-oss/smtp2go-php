<?php

namespace SMTP2GO\Types\Mail;


use SMTP2GO\Mime\Detector;

class FileAttachment
{
    /**
     * @var string The raw data of the attachment, not base64 encoded
     */
    protected $body;

    /**
     * @var string The name to give the attached file
     */
    protected $filename;

    /**
     * @var string The mimetype of the attachment based on the filename
     */
    protected $mimetype;

    public function __construct($body, $filename)
    {

        $detector = new Detector;
        $this->body = $body;
        $this->filename = $filename;
        $this->mimetype = $detector->detectMimeType($this->filename) ?? 'application/octet-stream';
    }

    public function toArray()
    {
        return array(
            'filename' => $this->getFileName(),
            'fileblob' => base64_encode($this->getBody()),
            'mimetype' => $this->getMimetype(),
        );
    }

    /**
     * Get the value of body
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set the value of body
     *
     * @return  self
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Set the value of filename
     *
     * @return  self
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get the value of filename
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get the value of mimetype
     */
    public function getMimetype()
    {
        return $this->mimetype;
    }

    /**
     * Set the value of mimetype
     *
     * @return  self
     */
    public function setMimetype($mimetype)
    {
        $this->mimetype = $mimetype;

        return $this;
    }
}
