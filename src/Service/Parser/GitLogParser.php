<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Parser;

use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Git\Commit;
use DR\GitCommitNotification\Git\FormatPattern;
use DR\GitCommitNotification\Service\CommitHydrator;
use DR\GitCommitNotification\Service\Git\Log\FormatPatternFactory;
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
    public function parse(Repository $repository, string $commitLog): array
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
            $diffFiles = $this->diffParser->parse($data[FormatPattern::PATCH]);

            // create model from the parts
            $result[] = $logCommit = $this->hydrator->hydrate($repository, $data, $diffFiles);

            // transfer refs from previous commit if current commit doesn't have one.
            if ($logCommit->refs === null && $previousCommit !== null) {
                $logCommit->refs = $previousCommit->refs;
            }
            $previousCommit = $logCommit;
        }

        return $result;
    }
}
