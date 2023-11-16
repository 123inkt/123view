<?php
declare(strict_types=1);

namespace DR\Review\ViewModel\Mail;

use DR\Review\Entity\Git\Commit;
use DR\Utils\Arrays;

class CommitsViewModel
{
    /**
     * @param Commit[] $commits
     */
    public function __construct(public readonly array $commits, public readonly string $theme, public readonly ?string $notificationReadUrl = null)
    {
    }

    /**
     * @return string[]
     */
    public function getAuthors(): array
    {
        $authors = [];
        foreach ($this->commits as $commit) {
            $authors[] = $commit->author->name;
        }

        return Arrays::unique($authors);
    }
}
