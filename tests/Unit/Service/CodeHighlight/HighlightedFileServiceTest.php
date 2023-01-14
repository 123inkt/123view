<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeHighlight;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Model\Review\Highlight\HighlightedFile;
use DR\Review\Service\CodeHighlight\FilenameToLanguageTranslator;
use DR\Review\Service\CodeHighlight\HighlightedFileService;
use DR\Review\Service\CodeHighlight\HighlightHtmlLineSplitter;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Utility\Assert;
use Exception;
use Highlight\Highlighter;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

/**
 * @coversDefaultClass \DR\Review\Service\CodeHighlight\HighlightedFileService
 * @covers ::__construct
 */
class HighlightedFileServiceTest extends AbstractTestCase
{
    private FilenameToLanguageTranslator&MockObject $translator;
    private Highlighter&MockObject                  $highlighter;
    private HighlightHtmlLineSplitter&MockObject    $splitter;
    private HighlightedFileService                  $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->translator  = $this->createMock(FilenameToLanguageTranslator::class);
        $this->highlighter = $this->createMock(Highlighter::class);
        $this->splitter    = $this->createMock(HighlightHtmlLineSplitter::class);
        $this->service     = new HighlightedFileService($this->translator, $this->highlighter, $this->splitter);
    }

    /**
     * @covers ::fromDiffFile
     * @throws Exception
     */
    public function testGetHighlightedFileUnknownLanguage(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = '';

        $this->translator->expects(self::once())->method('translate')->with('')->willReturn(null);

        $this->highlighter->expects(self::never())->method('highlight');
        $this->splitter->expects(self::never())->method('split');

        static::assertNull($this->service->fromDiffFile($file));
    }

    /**
     * @covers ::fromDiffFile
     * @throws Exception
     */
    public function testGetHighlightedFile(): void
    {
        $lineA               = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'file-data')]);
        $block               = new DiffBlock();
        $block->lines        = [$lineA];
        $file                = new DiffFile();
        $file->filePathAfter = '/path/to/file.xml';
        $file->addBlock($block);

        $result        = new stdClass();
        $result->value = 'highlighted-data';

        $this->translator->expects(self::once())->method('translate')->with('/path/to/file.xml')->willReturn('xml');

        $this->highlighter->expects(self::once())->method('highlight')->with('xml', 'file-data')->willReturn($result);
        $this->splitter->expects(self::once())->method('split')->with('highlighted-data')->willReturn(['highlighted', 'data']);

        $actual   = $this->service->fromDiffFile($file);
        $expected = new HighlightedFile(Assert::isString($file->filePathAfter), ['highlighted', 'data']);
        static::assertEquals($expected, $actual);
    }
}
