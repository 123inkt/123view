<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Report;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Request\Report\UploadCodeCoverageRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<UploadCodeCoverageRequest>
 */
#[CoversClass(UploadCodeCoverageRequest::class)]
class UploadCodeCoverageRequestTest extends AbstractRequestTestCase
{
    public function testGetBasePath(): void
    {
        $this->request->query->set('basePath', 'basePath');
        static::assertSame('basePath', $this->validatedRequest->getBasePath());
    }

    public function testGetBranchId(): void
    {
        $this->request->query->set('branchId', 'branchId');
        static::assertSame('branchId', $this->validatedRequest->getBranchId());
    }

    public function testGetFormat(): void
    {
        $this->request->query->set('format', 'gitlab');
        static::assertSame('gitlab', $this->validatedRequest->getFormat());
    }

    public function testGetContent(): void
    {
        static::assertSame('content', $this->validatedRequest->getData());
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(
            [
                'query' => [
                    'basePath' => 'string',
                    'branchId' => 'string|min:1|max:255',
                    'format'   => 'string|in:clover'
                ]
            ]
        );
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return UploadCodeCoverageRequest::class;
    }
}
