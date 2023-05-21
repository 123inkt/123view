<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use Doctrine\DBAL\Exception;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Message\Review\ReviewOpened;
use DR\Review\Message\Review\ReviewRejected;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\ViewModel\App\Project\ProjectsViewModel;

class ProjectsViewModelProvider
{
    private const FEED_EVENTS = [
        ReviewAccepted::NAME,
        ReviewRejected::NAME,
        ReviewOpened::NAME,
        CommentAdded::NAME,
        CommentResolved::NAME,
    ];

    public function __construct(
        private readonly RepositoryRepository $repositoryRepository,
        private readonly RevisionRepository $revisionRepository,
        private readonly ReviewTimelineViewModelProvider $timelineViewModelProvider,
        private readonly User $user,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getProjectsViewModel(): ProjectsViewModel
    {
        $repositories      = $this->repositoryRepository->findBy(['active' => 1], ['displayName' => 'ASC']);
        $revisionCount     = $this->revisionRepository->getRepositoryRevisionCount();
        $timelineViewModel = $this->timelineViewModelProvider->getTimelineViewModelForFeed($this->user, self::FEED_EVENTS);

        return new ProjectsViewModel($repositories, $revisionCount, $timelineViewModel);
    }
}
