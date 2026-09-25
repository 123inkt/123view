<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Review\Setting;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Request\Review\Setting\DiffVisibleLinesRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<DiffVisibleLinesRequest>
 */
#[CoversClass(DiffVisibleLinesRequest::class)]
class DiffVisibleLinesRequestTest extends AbstractRequestTestCase
{
    public function testGetVisibleLines(): void
    {
        $this->request->request->set('visibleLines', '10');
        static::assertSame(10, $this->validatedRequest->getVisibleLines());
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(['request' => ['visibleLines' => 'required|int|min:0|max:20']]);
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return DiffVisibleLinesRequest::class;
    }
}
