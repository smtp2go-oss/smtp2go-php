<?php

namespace SMTP2GO\Collections\Mail;

use SMTP2GO\Types\Mail\Attachment;
use SMTP2GO\Collections\Collection;
use SMTP2GO\Types\Mail\FileAttachment;

class AttachmentCollection extends Collection
{
    protected $items;

    public function __construct(array $attachments = [])
    {
        foreach ($attachments as $attachment) {
            $this->add($attachment);
        }
    }

    public function add($attachment)
    {
        if (is_a($attachment, Attachment::class) || is_a($attachment, FileAttachment::class)) {
            $this->items[] = $attachment;
        } else {
            throw new \InvalidArgumentException('This collection expects objects of type ' . Attachment::class, ' but recieved ' . get_class($attachment));
        }
        return $this;
    }
}
