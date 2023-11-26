<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Review;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Request\Review\FileSeenStatusRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<FileSeenStatusRequest>
 */
#[CoversClass(FileSeenStatusRequest::class)]
class FileSeenStatusRequestTest extends AbstractRequestTestCase
{
    public function testGetFilePath(): void
    {
        $this->request->request->set('filePath', 'foobar');
        static::assertSame('foobar', $this->validatedRequest->getFilePath());
    }

    public function testGetSeenStatus(): void
    {
        $this->request->request->set('seen', '1');
        static::assertTrue($this->validatedRequest->getSeenStatus());
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(
            [
                'request' => [
                    'filePath' => 'required|string|filled|max:500',
                    'seen'     => 'required|integer|between:0,1'
                ]
            ]
        );
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return FileSeenStatusRequest::class;
    }
}
