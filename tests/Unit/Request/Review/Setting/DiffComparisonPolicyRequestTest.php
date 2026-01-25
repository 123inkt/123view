<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Review\Setting;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Request\Review\Setting\DiffComparisonPolicyRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<DiffComparisonPolicyRequest>
 */
#[CoversClass(DiffComparisonPolicyRequest::class)]
class DiffComparisonPolicyRequestTest extends AbstractRequestTestCase
{
    public function testGetComparisonPolicy(): void
    {
        $this->request->request->set('comparisonPolicy', 'trim');
        static::assertSame(DiffComparePolicy::TRIM, $this->validatedRequest->getComparisonPolicy());
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(['request' => ['comparisonPolicy' => 'required|string|in:all,trim,ignore,ignore_empty_lines']]);
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return DiffComparisonPolicyRequest::class;
    }
}
