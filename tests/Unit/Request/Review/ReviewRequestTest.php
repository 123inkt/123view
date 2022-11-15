<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Request\Review;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\GitCommitNotification\Model\Review\Action\AbstractReviewAction;
use DR\GitCommitNotification\Request\Review\ReviewRequest;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewActionFactory;
use DR\GitCommitNotification\Tests\Unit\Request\AbstractRequestTestCase;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewViewModel;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractRequestTestCase<ReviewRequest>
 * @coversDefaultClass \DR\GitCommitNotification\Request\Review\ReviewRequest
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
     * @covers ::getTab
     */
    public function testGetAction(): void
    {
        $action = $this->createMock(AbstractReviewAction::class);
        $this->actionFactory->expects(self::once())->method('createFromRequest')->with($this->request)->willReturn($action);

        $this->request->query->set('action', 'my-action');
        static::assertSame($action, $this->validatedRequest->getAction());
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
                    'filePath' => 'string|filled',
                    'tab'      => 'string|in:' . ReviewViewModel::SIDEBAR_TAB_REVISIONS . ',' . ReviewViewModel::SIDEBAR_TAB_OVERVIEW,
                    'action'   => 'string'
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
