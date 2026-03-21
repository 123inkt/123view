<?php
declare(strict_types=1);

namespace DR\Review\Service\Parser;

use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Git\FormatPattern;
use DR\Review\Service\CommitHydrator;
use DR\Review\Service\Git\Log\FormatPatternFactory;
use Exception;

class GitLogParser
{
    private CommitHydrator       $hydrator;
    private FormatPatternFactory $patternFactory;
    private DiffParser           $diffParser;

    public function __construct(FormatPatternFactory $patternFactory, CommitHydrator $hydrator, DiffParser $diffParser)
    {
        $this->hydrator       = $hydrator;
        $this->patternFactory = $patternFactory;
        $this->diffParser     = $diffParser;
    }

    public function getPattern(): string
    {
        return $this->patternFactory->createPattern();
    }

    /**
     * @return Commit[]
     * @throws Exception
     */
    public function parse(Repository $repository, string $commitLog, ?int $limit = null, bool $includeRaw = false): array
    {
        $result         = [];
        $pattern        = array_merge([], FormatPatternFactory::PATTERN, [FormatPattern::PATCH]);
        $previousCommit = null;

        $commits = explode(FormatPatternFactory::COMMIT_DELIMITER, $commitLog);
        foreach ($commits as $commit) {
            // skip empty commits
            if ($commit === '') {
                continue;
            }

            // explode parts
            $parts = explode(FormatPatternFactory::PARTS_DELIMITER, $commit);

            // combine keys with the values. (never false, as warnings are converted to exceptions via symfony)
            /** @var array<string, string> $data */
            $data = array_combine($pattern, $parts);

            // parse porcelain patch log
            $diffFiles = $this->diffParser->parse($data[FormatPattern::PATCH], $includeRaw);

            // create model from the parts
            $result[] = $logCommit = $this->hydrator->hydrate($repository, $data, $diffFiles);

            // transfer refs from previous commit if current commit doesn't have one.
            if ($logCommit->refs === null && $previousCommit !== null) {
                $logCommit->refs = $previousCommit->refs;
            }
            $previousCommit = $logCommit;

            if ($limit !== null && count($result) === $limit) {
                break;
            }
        }

        return $result;
    }
}
