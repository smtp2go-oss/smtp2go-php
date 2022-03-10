<?php

namespace SMTP2GO\Collections\Mail;


use SMTP2GO\Collections\Collection;
use SMTP2GO\Types\Mail\CustomHeader;

class CustomHeaderCollection extends Collection
{
    protected $items;

    public function __construct(array $headers = [])
    {
        foreach ($headers as $header) {
            $this->add($header);
        }
    }

    public function add($header)
    {
        if (is_a($header, CustomHeader::class)) {
            $this->items[] = $header;
        } else {
            throw new \InvalidArgumentException('This collection expects objects of type ' . CustomHeader::class, ' but recieved ' . get_class($header));
        }
        return $this;
    }
}
