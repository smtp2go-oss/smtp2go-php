<?php

namespace SMTP2GO\Types\Mail;

use SMTP2GO\Mime\Detector;

class Attachment
{

    /**
     * The patch to the the attachment
     * @var string
     */
    protected  $filepath;

    /**
     * The name to give the attached file
     * @var string
     */
    protected  $filename;

    /**
     * The raw data of the attachment, not base64 encoded
     * @var string
     */
    protected  $fileblob;

    /**
     * The mimetype of the attachment
     * @var string
     */
    protected  $mimetype;

    /**
     * 
     * @param string $filepath 
     * @param string $filename - by default the name will be determined from the $filepath
     * @return void 
     */
    public function __construct(string $filepath, string $filename = '')
    {
        $this->filepath = $filepath;
        $this->fileblob = base64_encode(file_get_contents($filepath));
        $this->filename = $filename != '' ? $filename : basename($this->filepath);

        $detector = new Detector;
        $this->mimetype = $detector->detectMimeType($this->filepath);
    }

    public function toArray()
    {
        return array(
            'filename' => $this->getFileName(),
            'fileblob' => $this->getFileblob(),
            'mimetype' => $this->getMimetype(),
        );
    }

    /**
     * Get the name of the attachment
     *
     * @return  string
     */
    public function getfilepath()
    {
        return $this->filepath;
    }

    /**
     * Set the name of the attachment
     *
     * @param  string  $filepath  The name of the attachment
     *
     * @return  self
     */
    public function setfilepath(string $filepath)
    {
        $this->filepath = $filepath;

        return $this;
    }

    /**
     * Get the raw data of the attachment, not base64 encoded
     *
     * @return  string
     */
    public function getFileblob()
    {
        return $this->fileblob;
    }

    /**
     * Set the raw data of the attachment, not base64 encoded
     *
     * @param  string  $fileblob  The raw data of the attachment, not base64 encoded
     *
     * @return  self
     */
    public function setFileblob(string $fileblob)
    {
        $this->fileblob = $fileblob;

        return $this;
    }

    /**
     * Get the name to give the attached file
     *
     * @return  string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set the name to give the attached file
     *
     * @param  string  $filename  The name to give the attached file
     *
     * @return  self
     */
    public function setFilename(string $filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get the mimetype of the attachment
     *
     * @return  string
     */
    public function getMimetype()
    {
        return $this->mimetype;
    }

    /**
     * Set the mimetype of the attachment
     *
     * @param  string  $mimetype  The mimetype of the attachment
     *
     * @return  self
     */
    public function setMimetype(string $mimetype)
    {
        $this->mimetype = $mimetype;

        return $this;
    }
}
