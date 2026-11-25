<?php
declare(strict_types=1);

namespace DR\Review\Service\SummaryNote;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\ExternalTool\Gitlab\GitlabService;
use DR\Review\Model\Api\Gitlab\Note;
use DR\Review\Model\SummaryNote\MergeRequestSummaryNote;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Utils\Arrays;
use Throwable;

readonly class MergeRequestSummaryNoteService
{
    public function __construct(
        private string $gitlabApiUrl,
        private string $summaryNoteAuthor,
        private string $summaryNotePrefix,
        private GitlabService $gitlabService,
        private CodeReviewRevisionService $revisionService,
        private MergeRequestSummaryNoteFactory $summaryNoteFactory,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function getSummaryNote(CodeReview $review): ?MergeRequestSummaryNote
    {
        if ($this->summaryNoteAuthor === '' || $this->gitlabApiUrl === '') {
            return null;
        }

        $projectId = $review->getRepository()->getRepositoryProperty('gitlab-project-id');
        if ($projectId === null) {
            return null;
        }

        $remoteRef = $this->revisionService->findRemoteRef($review);
        if ($remoteRef === null) {
            return null;
        }

        $mergeRequest = $this->gitlabService->getMergeRequest((int)$projectId, $remoteRef);
        if ($mergeRequest === null) {
            return null;
        }

        $notes = $this->gitlabService->getMergeRequestNotes((int)$projectId, $mergeRequest['iid']);
        $note  = Arrays::findOrNull($notes, fn(Note $note) => $this->isSummaryNote($note));
        if ($note === null) {
            return null;
        }

        return $this->summaryNoteFactory->create($note, $mergeRequest['web_url']);
    }

    private function isSummaryNote(Note $note): bool
    {
        if ($note->system || $note->position !== null) {
            return false;
        }

        return $note->author?->username === $this->summaryNoteAuthor && str_starts_with($note->body, $this->summaryNotePrefix);
    }
}
