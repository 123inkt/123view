<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Provider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use DR\Review\ApiPlatform\Factory\CommentOutputFactory;
use DR\Review\ApiPlatform\Output\CommentOutput;
use DR\Review\ApiPlatform\Provider\CommentProvider;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Comment\CommentVisibility;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(CommentProvider::class)]
class CommentProviderTest extends AbstractTestCase
{
    private CommentRepository&MockObject    $commentRepository;
    private UserEntityProvider&MockObject   $userProvider;
    private CommentVisibility&MockObject    $commentVisibility;
    private CommentOutputFactory&MockObject $commentOutputFactory;
    private CommentProvider                 $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commentRepository    = $this->createMock(CommentRepository::class);
        $this->userProvider         = $this->createMock(UserEntityProvider::class);
        $this->commentVisibility    = $this->createMock(CommentVisibility::class);
        $this->commentOutputFactory = $this->createMock(CommentOutputFactory::class);
        $this->provider             = new CommentProvider(
            $this->commentRepository,
            $this->userProvider,
            $this->commentVisibility,
            $this->commentOutputFactory,
        );
    }

    public function testProvideShouldOnlySupportGet(): void
    {
        $this->commentRepository->expects($this->never())->method('find');
        $this->userProvider->expects($this->never())->method('getCurrentUser');
        $this->commentVisibility->expects($this->never())->method('isVisible');
        $this->commentOutputFactory->expects($this->never())->method('create');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only Get operation is supported');
        $this->provider->provide(new GetCollection());
    }

    public function testProvideMapsVisibleComment(): void
    {
        $operation = new Get();
        $comment   = new Comment();
        $user      = new User()->setId(10);
        $output    = static::createStub(CommentOutput::class);

        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($comment);
        $this->userProvider->expects($this->once())->method('getCurrentUser')->willReturn($user);
        $this->commentVisibility->expects($this->once())->method('isVisible')->with($comment, $user)->willReturn(true);
        $this->commentOutputFactory->expects($this->once())->method('create')->with($comment)->willReturn($output);

        static::assertSame($output, $this->provider->provide($operation, ['id' => '123']));
    }

    public function testMissingCommentReturns404(): void
    {
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->userProvider->expects($this->once())->method('getCurrentUser')->willReturn(new User());
        $this->commentVisibility->expects($this->never())->method('isVisible');
        $this->commentOutputFactory->expects($this->never())->method('create');

        $this->expectException(NotFoundHttpException::class);
        $this->provider->provide(new Get(), ['id' => '123']);
    }

    public function testProvideThrows404WhenCommentIsHidden(): void
    {
        $comment = new Comment();
        $user    = new User()->setId(10);

        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($comment);
        $this->userProvider->expects($this->once())->method('getCurrentUser')->willReturn($user);
        $this->commentVisibility->expects($this->once())->method('isVisible')->with($comment, $user)->willReturn(false);
        $this->commentOutputFactory->expects($this->never())->method('create');

        $this->expectException(NotFoundHttpException::class);
        $this->provider->provide(new Get(), ['id' => '123']);
    }

    public function testProvideRejectsInvalidIdentifier(): void
    {
        $this->commentRepository->expects($this->never())->method('find');
        $this->userProvider->expects($this->never())->method('getCurrentUser');
        $this->commentVisibility->expects($this->never())->method('isVisible');
        $this->commentOutputFactory->expects($this->never())->method('create');

        $this->expectException(RuntimeException::class);
        $this->provider->provide(new Get(), ['id' => 'not-a-number']);
    }
}
