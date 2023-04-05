<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use Doctrine\ORM\NonUniqueResultException;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\CodeReview\CodeReviewCreationService;
use DR\Review\Service\CodeReview\CodeReviewFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeReviewCreationService::class)]
class CodeReviewCreationServiceTest extends AbstractTestCase
{
    private CodeReviewFactory&MockObject    $reviewFactory;
    private CodeReviewRepository&MockObject $reviewRepository;
    private CodeReviewCreationService       $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewFactory    = $this->createMock(CodeReviewFactory::class);
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->service          = new CodeReviewCreationService($this->reviewFactory, $this->reviewRepository);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function testCreateFromRevision(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $revision = new Revision();
        $revision->setId(456);
        $revision->setRepository($repository);

        $review = new CodeReview();

        $this->reviewFactory->expects(self::once())->method('createFromRevision')->with($revision, 'reference')->willReturn($review);
        $this->reviewRepository->expects(self::once())->method('getCreateProjectId')->with(123)->willReturn(789);

        $actualReview = $this->service->createFromRevision($revision, 'reference');
        static::assertSame($review, $actualReview);
        static::assertSame(789, $actualReview->getProjectId());
    }
}
