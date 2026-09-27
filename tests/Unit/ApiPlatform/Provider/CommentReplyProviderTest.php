<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Provider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use DR\Review\ApiPlatform\Factory\CommentReplyOutputFactory;
use DR\Review\ApiPlatform\Output\CommentReplyOutput;
use DR\Review\ApiPlatform\Provider\CommentReplyProvider;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Service\CodeReview\Comment\CommentVisibility;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(CommentReplyProvider::class)]
class CommentReplyProviderTest extends AbstractTestCase
{
    private CommentReplyRepository&MockObject    $commentReplyRepository;
    private UserEntityProvider&MockObject        $userProvider;
    private CommentVisibility&MockObject         $commentVisibility;
    private CommentReplyOutputFactory&MockObject $commentReplyOutputFactory;
    private CommentReplyProvider                 $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentReplyRepository    = $this->createMock(CommentReplyRepository::class);
        $this->userProvider              = $this->createMock(UserEntityProvider::class);
        $this->commentVisibility         = $this->createMock(CommentVisibility::class);
        $this->commentReplyOutputFactory = $this->createMock(CommentReplyOutputFactory::class);
        $this->provider                  = new CommentReplyProvider(
            $this->commentReplyRepository,
            $this->userProvider,
            $this->commentVisibility,
            $this->commentReplyOutputFactory,
        );
    }

    public function testOnlyItemOperationsAreAccepted(): void
    {
        $this->commentReplyRepository->expects($this->never())->method('find');
        $this->userProvider->expects($this->never())->method('getCurrentUser');
        $this->commentVisibility->expects($this->never())->method('isVisible');
        $this->commentReplyOutputFactory->expects($this->never())->method('create');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Only Get operation is supported');
        $this->provider->provide(new GetCollection(), ['id' => '123']);
    }

    public function testProvideMapsVisibleReply(): void
    {
        $operation = new Get();
        $comment   = new Comment();
        $reply     = new CommentReply();
        $user      = new User()->setId(10);
        $output    = static::createStub(CommentReplyOutput::class);

        $reply->setComment($comment);
        $this->commentReplyRepository->expects($this->once())->method('find')->with(123)->willReturn($reply);
        $this->userProvider->expects($this->once())->method('getCurrentUser')->willReturn($user);
        $this->commentVisibility->expects($this->once())->method('isVisible')->with($comment, $user)->willReturn(true);
        $this->commentReplyOutputFactory->expects($this->once())->method('create')->with($reply)->willReturn($output);

        static::assertSame($output, $this->provider->provide($operation, ['id' => '123']));
    }

    public function testMissingReplyReturns404(): void
    {
        $this->commentReplyRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->userProvider->expects($this->once())->method('getCurrentUser')->willReturn(new User());
        $this->commentVisibility->expects($this->never())->method('isVisible');
        $this->commentReplyOutputFactory->expects($this->never())->method('create');

        $this->expectException(NotFoundHttpException::class);
        $this->provider->provide(new Get(), ['id' => '123']);
    }

    public function testHiddenReplyReturns404(): void
    {
        $comment = new Comment();
        $reply   = new CommentReply();
        $user    = new User()->setId(10);

        $reply->setComment($comment);
        $this->commentReplyRepository->expects($this->once())->method('find')->with(123)->willReturn($reply);
        $this->userProvider->expects($this->once())->method('getCurrentUser')->willReturn($user);
        $this->commentVisibility->expects($this->once())->method('isVisible')->with($comment, $user)->willReturn(false);
        $this->commentReplyOutputFactory->expects($this->never())->method('create');

        $this->expectException(NotFoundHttpException::class);
        $this->provider->provide(new Get(), ['id' => '123']);
    }

    public function testProvideRejectsInvalidIdentifier(): void
    {
        $this->commentReplyRepository->expects($this->never())->method('find');
        $this->userProvider->expects($this->never())->method('getCurrentUser');
        $this->commentVisibility->expects($this->never())->method('isVisible');
        $this->commentReplyOutputFactory->expects($this->never())->method('create');

        $this->expectException(RuntimeException::class);
        $this->provider->provide(new Get(), ['id' => 'not-a-number']);
    }
}
