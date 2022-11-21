<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeHighlight;

use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Model\Review\Highlight\HighlightedFile;
use DR\GitCommitNotification\Service\CodeHighlight\ExtensionToLanguageTranslator;
use DR\GitCommitNotification\Service\CodeHighlight\HighlightedFileService;
use DR\GitCommitNotification\Service\CodeHighlight\HighlightHtmlLineSplitter;
use DR\GitCommitNotification\Service\Git\Show\GitShowService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;
use Highlight\Highlighter;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CodeHighlight\HighlightedFileService
 * @covers ::__construct
 */
class HighlightedFileServiceTest extends AbstractTestCase
{
    private GitShowService&MockObject                $showService;
    private ExtensionToLanguageTranslator&MockObject $translator;
    private Highlighter&MockObject                   $highlighter;
    private HighlightHtmlLineSplitter&MockObject     $splitter;
    private HighlightedFileService                   $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->showService = $this->createMock(GitShowService::class);
        $this->translator  = $this->createMock(ExtensionToLanguageTranslator::class);
        $this->highlighter = $this->createMock(Highlighter::class);
        $this->splitter    = $this->createMock(HighlightHtmlLineSplitter::class);
        $this->service     = new HighlightedFileService($this->showService, $this->translator, $this->highlighter, $this->splitter);
    }

    /**
     * @covers ::fromRevision
     * @throws Exception
     */
    public function testGetHighlightedFileUnknownLanguage(): void
    {
        $revision = new Revision();
        $filePath = '/path/to/file';

        $this->showService->expects(self::once())->method('getFileAtRevision')->with($revision, $filePath)->willReturn("highlighted\ndata");
        $this->translator->expects(self::once())->method('translate')->with('')->willReturn(null);

        $this->highlighter->expects(self::never())->method('highlight');
        $this->splitter->expects(self::never())->method('split');

        static::assertSame(["highlighted", "data"], $this->service->fromRevision($revision, $filePath)->lines);
    }

    /**
     * @covers ::fromRevision
     * @throws Exception
     */
    public function testGetHighlightedFile(): void
    {
        $revision = new Revision();
        $filePath = '/path/to/file.xml';

        $result        = new stdClass();
        $result->value = 'highlighted-data';

        $this->showService->expects(self::once())->method('getFileAtRevision')->with($revision, $filePath)->willReturn('file-data');
        $this->translator->expects(self::once())->method('translate')->with('xml')->willReturn('xml');
        $this->highlighter->expects(self::once())->method('highlight')->with('xml', 'file-data')->willReturn($result);
        $this->splitter->expects(self::once())->method('split')->with('highlighted-data')->willReturn(['highlighted', 'data']);

        $actual   = $this->service->fromRevision($revision, $filePath);
        $expected = new HighlightedFile($filePath, ['highlighted', 'data']);
        static::assertEquals($expected, $actual);
    }
}
