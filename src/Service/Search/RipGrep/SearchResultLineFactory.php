<?php
declare(strict_types=1);

namespace DR\Review\Service\Search\RipGrep;

use DR\Review\Model\Search\SearchResultLine;
use DR\Review\Model\Search\SearchResultLineTypeEnum;

class SearchResultLineFactory
{
    /**
     * @param array{data: array{lines: array{text: string}, line_number: int}} $entry
     */
    public function createContextFromEntry(array $entry): SearchResultLine
    {
        if (isset($entry['data']['lines']['text']) === false) {
            $test =true;
        }


        return new SearchResultLine(
            $entry['data']['lines']['text'] ?? '',
            $entry['data']['line_number'],
            SearchResultLineTypeEnum::Context
        );
    }

    /**
     * @param array{data: array{lines: array{text: string}, line_number: int}} $entry
     */
    public function createMatchFromEntry(array $entry): SearchResultLine
    {
        return new SearchResultLine(
            $entry['data']['lines']['text'] ?? '',
            $entry['data']['line_number'],
            SearchResultLineTypeEnum::Match
        );
    }
}
