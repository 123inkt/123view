<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Request\Review;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Request\Review\ChangeReviewerStateRequest;
use DR\GitCommitNotification\Tests\Unit\Request\AbstractRequestTestCase;

/**
 * @extends AbstractRequestTestCase<ChangeReviewerStateRequest>
 * @coversDefaultClass \DR\GitCommitNotification\Request\Review\ChangeReviewerStateRequest
 */
class ChangeReviewerStateRequestTest extends AbstractRequestTestCase
{
    /**
     * @covers ::getState
     */
    public function testGetState(): void
    {
        $this->request->request->set('state', 'foobar');
        static::assertSame('foobar', $this->validatedRequest->getState());
    }

    /**
     * @covers ::getValidationRules
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(
            [
                'request' => ['state' => 'required|string|in:' . implode(',', CodeReviewerStateType::VALUES)]
            ]
        );
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return ChangeReviewerStateRequest::class;
    }
}
