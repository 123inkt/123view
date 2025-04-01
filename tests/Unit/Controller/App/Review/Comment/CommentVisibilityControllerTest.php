<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\CommentVisibilityController;
use DR\Review\Entity\Review\CommentVisibility;
use DR\Review\Request\Comment\CommentVisibilityRequest;
use DR\Review\Security\SessionKeys;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @extends AbstractControllerTestCase<CommentVisibilityController>
 */
#[CoversClass(CommentVisibilityController::class)]
class CommentVisibilityControllerTest extends AbstractControllerTestCase
{
    public function testInvoke(): void
    {
        $validatedRequest = $this->createMock(CommentVisibilityRequest::class);
        $session          = $this->createMock(SessionInterface::class);
        $request          = new Request();
        $request->setSession($session);

        $validatedRequest->expects(self::once())->method('getRequest')->willReturn($request);
        $validatedRequest->expects(self::once())->method('getVisibility')->willReturn(CommentVisibility::NONE);
        $session->expects(self::once())->method('set')->with(SessionKeys::REVIEW_COMMENT_VISIBILITY->value, CommentVisibility::NONE->value);

        /** @var JsonResponse $response */
        $response = ($this->controller)($validatedRequest);
        static::assertSame('"ok"', $response->getContent());
    }

    public function getController(): AbstractController
    {
        return new CommentVisibilityController();
    }
}
