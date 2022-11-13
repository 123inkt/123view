<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Request\Review;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewStateType;
use DR\GitCommitNotification\Request\Review\ChangeReviewStateRequest;
use DR\GitCommitNotification\Tests\Unit\Request\AbstractRequestTestCase;

/**
 * @extends AbstractRequestTestCase<ChangeReviewStateRequest>
 * @coversDefaultClass \DR\GitCommitNotification\Request\Review\ChangeReviewStateRequest
 */
class ChangeReviewStateRequestTest extends AbstractRequestTestCase
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
                'request' => ['state' => 'required|string|in:' . implode(',', CodeReviewStateType::VALUES)]
            ]
        );
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return ChangeReviewStateRequest::class;
    }
}
