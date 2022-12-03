<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\ViewModel\App\Revision;

use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\App\Revision\ReviewRevisionViewModel;
use Symfony\Component\Form\FormView;

/**
 * @coversDefaultClass \DR\GitCommitNotification\ViewModel\App\Revision\ReviewRevisionViewModel
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
