<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\GetMergeRequestSummaryNoteController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Model\SummaryNote\MergeRequestSummaryNote;
use DR\Review\Model\SummaryNote\SummaryNoteStatus;
use DR\Review\Service\SummaryNote\MergeRequestSummaryNoteService;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @extends AbstractControllerTestCase<GetMergeRequestSummaryNoteController>
 */
#[CoversClass(GetMergeRequestSummaryNoteController::class)]
class GetMergeRequestSummaryNoteControllerTest extends AbstractControllerTestCase
{
    private MergeRequestSummaryNoteService&MockObject $summaryNoteService;

    protected function setUp(): void
    {
        $this->summaryNoteService = $this->createMock(MergeRequestSummaryNoteService::class);
        parent::setUp();
    }

    /**
     * @throws Throwable
     */
    public function testInvokeWithoutSummaryNote(): void
    {
        $review = new CodeReview();
        $this->summaryNoteService->expects($this->once())->method('getSummaryNote')->with($review)->willReturn(null);

        $expected = new Response(status: Response::HTTP_NO_CONTENT, headers: ['Cache-Control' => 'no-store']);
        static::assertEquals($expected, ($this->controller)($review));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeWithSummaryNote(): void
    {
        $review  = new CodeReview();
        $summaryNote = new MergeRequestSummaryNote(SummaryNoteStatus::Warning, '2 new findings', '<p>html</p>', 'https://gitlab/mr#note_1');
        $this->summaryNoteService->expects($this->once())->method('getSummaryNote')->with($review)->willReturn($summaryNote);

        $expected = new JsonResponse(
            ['status' => 'warning', 'title' => '2 new findings', 'html' => '<p>html</p>', 'url' => 'https://gitlab/mr#note_1'],
            headers: ['Cache-Control' => 'no-store']
        );
        static::assertEquals($expected, ($this->controller)($review));
    }

    public function getController(): AbstractController
    {
        return new GetMergeRequestSummaryNoteController($this->summaryNoteService);
    }
}
