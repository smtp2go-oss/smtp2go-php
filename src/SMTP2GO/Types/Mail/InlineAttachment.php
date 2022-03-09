<?php

namespace SMTP2GO\Types\Mail;

use SMTP2GO\Types\Mail\Attachment;


class InlineAttachment extends Attachment
{

    public function __construct(string $filename, string $data, string $mimetype)
    {
        $this->filename = $filename;
        $this->fileblob = base64_encode($data);
        $this->mimetype = $mimetype;
    }
}
