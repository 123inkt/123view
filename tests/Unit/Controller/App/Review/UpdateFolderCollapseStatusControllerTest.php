<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\UpdateFolderCollapseStatusController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\FolderCollapseStatus;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\FolderCollapseStatusRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends AbstractControllerTestCase<UpdateFolderCollapseStatusController>
 */
#[CoversClass(UpdateFolderCollapseStatusController::class)]
class UpdateFolderCollapseStatusControllerTest extends AbstractControllerTestCase
{
    private FolderCollapseStatusRepository&MockObject $folderCollapseRepository;

    protected function setUp(): void
    {
        $this->folderCollapseRepository = $this->createMock(FolderCollapseStatusRepository::class);
        parent::setUp();
    }

    public function testInvokeBadRequest(): void
    {
        $response = ($this->controller)(new Request(), new CodeReview());
        static::assertEquals(new Response(status: Response::HTTP_BAD_REQUEST), $response);
    }

    public function testInvokeCollapsed(): void
    {
        $user    = new User();
        $request = new Request(request: ['state' => 'collapsed', 'path' => 'test']);
        $review  = new CodeReview();
        $status = (new FolderCollapseStatus())->setReview($review)->setUser($user)->setPath('test');

        $this->expectGetUser($user);
        $this->folderCollapseRepository->expects($this->once())->method('save')->with($status);

        $response = ($this->controller)($request, $review);
        static::assertEquals(new Response(status: Response::HTTP_ACCEPTED), $response);
    }

    public function testInvokeExpand(): void
    {
        $user    = new User();
        $request = new Request(request: ['state' => 'expanded', 'path' => 'test']);
        $review  = new CodeReview();

        $this->expectGetUser($user);
        $this->folderCollapseRepository->expects($this->once())
            ->method('removeOneBy')
            ->with(['user' => $user, 'review' => $review, 'path' => 'test'], null, true);

        $response = ($this->controller)($request, $review);
        static::assertEquals(new Response(status: Response::HTTP_ACCEPTED), $response);
    }

    #[Override]
    public function getController(): AbstractController
    {
        return new UpdateFolderCollapseStatusController($this->folderCollapseRepository);
    }
}
