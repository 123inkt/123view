<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\ExternalTool\Gitlab\GitlabService;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Utils\Arrays;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

class GetMergeRequestForReviewController extends AbstractController
{
    public function __construct(
        private readonly string $gitlabApiUrl,
        private readonly GitlabService $gitlabService,
        private readonly CodeReviewRevisionService $revisionService,
    ) {
    }

    /**
     * @throws Throwable
     */
    #[Route('/api/review/{id<\d+>}/merge-request', name: self::class, methods: 'GET', stateless: true)]
    public function __invoke(#[MapEntity] CodeReview $review): JsonResponse
    {
        if ($this->gitlabApiUrl === '') {
            return new JsonResponse(null, headers: ['Cache-Control' => 'public']);
        }

        $projectId = $review->getRepository()->getRepositoryProperty('gitlab-project-id');
        if ($projectId === null) {
            return new JsonResponse(null, headers: ['Cache-Control' => 'public']);
        }

        $revisions = array_reverse($this->revisionService->getRevisions($review));
        $remoteRef = Arrays::findOrNull($revisions, static fn($value) => $value->getFirstBranch() !== null)?->getFirstBranch();
        if ($remoteRef === null) {
            return new JsonResponse(null, headers: ['Cache-Control' => 'public,max-age=3600']);
        }

        $url = $this->gitlabService->getMergeRequestUrl((int)$projectId, $remoteRef);

        return new JsonResponse(
            ['url' => $url, 'icon' => 'bi-gitlab', 'title' => 'Go to merge request in gitlab'],
            headers: ['Cache-Control' => 'public,max-age=86400']
        );
    }
}
