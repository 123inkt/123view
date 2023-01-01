<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Review;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Request\Review\ReviewRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModel\App\Review\ReviewViewModel;

/**
 * @extends AbstractRequestTestCase<ReviewRequest>
 * @coversDefaultClass \DR\Review\Request\Review\ReviewRequest
 * @covers ::__construct
 */
class ReviewRequestTest extends AbstractRequestTestCase
{
    /**
     * @covers ::getFilePath
     */
    public function testGetFilePath(): void
    {
        $this->request->query->set('filePath', 'foobar');
        static::assertSame('foobar', $this->validatedRequest->getFilePath());
    }

    /**
     * @covers ::getTab
     */
    public function testGetTab(): void
    {
        $this->request->query->set('tab', 'revisions');
        static::assertSame('revisions', $this->validatedRequest->getTab());
    }

    /**
     * @covers ::getDiffMode
     */
    public function testGetDiffMode(): void
    {
        static::assertSame(ReviewDiffModeEnum::INLINE, $this->validatedRequest->getDiffMode());

        $this->request->query->set('diff', 'unified');
        static::assertSame(ReviewDiffModeEnum::UNIFIED, $this->validatedRequest->getDiffMode());
    }

    /**
     * @covers ::getValidationRules
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(
            [
                'query' => [
                    'filePath' => 'string|filled',
                    'tab'      => 'string|in:' . ReviewViewModel::SIDEBAR_TAB_REVISIONS . ',' . ReviewViewModel::SIDEBAR_TAB_OVERVIEW,
                    'diff'     => 'string|in:unified,inline'
                ]
            ]
        );
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return ReviewRequest::class;
    }
}
