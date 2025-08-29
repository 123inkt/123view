<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use Doctrine\DBAL\Exception;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Message\Review\ReviewOpened;
use DR\Review\Message\Review\ReviewRejected;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\User\UserService;
use DR\Review\Utility\Strings;
use DR\Review\ViewModel\App\Project\ProjectsViewModel;
use DR\Utils\Arrays;

class ProjectsViewModelProvider
{
    private const FEED_EVENTS = [
        ReviewAccepted::NAME,
        ReviewRejected::NAME,
        ReviewOpened::NAME,
        CommentAdded::NAME,
        CommentResolved::NAME,
        CommentReplyAdded::NAME
    ];

    public function __construct(
        private readonly RepositoryRepository $repositoryRepository,
        private readonly RevisionRepository $revisionRepository,
        private readonly ReviewTimelineViewModelProvider $timelineViewModelProvider,
        private readonly UserService $userService,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getProjectsViewModel(string $searchQuery): ProjectsViewModel
    {
        $repositories      = $this->repositoryRepository->findBy(['active' => 1], ['displayName' => 'ASC']);
        $revisionCount     = $this->revisionRepository->getRepositoryRevisionCount();
        $timelineViewModel = $this->timelineViewModelProvider->getTimelineViewModelForFeed($this->userService->getCurrentUser(), self::FEED_EVENTS);

        if ($searchQuery !== '') {
            $parts        = Arrays::explode(' ', $searchQuery);
            $repositories = array_filter($repositories, static fn($repository) => Strings::contains($repository->getDisplayName(), $parts));
        }

        return new ProjectsViewModel($repositories, $revisionCount, $timelineViewModel, $searchQuery);
    }
}
