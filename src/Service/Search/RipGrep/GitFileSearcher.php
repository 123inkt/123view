<?php
declare(strict_types=1);

namespace DR\Review\Service\Search\RipGrep;

class GitFileSearcher
{
    private const DEFAULT_ARGUMENTS = [
        '--hidden',
        '--color',
        'never',
        '--line-number',
        '--after-context=5',
        '--before-context=5',
        '--field-context-separator=#',
        '--glob',
        '!.git/',
    ];

    public function __construct(private readonly string $gitCacheDirectory, private readonly RipGrepProcessExecutor $executor)
    {
    }

    public function find(string $searchQuery): string
    {
        $arguments = self::DEFAULT_ARGUMENTS;
        array_push($arguments, $searchQuery);

        return $this->executor->execute($arguments, $this->gitCacheDirectory)->output;
    }
}
