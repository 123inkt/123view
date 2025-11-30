<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Grep;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\GitRepositoryLockManager;
use Exception;

class LockableGitGrepService
{
    public function __construct(private readonly GitRepositoryLockManager $lockManager, private readonly GitGrepService $grepService)
    {
    }

    /**
     * @throws Exception
     */
    public function grep(Revision $revision, string $pattern, ?int $context = null): ?string
    {
        return $this->lockManager->start($revision->getRepository(), fn() => $this->grepService->grep($revision, $pattern, $context));
    }
}
