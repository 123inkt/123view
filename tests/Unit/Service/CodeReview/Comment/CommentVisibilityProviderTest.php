<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Comment;

use DR\Review\Entity\Review\CommentVisibility;
use DR\Review\Security\SessionKeys;
use DR\Review\Service\CodeReview\Comment\CommentVisibilityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

#[CoversClass(CommentVisibilityProvider::class)]
class CommentVisibilityProviderTest extends AbstractTestCase
{
    private RequestStack              $requestStack;
    private Request                   $request;
    private Session                   $session;
    private CommentVisibilityProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requestStack = new RequestStack();
        $this->session      = new Session();
        $this->request      = new Request();
        $this->request->setSession($this->session);
        $this->provider = new CommentVisibilityProvider($this->requestStack);
    }

    public function testGetCommentVisibilityDefault(): void
    {
        static::assertSame(CommentVisibility::ALL->value, $this->provider->getCommentVisibility()->value);
    }

    public function testGetCommentVisibilityRequestWithoutSessionValue(): void
    {
        $this->requestStack->push($this->request);
        static::assertSame(CommentVisibility::ALL->value, $this->provider->getCommentVisibility()->value);
    }

    public function testGetCommentVisibility(): void
    {
        $this->session->set(SessionKeys::REVIEW_COMMENT_VISIBILITY->value, CommentVisibility::UNRESOLVED->value);
        $this->requestStack->push($this->request);
        static::assertSame(CommentVisibility::UNRESOLVED->value, $this->provider->getCommentVisibility()->value);
    }
}
