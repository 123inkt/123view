<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Revision;

use DR\Review\Controller\App\Revision\AttachRevisionSelectionController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Revision\AttachRevisionsViewModel;
use DR\Review\ViewModel\App\Revision\RevisionsViewModel;
use DR\Review\ViewModelProvider\RevisionViewModelProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Revision\AttachRevisionSelectionController
 * @covers ::__construct
 */
class AttachRevisionSelectionControllerTest extends AbstractTestCase
{
    private RevisionViewModelProvider&MockObject $viewModelProvider;
    private AttachRevisionSelectionController    $controller;

    public function setUp(): void
    {
        parent::setUp();
        $this->viewModelProvider = $this->createMock(RevisionViewModelProvider::class);
        $this->controller        = new AttachRevisionSelectionController($this->viewModelProvider);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $request    = new Request(['search' => 'search', 'page' => 123]);
        $repository = new Repository();
        $review     = new CodeReview();
        $review->setRepository($repository);
        $viewModel = $this->createMock(RevisionsViewModel::class);

        $this->viewModelProvider->expects(self::once())
            ->method('getRevisionsViewModel')
            ->with($repository, 123, 'search', false)
            ->willReturn($viewModel);

        $result = ($this->controller)($request, $review);
        static::assertEquals(
            ['attachRevisionsModel' => new AttachRevisionsViewModel($review, $viewModel)],
            $result
        );
    }
}
