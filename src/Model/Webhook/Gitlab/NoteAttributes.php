<?php
declare(strict_types=1);

namespace DR\Review\Model\Webhook\Gitlab;

class NoteAttributes
{
    public string   $note;
    public Position $position;
}
