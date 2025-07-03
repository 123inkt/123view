<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Revision;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Revision\ReviewRevisionViewModel;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\FormView;

#[CoversClass(ReviewRevisionViewModel::class)]
class ReviewRevisionViewModelTest extends AbstractTestCase
{
    public function testGetRevision(): void
    {
        $revision = new Revision();
        $revision->setId(123);

        $formA     = $this->createMock(FormView::class);
        $formB     = $this->createMock(FormView::class);
        $viewModel = new ReviewRevisionViewModel([$revision], [], $formA, $formB);

        static::assertNull($viewModel->getRevision('1'));
        static::assertSame($revision, $viewModel->getRevision('123'));
    }
}
