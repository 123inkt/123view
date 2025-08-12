<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Activity;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Model\Review\ActivityVariable;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityVariableFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(CodeReviewActivityVariableFactory::class)]
class CodeReviewActivityVariableFactoryTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject  $urlGenerator;
    private CodeReviewActivityVariableFactory $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->service      = new CodeReviewActivityVariableFactory($this->urlGenerator);
    }

    public function testCreateFromComment(): void
    {
        $review = new CodeReview();
        $review->setId(456);

        $comment = new Comment();
        $comment->setId(123);
        $comment->setReview($review);
        $comment->setFilePath('filepath');

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(ReviewController::class, ['review' => $review, 'filePath' => 'filepath'])
            ->willReturn('https://url/');

        $activity = $this->service->createFromComment($comment);
        static::assertTrue($activity->htmlSafe);
        static::assertSame('file', $activity->key);
        static::assertSame('<a href="https://url/#comment-123">filepath</a>', $activity->value);
    }

    public function testCreateParams(): void
    {
        $variableA = new ActivityVariable('escape', 'foo & bar');
        $variableB = new ActivityVariable('not-escape', 'foo &amp; bar', true);

        $params = $this->service->createParams([$variableA, $variableB]);
        static::assertSame(['escape' => 'foo &amp; bar', 'not-escape' => 'foo &amp; bar'], $params);
    }
}
