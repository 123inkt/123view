<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Request\Review;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\GitCommitNotification\Request\Review\FileSeenStatusRequest;
use DR\GitCommitNotification\Tests\Unit\Request\AbstractRequestTestCase;

/**
 * @extends AbstractRequestTestCase<FileSeenStatusRequest>
 * @coversDefaultClass \DR\GitCommitNotification\Request\Review\FileSeenStatusRequest
 */
class FileSeenStatusRequestTest extends AbstractRequestTestCase
{
    /**
     * @covers ::getFilePath
     */
    public function testGetFilePath(): void
    {
        $this->request->request->set('filePath', 'foobar');
        static::assertSame('foobar', $this->validatedRequest->getFilePath());
    }

    /**
     * @covers ::getSeenStatus
     */
    public function testGetSeenStatus(): void
    {
        $this->request->request->set('seen', '1');
        static::assertTrue($this->validatedRequest->getSeenStatus());
    }

    /**
     * @covers ::getValidationRules
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
