<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Revision;

use DR\Review\Entity\Review\Revision;
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

        $form      = $this->createMock(FormView::class);
        $viewModel = new ReviewRevisionViewModel([$revision], $form);

        static::assertNull($viewModel->getRevision('1'));
        static::assertSame($revision, $viewModel->getRevision('123'));
    }
}
