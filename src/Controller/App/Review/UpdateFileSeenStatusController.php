<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Request\Review\FileSeenStatusRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Utility\Assert;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

class UpdateFileSeenStatusController extends AbstractController
{
    public function __construct(
        private readonly FileSeenStatusService $fileSeenStatusService,
        private readonly ReviewDiffServiceInterface $diffService
    ) {
    }

    /**
     * @throws Throwable
     */
    #[Route('app/reviews/{id<\d+>}/file-seen-status', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(FileSeenStatusRequest $request, #[MapEntity] CodeReview $review): Response
    {
        $filePath   = $request->getFilePath();
        $seenStatus = $request->getSeenStatus();
        $files      = $this->diffService->getDiffFiles(
            Assert::notNull($review->getRepository()),
            $review->getRevisions()->toArray(),
            new FileDiffOptions(0)
        );

        // find filepath in files
        foreach ($files as $diffFile) {
            if ($diffFile->filePathBefore !== $filePath && $diffFile->filePathAfter !== $filePath) {
                continue;
            }

            if ($seenStatus) {
                $this->fileSeenStatusService->markAsSeen($review, $this->getUser(), $diffFile);
            } else {
                $this->fileSeenStatusService->markAsUnseen($review, $this->getUser(), $diffFile);
            }

            return new Response(status: Response::HTTP_ACCEPTED);
        }

        return new Response(status: Response::HTTP_NOT_MODIFIED);
    }
}
