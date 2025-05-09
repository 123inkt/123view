<?php
declare(strict_types=1);

namespace DR\Review\Service\Search\RipGrep;

use DR\Review\Model\Search\SearchResult;
use DR\Review\Model\Search\SearchResultLine;
use DR\Review\Model\Search\SearchResultLineTypeEnum;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Symfony\Component\Finder\SplFileInfo;

class FileSearcher
{
    private const DEFAULT_ARGUMENTS = [
        '--hidden',
        '--color=never',
        '--line-number',
        '--after-context=5',
        '--before-context=5',
        '--field-context-separator=#',
        '--glob=!.git/',
        '--json'
    ];

    public function __construct(private readonly string $gitCacheDirectory, private readonly RipGrepProcessExecutor $executor)
    {
    }

    /**
     * @return SearchResult[]
     * @throws JsonException
     */
    public function find(string $searchQuery): array
    {
        $arguments = self::DEFAULT_ARGUMENTS;
        array_push($arguments, $searchQuery);

        $results = [];
        $current = null;
        foreach ($this->executor->execute($arguments, $this->gitCacheDirectory) as $line) {
            $data = Json::decode($line, true);
            if ($data['type'] === 'begin') {
                $filepath = $data['data']['path']['text'];
                $file     = new SplFileInfo($filepath, $this->gitCacheDirectory, str_replace($this->gitCacheDirectory, '', $filepath));
                $current  = new SearchResult($file);
            } elseif ($data['type'] === 'end') {
                $results[] = $current;
                $current   = null;
            } elseif ($data['type'] === 'context') {
                $resultLine       = new SearchResultLine(
                    $data['data']['lines']['text'],
                    $data['data']['line_number'],
                    SearchResultLineTypeEnum::Context
                );
                $current->lines[] = $resultLine;
            } elseif ($data['type'] === 'match') {
                $resultLine       = new SearchResultLine(
                    $data['data']['lines']['text'],
                    $data['data']['line_number'],
                    SearchResultLineTypeEnum::Match
                );
                $current->lines[] = $resultLine;
            }

            if (count($results) >= 100) {
                break;
            }
        }

        return $results;
    }
}
