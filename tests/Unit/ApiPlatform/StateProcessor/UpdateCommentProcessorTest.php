<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\StateProcessor;

use ApiPlatform\Metadata\Patch;
use DR\PHPUnitExtensions\Symfony\ClockTestTrait;
use DR\Review\ApiPlatform\Factory\CommentOutputFactory;
use DR\Review\ApiPlatform\Input\UpdateCommentInput;
use DR\Review\ApiPlatform\Output\CommentOutput;
use DR\Review\ApiPlatform\StateProcessor\UpdateCommentProcessor;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Entity\Review\CommentTagEnum;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Comment\CommentVisibility;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

#[CoversClass(UpdateCommentProcessor::class)]
class UpdateCommentProcessorTest extends AbstractTestCase
{
    use ClockTestTrait;

    private CommentRepository&MockObject $commentRepository;
    private UserEntityProvider $userProvider;
    private CommentVisibility $commentVisibility;
    private CommentOutputFactory $outputFactory;
    private UpdateCommentProcessor $processor;
    private Comment $comment;
    private User $author;
    private User $currentUser;
    private bool $visible = true;
    private bool $saved = false;
    private int $saveCount = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->userProvider      = static::createStub(UserEntityProvider::class);
        $this->commentVisibility = static::createStub(CommentVisibility::class);
        $this->outputFactory     = static::createStub(CommentOutputFactory::class);
        $this->processor         = new UpdateCommentProcessor(
            $this->commentRepository,
            $this->userProvider,
            $this->commentVisibility,
            $this->outputFactory,
        );

        $this->author      = new User()->setId(10);
        $this->currentUser = $this->author;
        $this->comment     = new Comment()
            ->setId(123)
            ->setUser($this->author)
            ->setMessage('Original comment')
            ->setTag(CommentTagEnum::Suggestion)
            ->setType(CommentTypeEnum::Final)
            ->setState(CommentStateEnum::Open)
            ->setUpdateTimestamp(2000);

        $this->commentRepository
            ->expects($this->once())
            ->method('find')
            ->willReturnCallback(fn(int|string $id): ?Comment => (int)$id === 123 ? $this->comment : null);
        $this->userProvider->method('getCurrentUser')->willReturnCallback(fn(): User => $this->currentUser);
        $this->commentVisibility->method('isVisible')->willReturnCallback(fn(): bool => $this->visible);
        $this->outputFactory->method('create')->willReturnCallback(function (): CommentOutput {
            self::assertTrue($this->saved, 'Output must be created only after persistence.');

            return static::createStub(CommentOutput::class);
        });
    }

    public function testSavesCombinedAuthorUpdateOnce(): void
    {
        $input          = $this->input('  Updated comment  ');
        $input->setTag(null);
        $input->state   = CommentStateEnum::Resolved;
        $this->commentRepository->expects($this->once())->method('save')->with($this->comment, true)->willReturnCallback(function (): void {
            $this->saved = true;
            $this->saveCount++;
        });
        $this->processor->process($input, new Patch(), ['id' => '123']);
        self::assertSame(1, $this->saveCount);
        self::assertSame('Updated comment', $this->comment->getMessage());
        self::assertNull($this->comment->getTag());
        self::assertSame(CommentStateEnum::Resolved, $this->comment->getState());
        self::assertSame(self::time(), $this->comment->getUpdateTimestamp());
    }

    public function testNonAuthorCanChangeFinalState(): void
    {
        $this->currentUser = new User()->setId(20);
        $input             = new UpdateCommentInput();
        $input->state      = CommentStateEnum::Resolved;
        $this->commentRepository->expects($this->once())->method('save')->with($this->comment, true)->willReturnCallback(function (): void {
            $this->saved = true;
            $this->saveCount++;
        });

        $this->processor->process($input, new Patch(), ['id' => '123']);

        self::assertSame(1, $this->saveCount);
        self::assertSame(CommentStateEnum::Resolved, $this->comment->getState());
        self::assertSame('Original comment', $this->comment->getMessage());
        self::assertSame(CommentTagEnum::Suggestion, $this->comment->getTag());
        self::assertSame(self::time(), $this->comment->getUpdateTimestamp());
    }

    public function testMixedNonAuthorUpdateFailsAtomically(): void
    {
        $this->currentUser = new User()->setId(20);
        $input             = $this->input('Unauthorized edit');
        $input->state      = CommentStateEnum::Resolved;
        $this->commentRepository->expects($this->never())->method('save');

        try {
            $this->processor->process($input, new Patch(), ['id' => '123']);
            self::fail('Expected the mixed update to be denied.');
        } catch (AccessDeniedHttpException) {
            self::assertSame('Original comment', $this->comment->getMessage());
            self::assertSame(CommentStateEnum::Open, $this->comment->getState());
            self::assertSame(2000, $this->comment->getUpdateTimestamp());
            self::assertFalse($this->saved);
        }
    }

    public function testRejectsDraftStateChangeBeforeSave(): void
    {
        $this->comment->setType(CommentTypeEnum::Draft);
        $input        = new UpdateCommentInput();
        $input->state = CommentStateEnum::Resolved;
        $this->commentRepository->expects($this->never())->method('save');

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->processor->process($input, new Patch(), ['id' => '123']);
    }

    public function testReturnsNotFoundForHiddenDraft(): void
    {
        $this->comment->setType(CommentTypeEnum::Draft);
        $this->currentUser = new User()->setId(20);
        $this->visible      = false;
        $this->commentRepository->expects($this->never())->method('save');

        $this->expectException(NotFoundHttpException::class);
        $this->processor->process($this->input('Update'), new Patch(), ['id' => '123']);
    }

    public function testReturnsNotFoundForMissingComment(): void
    {
        $this->commentRepository->expects($this->never())->method('save');

        $this->expectException(NotFoundHttpException::class);
        $this->processor->process($this->input('Update'), new Patch(), ['id' => '456']);
    }

    private function input(string $message): UpdateCommentInput
    {
        $input          = new UpdateCommentInput();
        $input->message = $message;

        return $input;
    }

    protected function freezeTimeAt(): int
    {
        return 1_700_000_000;
    }
}
