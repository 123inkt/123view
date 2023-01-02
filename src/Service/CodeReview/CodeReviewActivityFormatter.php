<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\Review\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentRemoved;
use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Message\Review\ReviewClosed;
use DR\Review\Message\Review\ReviewCreated;
use DR\Review\Message\Review\ReviewOpened;
use DR\Review\Message\Review\ReviewRejected;
use DR\Review\Message\Review\ReviewResumed;
use DR\Review\Message\Reviewer\ReviewerAdded;
use DR\Review\Message\Reviewer\ReviewerRemoved;
use DR\Review\Message\Reviewer\ReviewerStateChanged;
use DR\Review\Message\Revision\ReviewRevisionAdded;
use DR\Review\Message\Revision\ReviewRevisionRemoved;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\Review\RevisionRepository;
use DR\Review\Repository\User\UserRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class CodeReviewActivityFormatter
{
    private const TRANSLATION_MAP = [
        ReviewerAdded::NAME . '-by'              => 'timeline.reviewer.added.by',
        ReviewerAdded::NAME                      => 'timeline.reviewer.added',
        ReviewerRemoved::NAME . '-by'            => 'timeline.reviewer.removed.by',
        ReviewerRemoved::NAME                    => 'timeline.reviewer.removed',
        ReviewCreated::NAME                      => 'timeline.review.created.from.revision',
        ReviewClosed::NAME                       => 'timeline.review.closed',
        ReviewerStateChanged::NAME . '-accepted' => 'timeline.reviewer.accepted',
        ReviewerStateChanged::NAME . '-rejected' => 'timeline.reviewer.rejected',
        ReviewAccepted::NAME                     => 'timeline.review.accepted',
        ReviewRejected::NAME                     => 'timeline.review.rejected',
        ReviewOpened::NAME                       => 'timeline.review.opened',
        ReviewResumed::NAME                      => 'timeline.review.resumed',
        ReviewRevisionAdded::NAME                => 'timeline.review.revision.added',
        ReviewRevisionRemoved::NAME              => 'timeline.review.revision.removed',
        CommentAdded::NAME                       => 'timeline.comment.added',
        CommentRemoved::NAME                     => 'timeline.comment.removed',
        CommentResolved::NAME                    => 'timeline.comment.resolved',
    ];

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UserRepository $userRepository,
        private readonly RevisionRepository $revisionRepository,
        private readonly CommentRepository $commentRepository,
        private string $applicationName
    ) {
    }

    public function format(CodeReviewActivity $activity, ?User $user = null): ?string
    {
        $translationId = $this->getTranslationId($activity);
        if ($translationId === null) {
            return null;
        }
        $username = $user === $activity->getUser() ? $this->translator->trans('you') : $activity->getUser()?->getName() ?? $this->applicationName;

        $params = $this->addCustomParams($activity, ['username' => $username]);

        // html escape as the translation strings are html
        $params = array_map(static fn(string $val): string => htmlspecialchars($val, ENT_QUOTES), $params);

        return $this->translator->trans($translationId, $params);
    }

    /**
     * @param array<string, string> $params
     *
     * @return array<string, string>
     */
    private function addCustomParams(CodeReviewActivity $activity, array $params): array
    {
        // when reviewer was added/removed by someone else, set reviewer name
        if (in_array($activity->getEventName(), [ReviewerRemoved::NAME, ReviewerAdded::NAME], true)
            && $activity->getDataValue('userId') !== $activity->getDataValue('byUserId')) {
            $params['reviewerName'] = $this->userRepository->find((int)$activity->getDataValue('userId'))?->getName() ?? '';
        }

        // when revision was added or removed, add revision hash + message
        if (in_array($activity->getEventName(), [ReviewRevisionAdded::NAME, ReviewRevisionRemoved::NAME], true)) {
            $revision = $this->revisionRepository->find((int)$activity->getDataValue('revisionId'));
            if ($revision instanceof Revision) {
                $params['revision'] = sprintf('%s - %s', substr((string)$revision->getCommitHash(), 0, 8), $revision->getTitle());
            } else {
                $params['revision'] = (string)$activity->getDataValue('title');
            }
        }

        // add filepath the comment was added to
        if (in_array($activity->getEventName(), [CommentAdded::NAME, CommentResolved::NAME, CommentRemoved::NAME], true)) {
            $comment        = $this->commentRepository->find((int)$activity->getDataValue('commentId'));
            $params['file'] = basename($comment?->getFilePath() ?? (string)$activity->getDataValue('file'));
        }

        return $params;
    }

    private function getTranslationId(CodeReviewActivity $activity): ?string
    {
        $key = $activity->getEventName();

        switch ($activity->getEventName()) {
            case ReviewerAdded::NAME:
            case ReviewerRemoved::NAME:
                $key .= $activity->getDataValue('userId') !== $activity->getDataValue('byUserId') ? '-by' : '';
                break;
            case ReviewerStateChanged::NAME:
                $key .= '-' . $activity->getDataValue('newState');
                break;
        }

        return self::TRANSLATION_MAP[$key] ?? null;
    }
}
