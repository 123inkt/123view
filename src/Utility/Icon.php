<?php
declare(strict_types=1);

namespace DR\Review\Utility;

class Icon
{
    public static function getBase64(string $filepath): string
    {
        return 'data:image/' . pathinfo($filepath, PATHINFO_EXTENSION) . ';base64,' . base64_encode((string)file_get_contents($filepath));
    }
}
