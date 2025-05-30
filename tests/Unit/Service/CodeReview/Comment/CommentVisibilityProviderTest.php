<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Comment;

use DR\Review\Entity\Review\CommentVisibility;
use DR\Review\Security\SessionKeys;
use DR\Review\Service\CodeReview\Comment\CommentVisibilityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[CoversClass(CommentVisibilityProvider::class)]
class CommentVisibilityProviderTest extends AbstractTestCase
{
    private RequestStack                $requestStack;
    private Request                     $request;
    private SessionInterface&MockObject $session;
    private CommentVisibilityProvider   $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requestStack = new RequestStack();
        $this->session      = $this->createMock(SessionInterface::class);
        $this->request      = new Request();
        $this->request->setSession($this->session);
        $this->provider = new CommentVisibilityProvider($this->requestStack);
    }

    public function testGetCommentVisibilityDefault(): void
    {
        static::assertSame(CommentVisibility::ALL, $this->provider->getCommentVisibility());
    }

    public function testGetCommentVisibilityRequestWithoutSessionValue(): void
    {
        $this->requestStack->push($this->request);
        static::assertSame(CommentVisibility::ALL, $this->provider->getCommentVisibility());
    }

    public function testGetCommentVisibility(): void
    {
        $this->session->expects($this->once())
            ->method('get')
            ->with(SessionKeys::REVIEW_COMMENT_VISIBILITY->value)
            ->willReturn(CommentVisibility::UNRESOLVED->value);

        $this->requestStack->push($this->request);
        static::assertSame(CommentVisibility::UNRESOLVED, $this->provider->getCommentVisibility());
    }
}
