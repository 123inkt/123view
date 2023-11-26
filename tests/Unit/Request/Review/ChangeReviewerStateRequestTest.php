<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Review;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Request\Review\ChangeReviewerStateRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<ChangeReviewerStateRequest>
 */
#[CoversClass(ChangeReviewerStateRequest::class)]
class ChangeReviewerStateRequestTest extends AbstractRequestTestCase
{
    public function testGetState(): void
    {
        $this->request->request->set('state', 'foobar');
        static::assertSame('foobar', $this->validatedRequest->getState());
    }

    /**
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
