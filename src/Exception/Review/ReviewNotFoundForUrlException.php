<?php
declare(strict_types=1);

namespace DR\Review\Exception\Review;

class ReviewNotFoundForUrlException extends ReviewException
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(string $repositoryName, int $projectId)
    {
        parent::__construct(sprintf('Review cr-%d not found in repository: %s', $projectId, $repositoryName));
    }
}
