<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Tool\Agent;

use DR\Review\Entity\Ai\AiReviewReference;
use DR\Review\Entity\Ai\AiReviewReferenceMatcher;
use DR\Review\Entity\Ai\AiReviewReferenceSection;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\Ai\Reference\AiReviewReferenceMatcherService;
use DR\Review\Service\Ai\Tool\Agent\AiReviewListReferencesTool;
use DR\Review\Service\CodeReview\CodeReviewDiffService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(AiReviewListReferencesTool::class)]
class AiReviewListReferencesToolTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject          $repository;
    private CodeReviewDiffService&MockObject         $diffService;
    private AiReviewReferenceMatcherService&MockObject $matcherService;
    private AiReviewListReferencesTool                $tool;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository     = $this->createMock(CodeReviewRepository::class);
        $this->diffService    = $this->createMock(CodeReviewDiffService::class);
        $this->matcherService = $this->createMock(AiReviewReferenceMatcherService::class);
        $this->tool           = new AiReviewListReferencesTool($this->logger, $this->repository, $this->diffService, $this->matcherService);
    }

    public function testInvokeShouldThrowWhenReviewNotFound(): void
    {
        $this->repository->expects($this->once())->method('find')->with(1)->willReturn(null);
        $this->diffService->expects($this->never())->method('getDiff');
        $this->matcherService->expects($this->never())->method('getApplicableSections');

        $this->expectException(CodeReviewNotFoundException::class);
        ($this->tool)(1, 'src/Foo.php');
    }

    public function testInvokeShouldReturnApplicableSectionsForMatchedDiffFile(): void
    {
        $review = new CodeReview()->setId(1);
        $file   = new DiffFile();
        $file->filePathAfter = 'src/Foo.php';
        $file->raw           = 'class Foo extends Bar {}';

        $reference = new AiReviewReference()->setName('PHP Rules');
        $section   = new AiReviewReferenceSection()->setId(42)->setHeading('Inheritance')->setContent('Some content');
        $reference->addSection($section);
        $reference->addMatcher(new AiReviewReferenceMatcher()->setFilePattern('*.php')->setCodeMarker('extends'));

        $this->repository->expects($this->once())->method('find')->with(1)->willReturn($review);
        $this->diffService->expects($this->once())->method('getDiff')->with($review)->willReturn([$file]);
        $this->matcherService->expects($this->once())->method('getApplicableSections')
            ->with('src/Foo.php', 'class Foo extends Bar {}')
            ->willReturn([$section]);

        $result = ($this->tool)(1, 'src/Foo.php');

        static::assertCount(1, $result);
        static::assertSame(42, $result[0]['sectionId']);
        static::assertSame('PHP Rules', $result[0]['reference']);
        static::assertSame('Inheritance', $result[0]['heading']);
        static::assertSame(strlen('Some content'), $result[0]['characters']);
    }
}
