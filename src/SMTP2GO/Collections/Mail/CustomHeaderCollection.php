<?php

namespace SMTP2GO\Collections\Mail;


use SMTP2GO\Collections\Collection;
use SMTP2GO\Types\Mail\CustomHeader;

class CustomHeaderCollection extends Collection
{
    /**
     * 
     * @var array The collection of headers
     */
    protected $items = [];

    /**
     * Headers that are allowed to be duplicated per https://www.rfc-editor.org/rfc/rfc5322#section-3.6
     */
    const ALLOWED_MULTIPLE_HEADERS = [
        'comments',
        'keywords',
        'optional-field',
        'trace',
        'resent-date',
        'resent-from',
        'resent-sender',
        'resent-to',
        'resent-cc',
        'resent-bcc',
        'resent-msg-id'
    ];

    public function __construct(array $headers = [])
    {
        foreach ($headers as $header) {
            $this->add($header);
        }
    }

    public function add($header)
    {

        if (is_a($header, CustomHeader::class)) {
            /** @var CustomHeader $header */
            if (in_array(strtolower($header->getHeader()), static::ALLOWED_MULTIPLE_HEADERS)) {
                $this->items[] = $header;
            } else {
                $found = false;
                foreach ($this->items as $customHeader) {
                    /** @var CustomHeader $customHeader */
                    if (strtolower($header->getHeader()) === strtolower($customHeader->getHeader())) {
                        $customHeader->setValue($header->getValue());
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $this->items[] = $header;
                }
            }
        } else {
            throw new \InvalidArgumentException('This collection expects objects of type ' . CustomHeader::class, ' but recieved ' . get_class($header));
        }
        return $this;
    }
}
