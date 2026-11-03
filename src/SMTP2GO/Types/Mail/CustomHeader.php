<?php

namespace SMTP2GO\Types\Mail;

class CustomHeader
{
    protected $header = '';

    protected $value = '';

    /**
     * @deprecated 
     * A header name must be an RFC-5322 field name: printable US-ASCII
     * characters excluding the colon, which separates the name from the value.
     * This was used in 1.3.0 but for API compatability, we'll rely on its validation instead
     */
    const VALID_HEADER_NAME = '/^[!-9;-~]+$/';

    /**
     *
     * @param string $header
     * @param string $value
     * @return void
     * @throws \InvalidArgumentException if $header is not a valid header name
     */
    public function __construct(string $header, string $value = '')
    {
        $this->setHeader($header);
        $this->setValue($value);
    }


    public function getValue()
    {
        return $this->value;
    }

    /**     
     *
     * Set and sanitize the value
     *
     * @return  self
     */
    public function setValue(string $value)
    {
        $this->value = trim(preg_replace('/[[:cntrl:]]/', '', $value) ?? '');

        return $this;
    }

    /**
     * Get the value of header
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Set the value of header
     *
     *
     * @return  self

     */
    public function setHeader(string $header)
    {
        $this->header = $header;

        return $this;
    }
}
