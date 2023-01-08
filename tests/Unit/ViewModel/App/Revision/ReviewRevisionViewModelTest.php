<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Revision;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Revision\ReviewRevisionViewModel;
use Symfony\Component\Form\FormView;

/**
 * @coversDefaultClass \DR\Review\ViewModel\App\Revision\ReviewRevisionViewModel
 * @covers ::__construct
 */
class ReviewRevisionViewModelTest extends AbstractTestCase
{
    /**
     * @covers ::getRevision
     */
    public function testGetRevision(): void
    {
        $revision = new Revision();
        $revision->setId(123);

        $formA     = $this->createMock(FormView::class);
        $formB     = $this->createMock(FormView::class);
        $viewModel = new ReviewRevisionViewModel([$revision], $formA, $formB);

        static::assertNull($viewModel->getRevision('1'));
        static::assertSame($revision, $viewModel->getRevision('123'));
    }
}
