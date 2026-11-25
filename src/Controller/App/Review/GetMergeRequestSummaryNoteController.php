<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\SummaryNote\MergeRequestSummaryNoteService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

class GetMergeRequestSummaryNoteController extends AbstractController
{
    public function __construct(private readonly MergeRequestSummaryNoteService $summaryNoteService)
    {
    }

    /**
     * @throws Throwable
     */
    #[Route('app/reviews/{id<\d+>}/summary-note', name: self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(#[MapEntity] CodeReview $review): Response
    {
        $summaryNote = $this->summaryNoteService->getSummaryNote($review);
        if ($summaryNote === null) {
            return new Response(status: Response::HTTP_NO_CONTENT, headers: ['Cache-Control' => 'no-store']);
        }

        return new JsonResponse(
            [
                'status' => $summaryNote->status->value,
                'title'  => $summaryNote->title,
                'html'   => $summaryNote->html,
                'url'    => $summaryNote->url,
            ],
            headers: ['Cache-Control' => 'no-store']
        );
    }
}
