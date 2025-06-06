<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider;

use ArrayIterator;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Model\Review\CodeReviewDto;
use DR\Review\Request\Review\ReviewRequest;
use DR\Review\Service\CodeReview\CodeReviewDtoProvider;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Review\ViewModelProvider\Appender\Review\ReviewViewModelAppenderInterface;
use DR\Review\ViewModelProvider\ReviewViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(ReviewViewModelProvider::class)]
class ReviewViewModelProviderTest extends AbstractTestCase
{
    private CodeReviewDtoProvider&MockObject            $reviewDtoProvider;
    private ReviewViewModelAppenderInterface&MockObject $viewModelAppender;
    private ReviewViewModelProvider                     $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewDtoProvider = $this->createMock(CodeReviewDtoProvider::class);
        $this->viewModelAppender = $this->createMock(ReviewViewModelAppenderInterface::class);
        $this->provider          = new ReviewViewModelProvider($this->reviewDtoProvider, new ArrayIterator([$this->viewModelAppender]));
    }

    /**
     * @throws Throwable
     */
    public function testGetViewModel(): void
    {
        $review  = $this->createMock(CodeReview::class);
        $request = $this->createMock(ReviewRequest::class);
        $request->method('getTab')->willReturn('tab');
        $dto = $this->createDto();

        $this->reviewDtoProvider->expects(self::once())->method('provide')->with($review, $request)->willReturn($dto);
        $this->viewModelAppender->expects(self::once())->method('accepts')->with($dto)->willReturn(true);
        $this->viewModelAppender->expects(self::once())->method('append')->with($dto);

        $expected = new ReviewViewModel($review, $dto->revisions, 'tab', 1);
        $actual   = $this->provider->getViewModel($review, $request);
        static::assertEquals($expected, $actual);
    }

    private function createDto(): CodeReviewDto
    {
        $revision = new Revision();

        return new CodeReviewDto(
            new CodeReview(),
            [$revision],
            [$revision],
            new DirectoryTreeNode('name'),
            null,
            'string',
            'tab',
            DiffComparePolicy::ALL,
            ReviewDiffModeEnum::INLINE,
            null
        );
    }
}
