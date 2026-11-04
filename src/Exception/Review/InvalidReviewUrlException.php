<?php
declare(strict_types=1);

namespace DR\Review\Exception\Review;

class InvalidReviewUrlException extends ReviewException
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(string $url)
    {
        parent::__construct('Invalid review URL: ' . $url);
    }
}
