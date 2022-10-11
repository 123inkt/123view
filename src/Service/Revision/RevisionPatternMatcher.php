<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Revision;

class RevisionPatternMatcher
{
    public function match(string $message): ?string {

    }
}
//
///**
// * Grab the task name from a commit message
// */
//class TaskNameMatcher
//{
//    public function match(string $message): ?MatchResult
//    {
//        // match F#123 US#123 T#123
//        // match F#123 US#123 B#123
//        // match US#123 T#123
//        // match US#123 B#123
//        if (preg_match('/^\s*(?:F#(?<feature>\d+)\s+)?US#(?<story>\d+)\s((?<type>[BT])#(?<task>\d+))/', $message, $matches) === 1) {
//            return new MatchResult(
//                featureId: $matches['feature'],
//                storyId:   $matches['story'],
//                taskId:    $matches['type'] === 'T' ? $matches['task'] : null,
//                bugId:     $matches['type'] === 'B' ? $matches['task'] : null
//            );
//        }
//
//        // match B#123
//        if (preg_match('/^\s*B#(?<bug>\d+)/', $message, $matches) === 1) {
//            return new MatchResult(bugId: $matches['bug']);
//        }
//
//        return null;
//    }
//}
