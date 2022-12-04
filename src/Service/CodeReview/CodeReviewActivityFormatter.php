<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview;

use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Entity\Review\CodeReviewActivity;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Message\Review\ReviewAccepted;
use DR\GitCommitNotification\Message\Review\ReviewClosed;
use DR\GitCommitNotification\Message\Review\ReviewCreated;
use DR\GitCommitNotification\Message\Review\ReviewOpened;
use DR\GitCommitNotification\Message\Review\ReviewRejected;
use DR\GitCommitNotification\Message\Review\ReviewResumed;
use DR\GitCommitNotification\Message\Reviewer\ReviewerAdded;
use DR\GitCommitNotification\Message\Reviewer\ReviewerRemoved;
use DR\GitCommitNotification\Message\Reviewer\ReviewerStateChanged;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionRemoved;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Repository\User\UserRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class CodeReviewActivityFormatter
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly UserRepository $userRepository,
        private readonly RevisionRepository $revisionRepository,
        private string $applicationName
    ) {
    }

    public function format(User $user, CodeReviewActivity $activity): ?string
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
            }
        }

        return $params;
    }

    private function getTranslationId(CodeReviewActivity $activity): ?string
    {
        switch ($activity->getEventName()) {
            case ReviewerRemoved::NAME:
                return $activity->getDataValue('userId') !== $activity->getDataValue('byUserId')
                    ? 'timeline.reviewer.removed.by'
                    : 'timeline.reviewer.removed';
            case ReviewerAdded::NAME:
                return $activity->getDataValue('userId') !== $activity->getDataValue('byUserId')
                    ? 'timeline.reviewer.added.by'
                    : 'timeline.reviewer.added';
            case ReviewerStateChanged::NAME:
                if ($activity->getDataValue('newState') === CodeReviewerStateType::ACCEPTED) {
                    return 'timeline.reviewer.accepted';
                }
                if ($activity->getDataValue('newState') === CodeReviewerStateType::REJECTED) {
                    return 'timeline.reviewer.rejected';
                }

                return null;
            case ReviewCreated::NAME:
                return 'timeline.review.created.from.revision';
            case ReviewClosed::NAME:
                return 'timeline.review.closed';
            case ReviewAccepted::NAME:
                return 'timeline.review.accepted';
            case ReviewRejected::NAME:
                return 'timeline.review.rejected';
            case ReviewOpened::NAME:
                return 'timeline.review.opened';
            case ReviewResumed::NAME:
                return 'timeline.review.resumed';
            case ReviewRevisionAdded::NAME:
                return 'timeline.review.revision.added';
            case ReviewRevisionRemoved::NAME:
                return 'timeline.review.revision.removed';
            default:
                return null;
        }
    }
}
