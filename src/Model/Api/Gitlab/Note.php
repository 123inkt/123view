<?php
declare(strict_types=1);

namespace DR\Review\Model\Api\Gitlab;

class Note
{
    public int      $id;
    public string   $body;
    public ?Position $position = null;
}
