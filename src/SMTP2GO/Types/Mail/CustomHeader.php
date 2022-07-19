<?php

namespace SMTP2GO\Types\Mail;

class CustomHeader
{
    protected $header;

    protected $value = '';

    /**
     * 
     * @param string $header 
     * @param string $value 
     * @return void 
     */
    public function __construct(string $header, string $value = '')
    {
        $this->header = $header;
        $this->value = $value;
    }

    /**
     * Get the value of value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of value
     *
     * @return  self
     */
    public function setValue($value)
    {
        $this->value = $value;

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
     * @return  self
     */
    public function setHeader($header)
    {
        $this->header = $header;

        return $this;
    }
}
