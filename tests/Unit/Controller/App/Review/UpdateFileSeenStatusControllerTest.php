<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Review;

use DR\GitCommitNotification\Controller\App\Review\UpdateFileSeenStatusController;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Request\Review\FileSeenStatusRequest;
use DR\GitCommitNotification\Service\CodeReview\FileSeenStatusService;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Review\UpdateFileSeenStatusController
 * @covers ::__construct
 */
class UpdateFileSeenStatusControllerTest extends AbstractControllerTestCase
{
    private FileSeenStatusService&MockObject      $statusService;
    private ReviewDiffServiceInterface&MockObject $diffService;

    public function setUp(): void
    {
        $this->statusService = $this->createMock(FileSeenStatusService::class);
        $this->diffService   = $this->createMock(ReviewDiffServiceInterface::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeMarkAsSeen(): void
    {
        $request = $this->createMock(FileSeenStatusRequest::class);
        $request->method('getFilePath')->willReturn('filepath');
        $request->method('getSeenStatus')->willReturn(true);

        $revision   = new Revision();
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setRepository($repository);
        $review->getRevisions()->add($revision);

        $diffFileA                = new DiffFile();
        $diffFileA->filePathAfter = 'filepath';
        $diffFileB                = new DiffFile();

        $user = new User();
        $this->expectGetUser($user);

        $this->diffService->expects(self::once())->method('getDiffFiles')->with($repository, [$revision])->willReturn([$diffFileA, $diffFileB]);
        $this->statusService->expects(self::once())->method('markAsSeen')->with($review, $user, $diffFileA);

        /** @var Response $response */
        $response = ($this->controller)($request, $review);
        static::assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeMarkAsUnseen(): void
    {
        $request = $this->createMock(FileSeenStatusRequest::class);
        $request->method('getFilePath')->willReturn('filepath');
        $request->method('getSeenStatus')->willReturn(false);

        $revision   = new Revision();
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setRepository($repository);
        $review->getRevisions()->add($revision);

        $diffFileA                = new DiffFile();
        $diffFileA->filePathAfter = 'filepath';
        $diffFileB                = new DiffFile();

        $user = new User();
        $this->expectGetUser($user);

        $this->diffService->expects(self::once())->method('getDiffFiles')->with($repository, [$revision])->willReturn([$diffFileA, $diffFileB]);
        $this->statusService->expects(self::once())->method('markAsUnseen')->with($review, $user, $diffFileA);

        /** @var Response $response */
        $response = ($this->controller)($request, $review);
        static::assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeFilepathDoesntMatchAFile(): void
    {
        $request = $this->createMock(FileSeenStatusRequest::class);
        $request->method('getFilePath')->willReturn('filepath');
        $request->method('getSeenStatus')->willReturn(false);

        $revision   = new Revision();
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setRepository($repository);
        $review->getRevisions()->add($revision);

        $diffFile = new DiffFile();

        $this->diffService->expects(self::once())->method('getDiffFiles')->with($repository, [$revision])->willReturn([$diffFile]);

        /** @var Response $response */
        $response = ($this->controller)($request, $review);
        static::assertSame(Response::HTTP_NOT_MODIFIED, $response->getStatusCode());
    }

    public function getController(): AbstractController
    {
        return new UpdateFileSeenStatusController($this->statusService, $this->diffService);
    }
}
