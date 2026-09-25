<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\User\UserEntityProvider;
use Twig\Attribute\AsTwigFunction;

class DraftCommentExtension
{
    private ?int $draftCount = null;

    public function __construct(private readonly UserEntityProvider $userProvider, private readonly CommentRepository $commentRepository)
    {
    }

    #[AsTwigFunction(name: 'draft_comment_count')]
    public function getDraftCount(): int
    {
        if ($this->draftCount !== null) {
            return $this->draftCount;
        }

        $user = $this->userProvider->getUser();
        if ($user === null) {
            return $this->draftCount = 0;
        }

        return $this->draftCount = $this->commentRepository->countDraftsByUser($user);
    }
}
