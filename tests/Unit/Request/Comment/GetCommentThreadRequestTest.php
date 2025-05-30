<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Model\Review\Action\AbstractReviewAction;
use DR\Review\Request\Comment\GetCommentThreadRequest;
use DR\Review\Service\CodeReview\Activity\CodeReviewActionFactory;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractRequestTestCase<GetCommentThreadRequest>
 */
#[CoversClass(GetCommentThreadRequest::class)]
class GetCommentThreadRequestTest extends AbstractRequestTestCase
{
    private CodeReviewActionFactory&MockObject $actionFactory;

    public function setUp(): void
    {
        $this->actionFactory = $this->createMock(CodeReviewActionFactory::class);
        parent::setUp();
    }

    public function testGetAction(): void
    {
        $action = $this->createMock(AbstractReviewAction::class);
        $this->actionFactory->expects($this->once())->method('createFromRequest')->with($this->request)->willReturn($action);

        $this->request->query->set('action', 'my-action');
        static::assertSame($action, $this->validatedRequest->getAction());
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(['query' => ['action' => 'string']]);
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return GetCommentThreadRequest::class;
    }

    /**
     * @inheritDoc
     */
    protected function getConstructorArguments(): array
    {
        return [$this->actionFactory];
    }
}
