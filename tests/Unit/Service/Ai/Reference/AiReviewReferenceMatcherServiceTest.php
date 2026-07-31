<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Reference;

use DR\Review\Entity\Ai\AiReviewReference;
use DR\Review\Entity\Ai\AiReviewReferenceMatcher;
use DR\Review\Entity\Ai\AiReviewReferenceSection;
use DR\Review\Repository\Ai\AiReviewReferenceRepository;
use DR\Review\Service\Ai\Reference\AiReviewReferenceMatcherService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(AiReviewReferenceMatcherService::class)]
class AiReviewReferenceMatcherServiceTest extends AbstractTestCase
{
    private AiReviewReferenceRepository&MockObject $referenceRepository;
    private AiReviewReferenceMatcherService         $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->referenceRepository = $this->createMock(AiReviewReferenceRepository::class);
        $this->service              = new AiReviewReferenceMatcherService($this->referenceRepository);
    }

    private function createReference(string $pattern, ?string $codeMarker = null): AiReviewReference
    {
        $reference = new AiReviewReference();
        $section   = new AiReviewReferenceSection()->setHeading('Heading')->setContent('Content');
        $reference->addSection($section);

        $matcher = new AiReviewReferenceMatcher()->setFilePattern($pattern)->setCodeMarker($codeMarker);
        $reference->addMatcher($matcher);

        return $reference;
    }

    public function testGetApplicableSectionsShouldMatchSimpleExtensionGlob(): void
    {
        $reference = $this->createReference('*.php');
        $this->referenceRepository->expects($this->exactly(2))->method('findAllEnabled')->willReturn([$reference]);

        static::assertCount(1, $this->service->getApplicableSections('src/Foo.php'));
        static::assertCount(0, $this->service->getApplicableSections('src/Foo.ts'));
    }

    public function testGetApplicableSectionsShouldMatchDoubleStarAcrossDirectories(): void
    {
        $reference = $this->createReference('src/Entity/**/*.php');
        $this->referenceRepository->expects($this->exactly(2))->method('findAllEnabled')->willReturn([$reference]);

        static::assertCount(1, $this->service->getApplicableSections('src/Entity/Review/CodeReview.php'));
        static::assertCount(0, $this->service->getApplicableSections('src/Service/Foo.php'));
    }

    public function testGetApplicableSectionsShouldRequireCodeMarkerWhenConfigured(): void
    {
        $reference = $this->createReference('*.php', 'extends');
        $this->referenceRepository->expects($this->exactly(2))->method('findAllEnabled')->willReturn([$reference]);

        static::assertCount(1, $this->service->getApplicableSections('src/Foo.php', 'class Foo extends Bar {}'));
        static::assertCount(0, $this->service->getApplicableSections('src/Foo.php', 'class Foo {}'));
    }

    public function testGetApplicableSectionsShouldReturnEmptyWhenReferenceHasNoMatchers(): void
    {
        $reference = new AiReviewReference();
        $reference->addSection(new AiReviewReferenceSection()->setHeading('Heading')->setContent('Content'));
        $this->referenceRepository->expects($this->once())->method('findAllEnabled')->willReturn([$reference]);

        static::assertCount(0, $this->service->getApplicableSections('src/Foo.php'));
    }
}
