<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Tool\Agent;

use DR\Review\Entity\Ai\AiReviewReferenceSection;
use DR\Review\Repository\Ai\AiReviewReferenceSectionRepository;
use DR\Review\Service\Ai\Reference\AiReviewReferenceBudgetTracker;
use DR\Review\Service\Ai\Tool\Agent\AiReviewReadReferenceSectionTool;
use DR\Review\Tests\AbstractTestCase;
use Mcp\Exception\ToolCallException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(AiReviewReadReferenceSectionTool::class)]
class AiReviewReadReferenceSectionToolTest extends AbstractTestCase
{
    private AiReviewReferenceSectionRepository&MockObject $sectionRepository;
    private AiReviewReferenceBudgetTracker                 $budgetTracker;
    private AiReviewReadReferenceSectionTool               $tool;

    public function setUp(): void
    {
        parent::setUp();
        $this->sectionRepository = $this->createMock(AiReviewReferenceSectionRepository::class);
        $this->budgetTracker      = new AiReviewReferenceBudgetTracker();
        $this->tool               = new AiReviewReadReferenceSectionTool($this->logger, $this->sectionRepository, $this->budgetTracker);
    }

    public function testInvokeShouldThrowWhenSectionNotFound(): void
    {
        $this->sectionRepository->expects($this->once())->method('find')->with(99)->willReturn(null);

        $this->expectException(ToolCallException::class);
        ($this->tool)(1, 'src/Foo.php', 99);
    }

    public function testInvokeShouldReturnFullContentWithinBudget(): void
    {
        $section = new AiReviewReferenceSection()->setHeading('h')->setContent('some content');
        $this->sectionRepository->expects($this->once())->method('find')->willReturn($section);
        $this->budgetTracker->startSession('1:src/Foo.php', 1000);

        $result = ($this->tool)(1, 'src/Foo.php', 5);

        static::assertSame('some content', $result);
        static::assertSame(1000 - strlen('some content'), $this->budgetTracker->remaining('1:src/Foo.php'));
    }

    public function testInvokeShouldTruncateWhenExceedingRemainingBudget(): void
    {
        $section = new AiReviewReferenceSection()->setHeading('h')->setContent(str_repeat('x', 100));
        $this->sectionRepository->expects($this->once())->method('find')->willReturn($section);
        $this->budgetTracker->startSession('1:src/Foo.php', 10);

        $result = ($this->tool)(1, 'src/Foo.php', 5);

        static::assertStringContainsString('truncated', $result);
        static::assertSame(0, $this->budgetTracker->remaining('1:src/Foo.php'));
    }

    public function testInvokeShouldRefuseWhenBudgetAlreadyExhausted(): void
    {
        $section = new AiReviewReferenceSection()->setHeading('h')->setContent('content');
        $this->sectionRepository->expects($this->once())->method('find')->willReturn($section);
        $this->budgetTracker->startSession('1:src/Foo.php', 0);

        $result = ($this->tool)(1, 'src/Foo.php', 5);

        static::assertSame('Reference budget exhausted for this file review; no further reference sections can be read.', $result);
    }

    public function testInvokeShouldTruncateContentExceedingMaxSectionLength(): void
    {
        $section = new AiReviewReferenceSection()->setHeading('h')->setContent(str_repeat('x', AiReviewReferenceSection::MAX_CONTENT_LENGTH + 10));
        $this->sectionRepository->expects($this->once())->method('find')->willReturn($section);
        $this->budgetTracker->startSession('1:src/Foo.php', AiReviewReferenceBudgetTracker::DEFAULT_BUDGET);

        $result = ($this->tool)(1, 'src/Foo.php', 5);

        static::assertLessThanOrEqual(AiReviewReferenceSection::MAX_CONTENT_LENGTH + 100, strlen($result));
        static::assertStringContainsString('truncated', $result);
    }
}
