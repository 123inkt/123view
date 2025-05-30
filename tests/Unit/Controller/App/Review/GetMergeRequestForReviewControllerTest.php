<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\GetMergeRequestForReviewController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryProperty;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\ExternalTool\Gitlab\GitlabService;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

/**
 * @extends AbstractControllerTestCase<GetMergeRequestForReviewController>
 */
#[CoversClass(GetMergeRequestForReviewController::class)]
class GetMergeRequestForReviewControllerTest extends AbstractControllerTestCase
{
    private GitlabService&MockObject             $gitlabService;
    private CodeReviewRevisionService&MockObject $revisionService;

    protected function setUp(): void
    {
        $this->gitlabService   = $this->createMock(GitlabService::class);
        $this->revisionService = $this->createMock(CodeReviewRevisionService::class);
        parent::setUp();
    }

    /**
     * @throws Throwable
     */
    public function testInvokeNoGitlabUrl(): void
    {
        $controller = new GetMergeRequestForReviewController('', $this->gitlabService, $this->revisionService);
        $review     = new CodeReview();

        $expected = new JsonResponse(null, headers: ['Cache-Control' => 'public']);
        static::assertEquals($expected, ($controller)($review));
    }

    public function testInvokeNoProjectId(): void
    {
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setRepository($repository);

        $expected = new JsonResponse(null, headers: ['Cache-Control' => 'public']);
        $response = ($this->controller)($review);
        static::assertEquals($expected, $response);
    }

    public function testInvokeNoRemoteRef(): void
    {
        $repository = new Repository();
        $repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '1'));
        $review = new CodeReview();
        $review->setRepository($repository);

        $expected = new JsonResponse(null, headers: ['Cache-Control' => 'public,max-age=3600']);
        $response = ($this->controller)($review);
        static::assertEquals($expected, $response);
    }

    public function testInvoke(): void
    {
        $revision = new Revision();
        $revision->setFirstBranch('remote-ref');
        $repository = new Repository();
        $repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '123'));
        $review = new CodeReview();
        $review->setRepository($repository);

        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->gitlabService->expects($this->once())->method('getMergeRequestUrl')->with(123, 'remote-ref')->willReturn('url');

        $expected = new JsonResponse(
            ['url' => 'url', 'icon' => 'bi-gitlab', 'title' => 'Go to merge request in gitlab'],
            headers: ['Cache-Control' => 'public,max-age=86400']
        );
        $response = ($this->controller)($review);
        static::assertEquals($expected, $response);
    }

    public function getController(): AbstractController
    {
        return new GetMergeRequestForReviewController('url', $this->gitlabService, $this->revisionService);
    }
}
