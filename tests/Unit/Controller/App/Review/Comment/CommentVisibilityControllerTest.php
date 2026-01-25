<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\CommentVisibilityController;
use DR\Review\Entity\Review\CommentVisibilityEnum;
use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserReviewSetting;
use DR\Review\Repository\User\UserReviewSettingRepository;
use DR\Review\Request\Comment\CommentVisibilityRequest;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @extends AbstractControllerTestCase<CommentVisibilityController>
 */
#[CoversClass(CommentVisibilityController::class)]
class CommentVisibilityControllerTest extends AbstractControllerTestCase
{
    private UserReviewSettingRepository&MockObject $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserReviewSettingRepository::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $user            = new User();
        $reviewSetting   = new UserReviewSetting();
        $validatedRequest = $this->createMock(CommentVisibilityRequest::class);

        $user->setReviewSetting($reviewSetting);

        $validatedRequest->expects(static::once())->method('getVisibility')->willReturn(CommentVisibilityEnum::NONE);
        $this->repository->expects(static::once())->method('save')->with($reviewSetting, true);
        $this->expectGetUser($user);

        /** @var JsonResponse $response */
        $response = ($this->controller)($validatedRequest);
        static::assertSame(CommentVisibilityEnum::NONE, $reviewSetting->getReviewCommentVisibility());
        static::assertSame('"ok"', $response->getContent());
    }

    public function getController(): AbstractController
    {
        return new CommentVisibilityController($this->repository);
    }
}
