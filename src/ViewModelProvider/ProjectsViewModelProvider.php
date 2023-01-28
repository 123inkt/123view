<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use Doctrine\DBAL\Exception;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Message\Review\ReviewRejected;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CodeReviewActivityRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityFormatter;
use DR\Review\ViewModel\App\Review\ProjectsViewModel;
use DR\Review\ViewModel\App\Review\Timeline\TimelineEntryViewModel;
use DR\Review\ViewModel\App\Review\Timeline\TimelineViewModel;

class ProjectsViewModelProvider
{
    private const FEED_EVENTS = [
        ReviewAccepted::NAME,
        ReviewRejected::NAME,
        CommentAdded::NAME,
        CommentReplyAdded::NAME,
        CommentResolved::NAME,
    ];

    public function __construct(
        private readonly RepositoryRepository $repositoryRepository,
        private readonly RevisionRepository $revisionRepository,
        private readonly CodeReviewActivityRepository $activityRepository,
        private readonly CodeReviewActivityFormatter $activityFormatter,
        private readonly User $user,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getProjectsViewModel(): ProjectsViewModel
    {
        $repositories  = $this->repositoryRepository->findBy(['active' => 1], ['displayName' => 'ASC']);
        $revisionCount = $this->revisionRepository->getRepositoryRevisionCount();

        $activities      = $this->activityRepository->findForUser($this->user->getId(), self::FEED_EVENTS);
        $timelineEntries = [];
        foreach ($activities as $activity) {
            $message = $this->activityFormatter->format($activity);
            if ($message === null) {
                continue;
            }
            $timelineEntries[] = new TimelineEntryViewModel([$activity], $message, null);
        }
        $timelineViewModel = new TimelineViewModel($timelineEntries);

        return new ProjectsViewModel($repositories, $revisionCount, $timelineViewModel);
    }
}
