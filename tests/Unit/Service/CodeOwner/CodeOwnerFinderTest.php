<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeOwner;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\CodeOwner\OwnerPattern;
use DR\Review\Service\CodeOwner\CodeOwnerFileFinder;
use DR\Review\Service\CodeOwner\CodeOwnerFilepathMatcher;
use DR\Review\Service\CodeOwner\CodeOwnerFileParser;
use DR\Review\Service\CodeOwner\CodeOwnerFinder;
use DR\Review\Tests\AbstractTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeOwnerFinder::class)]
class CodeOwnerFinderTest extends AbstractTestCase
{
    private MockObject&CodeOwnerFileFinder      $fileFinder;
    private MockObject&CodeOwnerFileParser      $parser;
    private MockObject&CodeOwnerFilepathMatcher $matcher;
    private CodeOwnerFinder                     $finder;
    private Repository                          $repository;
    private vfsStreamDirectory                  $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileFinder = $this->createMock(CodeOwnerFileFinder::class);
        $this->parser     = $this->createMock(CodeOwnerFileParser::class);
        $this->matcher    = $this->createMock(CodeOwnerFilepathMatcher::class);
        $this->finder     = new CodeOwnerFinder($this->fileFinder, $this->parser, $this->matcher);
        $this->repository = new Repository();
        $this->root       = vfsStream::setup();
    }

    public function testFindReturnsEmptyArrayWhenNoFilesFound(): void
    {
        $this->fileFinder->expects($this->once())->method('find')->willReturn([]);
        $this->parser->expects($this->never())->method('parse');
        $this->matcher->expects($this->never())->method('match');

        static::assertSame([], $this->finder->find($this->repository, 'src/Foo.php'));
    }

    public function testFindReturnsOwnersAndReversesPatternOrder(): void
    {
        vfsStream::newFile('CODEOWNERS')->withContent('content')->at($this->root);
        $filePath = $this->root->url() . '/CODEOWNERS';

        $pattern1 = new OwnerPattern('*', ['@everyone']);
        $pattern2 = new OwnerPattern('src/', ['@backend']);

        $this->fileFinder->expects($this->once())->method('find')->willReturn([$filePath]);
        $this->parser->expects($this->once())->method('parse')->willReturn([$pattern1, $pattern2]);
        $this->matcher->expects($this->once())
            ->method('match')
            ->with('src/Foo.php', [$pattern2, $pattern1])
            ->willReturn($pattern2);

        static::assertSame(['@backend'], $this->finder->find($this->repository, 'src/Foo.php'));
    }

    public function testFindFallsBackToNextFileAndReturnsEmptyWhenNoMatchFound(): void
    {
        vfsStream::newFile('CODEOWNERS1')->withContent('content')->at($this->root);
        vfsStream::newFile('CODEOWNERS2')->withContent('content')->at($this->root);

        $this->fileFinder->expects($this->once())->method('find')
            ->willReturn([$this->root->url() . '/CODEOWNERS1', $this->root->url() . '/CODEOWNERS2']);
        $this->parser->expects($this->exactly(2))->method('parse')->willReturn([]);
        $this->matcher->expects($this->exactly(2))->method('match')->willReturn(null);

        static::assertSame([], $this->finder->find($this->repository, 'src/Foo.php'));
    }
}
