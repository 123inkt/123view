<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Review;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Model\Review\Action\AbstractReviewAction;
use DR\Review\Request\Review\ReviewRequest;
use DR\Review\Security\SessionKeys;
use DR\Review\Service\CodeReview\Activity\CodeReviewActionFactory;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @extends AbstractRequestTestCase<ReviewRequest>
 * @coversDefaultClass \DR\Review\Request\Review\ReviewRequest
 * @covers ::__construct
 */
class ReviewRequestTest extends AbstractRequestTestCase
{
    private CodeReviewActionFactory&MockObject $actionFactory;

    public function setUp(): void
    {
        $this->actionFactory = $this->createMock(CodeReviewActionFactory::class);
        parent::setUp();
    }

    /**
     * @covers ::getFilePath
     */
    public function testGetFilePath(): void
    {
        $this->request->query->set('filePath', 'foobar');
        static::assertSame('foobar', $this->validatedRequest->getFilePath());
    }

    /**
     * @covers ::getTab
     */
    public function testGetTab(): void
    {
        $this->request->query->set('tab', 'revisions');
        static::assertSame('revisions', $this->validatedRequest->getTab());
    }

    /**
     * @covers ::getAction
     */
    public function testGetAction(): void
    {
        $action = $this->createMock(AbstractReviewAction::class);
        $this->actionFactory->expects(self::once())->method('createFromRequest')->with($this->request)->willReturn($action);

        $this->request->query->set('action', 'my-action');
        static::assertSame($action, $this->validatedRequest->getAction());
    }

    /**
     * @covers ::getDiffMode
     */
    public function testGetDiffMode(): void
    {
        $session = $this->createMock(Session::class);
        $this->request->setSession($session);

        static::assertSame(ReviewDiffModeEnum::INLINE, $this->validatedRequest->getDiffMode());

        $this->request->query->set('diff', 'unified');
        static::assertSame(ReviewDiffModeEnum::UNIFIED, $this->validatedRequest->getDiffMode());
    }

    /**
     * @covers ::getComparisonPolicy
     */
    public function testGetComparePolicyFromSession(): void
    {
        $session = $this->createMock(Session::class);
        $this->request->setSession($session);

        $session->expects(self::once())
            ->method('get')
            ->with(SessionKeys::DIFF_COMPARISON_POLICY->value)
            ->willReturn(DiffComparePolicy::TRIM->value);
        $session->expects(self::once())
            ->method('set')
            ->with(SessionKeys::DIFF_COMPARISON_POLICY->value, DiffComparePolicy::TRIM->value);

        static::assertSame(DiffComparePolicy::TRIM, $this->validatedRequest->getComparisonPolicy());
    }

    /**
     * @covers ::getDiffMode
     */
    public function testGetDiffModeFromSession(): void
    {
        $session = $this->createMock(Session::class);
        $this->request->setSession($session);

        $session->expects(self::once())
            ->method('get')
            ->with(SessionKeys::REVIEW_DIFF_MODE->value)
            ->willReturn(ReviewDiffModeEnum::SIDE_BY_SIDE->value);
        $session->expects(self::once())
            ->method('set')
            ->with(SessionKeys::REVIEW_DIFF_MODE->value, ReviewDiffModeEnum::SIDE_BY_SIDE->value);

        static::assertSame(ReviewDiffModeEnum::SIDE_BY_SIDE, $this->validatedRequest->getDiffMode());
    }

    /**
     * @covers ::getValidationRules
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(
            [
                'query' => [
                    'filePath'         => 'string|filled',
                    'tab'              => 'string|in:revisions,overview',
                    'diff'             => 'string|in:side-by-side,unified,inline',
                    'action'           => 'string',
                    'comparisonPolicy' => 'string|in:all,trim,ignore'
                ]
            ]
        );
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return ReviewRequest::class;
    }

    /**
     * @inheritDoc
     */
    protected function getConstructorArguments(): array
    {
        return [$this->actionFactory];
    }
}
