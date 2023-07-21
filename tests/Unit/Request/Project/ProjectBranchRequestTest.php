<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Project;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Request\Project\ProjectBranchRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<ProjectBranchRequest>
 */
#[CoversClass(ProjectBranchRequest::class)]
class ProjectBranchRequestTest extends AbstractRequestTestCase
{
    public function testGetSearchQuery(): void
    {
        static::assertNull($this->validatedRequest->getSearchQuery());

        $this->request->query->set('search', ' search ');
        static::assertSame('search', $this->validatedRequest->getSearchQuery());
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(
            [
                'query' => [
                    'search' => 'string'
                ]
            ]
        );
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return ProjectBranchRequest::class;
    }
}
