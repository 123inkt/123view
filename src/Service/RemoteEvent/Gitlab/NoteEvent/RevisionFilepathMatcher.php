<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab\NoteEvent;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Revision\RevisionFileRepository;
use DR\Utils\Arrays;

readonly class RevisionFilepathMatcher
{
    public function __construct(private RevisionFileRepository $revisionFileRepository)
    {
    }

    /**
     * @param Revision[] $revisions
     *
     * @return array{0: Revision, 1: string}|array{0: null, 1: null}
     */
    public function matchRevision(NoteEvent $event, array $revisions): array
    {
        foreach (Arrays::removeNull([$event->position->newPath, $event->position->oldPath]) as $path) {
            $revision = $this->findRevisionFor($path, $revisions, $event->position->headSha);
            if ($revision !== null) {
                return [$revision, $path];
            }
        }

        return [null, null];
    }

    /**
     * @param Revision[] $revisions
     */
    private function findRevisionFor(string $filepath, array $revisions, string $preferSha): ?Revision
    {
        $files = $this->revisionFileRepository->findRevisionsForFile($revisions, $filepath);
        foreach ($files as $file) {
            if ($file->getRevision()->getCommitHash() === $preferSha) {
                return $file->getRevision();
            }
        }

        return Arrays::firstOrNull($files)?->getRevision();
    }

}
