<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeHighlight;

use DR\Review\Service\CodeHighlight\HighlightedFilePreprocessor;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HighlightedFilePreprocessor::class)]
class HighlightedFilePreprocessorTest extends AbstractTestCase
{
    private HighlightedFilePreprocessor $preprocessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preprocessor = new HighlightedFilePreprocessor();
    }

    public function testProcessShouldSkipOtherLanguages(): void
    {
        static::assertSame('test <test[]>test', $this->preprocessor->process('foo', 'test <test[]>test'));
    }

    public function testProcessShouldRemoveTypescriptGenerics(): void
    {
        static::assertSame('test test', $this->preprocessor->process('typescript', 'test <test[]>test'));
    }
}
