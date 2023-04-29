<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Report;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Request\Report\UploadCodeInspectionRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<UploadCodeInspectionRequest>
 */
#[CoversClass(UploadCodeInspectionRequest::class)]
class UploadCodeInspectionRequestTest extends AbstractRequestTestCase
{
    public function testGetIdentifier(): void
    {
        $this->request->query->set('identifier', 'identifier');
        static::assertSame('identifier', $this->validatedRequest->getIdentifier());
    }

    public function testGetBasePath(): void
    {
        $this->request->query->set('basePath', 'basePath');
        static::assertSame('basePath', $this->validatedRequest->getBasePath());
    }

    public function testGetFormat(): void
    {
        $this->request->query->set('format', 'gitlab');
        static::assertSame('gitlab', $this->validatedRequest->getFormat());
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(
            [
                'query' => [
                    'identifier' => 'required|string|min:1|max:50',
                    'basePath'   => 'string',
                    'format'     => 'string|in:checkstyle,github,gitlab'
                ]
            ]
        );
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return UploadCodeInspectionRequest::class;
    }
}
