<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Review\Setting;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Request\Review\Setting\ReviewDiffModeRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<ReviewDiffModeRequest>
 */
#[CoversClass(ReviewDiffModeRequest::class)]
class ReviewDiffModeRequestTest extends AbstractRequestTestCase
{
    public function testGetDiffMode(): void
    {
        $this->request->request->set('diffMode', 'side-by-side');
        static::assertSame(ReviewDiffModeEnum::SIDE_BY_SIDE, $this->validatedRequest->getDiffMode());
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(['request' => ['diffMode' => 'required|string|in:side-by-side,unified,inline']]);
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return ReviewDiffModeRequest::class;
    }
}
