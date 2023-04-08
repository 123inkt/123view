<?php
declare(strict_types=1);

namespace DR\Review\Model\Review\QueryParser;

use DR\Review\Model\QueryParser\TermInterface;

class MatchAuthor implements TermInterface
{
    public readonly string $author;

    /**
     * @param string|string[] $author
     */
    public function __construct(string|array $author)
    {
        $this->author = is_array($author) ? implode('', $author) : $author;
    }
}
