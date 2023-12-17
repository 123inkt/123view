<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Review\LineReferenceStateEnum;
use DR\Review\Model\Api\Gitlab\Position;
use DR\Review\Model\Api\Gitlab\Version;

class PositionFactory
{
    public function create(Version $version, LineReference $lineReference): Position
    {
        $position               = new Position();
        $position->positionType = 'text';
        $position->headSha      = $lineReference->headSha ?? $version->headCommitSha;
        $position->startSha     = $version->startCommitSha;
        $position->baseSha      = $version->baseCommitSha;
        $position->oldPath      = $lineReference->oldPath;
        $position->newPath      = $lineReference->newPath;

        if ($lineReference->state === LineReferenceStateEnum::Added || $lineReference->state === LineReferenceStateEnum::Modified) {
            $position->newLine = $lineReference->lineAfter;
        } elseif ($lineReference->state === LineReferenceStateEnum::Deleted) {
            $position->oldLine = $lineReference->line;
        } else {
            $position->oldLine = $lineReference->line;
            $position->newLine = $lineReference->lineAfter;
        }

        return $position;
    }
}
