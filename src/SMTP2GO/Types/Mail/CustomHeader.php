<?php

namespace SMTP2GO\Types\Mail;

class CustomHeader
{
    protected $header = '';

    protected $value = '';

    /**
     * A header name must be an RFC-5322 field name: printable US-ASCII
     * characters excluding the colon, which separates the name from the value.
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
     * @throws \InvalidArgumentException if $header is not a valid header name
     */
    public function setHeader(string $header)
    {
        if (!preg_match(static::VALID_HEADER_NAME, $header)) {
            throw new \InvalidArgumentException(
                'Invalid header name "' . $header . '". A header name must consist of printable '
                    . 'ASCII characters excluding the colon.'
            );
        }

        $this->header = $header;

        return $this;
    }
}
