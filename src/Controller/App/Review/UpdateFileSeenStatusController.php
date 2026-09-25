<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Request\Review\FileSeenStatusRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\CodeReview\FileSeenStatusService;
use DR\Review\Service\CodeReview\UserReviewSettingsProvider;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

class UpdateFileSeenStatusController extends AbstractController
{
    public function __construct(
        private readonly FileSeenStatusService $fileSeenStatusService,
        private readonly ReviewDiffServiceInterface $diffService,
        private readonly UserReviewSettingsProvider $settingsProvider,
        private readonly CodeReviewRevisionService $revisionService,
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
        $options    = new FileDiffOptions(FileDiffOptions::DEFAULT_LINE_DIFF, $this->settingsProvider->getComparisonPolicy());

        if ($review->getType() === CodeReviewType::BRANCH) {
            $files = $this->diffService->getDiffForBranch($review, [], (string)$review->getReferenceId(), $options);
        } else {
            $files = $this->diffService->getDiffForRevisions($review->getRepository(), $this->revisionService->getRevisions($review), $options);
        }

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
