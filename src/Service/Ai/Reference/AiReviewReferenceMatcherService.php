<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Reference;

use DR\Review\Entity\Ai\AiReviewReference;
use DR\Review\Entity\Ai\AiReviewReferenceSection;
use DR\Review\Repository\Ai\AiReviewReferenceRepository;

/**
 * Determines which administrator-configured {@see AiReviewReferenceSection}s apply to a given
 * changed file, based on structured file-pattern (glob) and optional code-marker matchers.
 */
class AiReviewReferenceMatcherService
{
    public function __construct(private readonly AiReviewReferenceRepository $referenceRepository)
    {
    }

    /**
     * @return AiReviewReferenceSection[]
     */
    public function getApplicableSections(string $filepath, string $diffContent = ''): array
    {
        $sections = [];
        foreach ($this->referenceRepository->findAllEnabled() as $reference) {
            if ($this->referenceMatches($reference, $filepath, $diffContent) === false) {
                continue;
            }

            foreach ($reference->getSections() as $section) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    private function referenceMatches(AiReviewReference $reference, string $filepath, string $diffContent): bool
    {
        // a reference without any matcher never applies automatically
        foreach ($reference->getMatchers() as $matcher) {
            if ($this->matchesFilePattern($matcher->getFilePattern(), $filepath) === false) {
                continue;
            }

            $codeMarker = $matcher->getCodeMarker();
            if ($codeMarker !== null && $codeMarker !== '' && $this->matchesCodeMarker($codeMarker, $diffContent) === false) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function matchesFilePattern(string $pattern, string $filepath): bool
    {
        return preg_match($this->globToRegex($pattern), $filepath) === 1;
    }

    private function matchesCodeMarker(string $marker, string $diffContent): bool
    {
        if (@preg_match('#' . $marker . '#', '') !== false) {
            return preg_match('#' . $marker . '#', $diffContent) === 1;
        }

        return str_contains($diffContent, $marker);
    }

    /**
     * Converts a glob pattern to a regex. `*` (and `**`) match any number of characters, including
     * path separators, so a pattern like `*.php` matches a PHP file at any depth.
     */
    private function globToRegex(string $glob): string
    {
        $regex  = '';
        $length = strlen($glob);
        for ($i = 0; $i < $length; $i++) {
            $char = $glob[$i];
            if ($char === '*') {
                $regex .= '.*';
                // collapse consecutive `*`/`**` into a single `.*`
                while (($glob[$i + 1] ?? '') === '*') {
                    $i++;
                }
            } elseif ($char === '?') {
                $regex .= '.';
            } else {
                $regex .= preg_quote($char, '#');
            }
        }

        return '#^' . $regex . '$#';
    }
}
