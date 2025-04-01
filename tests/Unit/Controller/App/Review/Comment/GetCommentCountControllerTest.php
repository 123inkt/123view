<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\GetCommentCountController;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Utils\Assert;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @extends AbstractControllerTestCase<GetCommentCountController>
 */
#[CoversClass(GetCommentCountController::class)]
class GetCommentCountControllerTest extends AbstractControllerTestCase
{
    /**
     * @throws JsonException
     */
    public function testInvoke(): void
    {
        $commentA = (new Comment())->setState(CommentStateType::OPEN);
        $commentB = (new Comment())->setState(CommentStateType::RESOLVED);
        $review   = new CodeReview();
        $review->getComments()->add($commentA);
        $review->getComments()->add($commentB);

        /** @var JsonResponse $response */
        $response = ($this->controller)($review);
        static::assertSame(['total' => 2, 'open' => 1, 'resolved' => 1], Json::decode(Assert::notFalse($response->getContent()), true));
    }

    public function getController(): AbstractController
    {
        return new GetCommentCountController();
    }
}
