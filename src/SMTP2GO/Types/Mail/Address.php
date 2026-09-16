<?php

namespace SMTP2GO\Types\Mail;

class Address
{
    protected $email = '';
    protected $name  = '';

    public function __construct(string $email, string $name = '')
    {
        $this->email = $email;
        $this->name  = $name;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function setName(string $name): Address
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }


    public function setEmail(string $email): Address
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Render as an RFC-5322 address, quoting and escaping the display name so
     * that commas, quotes, colons etc. cannot break the header:
     * "Smith, John" <john@example.com>
     */
    public function toString(): string
    {
        $name  = $this->stripControlCharacters($this->name);
        $email = str_replace(['<', '>'], '', $this->stripControlCharacters($this->email));

        if ($name === '') {
            return $email;
        }

        return '"' . addcslashes($name, '"\\') . '" <' . $email . '>';
    }

    private function stripControlCharacters(string $value): string
    {
        return preg_replace('/[[:cntrl:]]/', '', $value) ?? "";
    }
}
