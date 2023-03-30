<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProviderInterface;
use DR\Review\ApiPlatform\Output\CodeReviewOutput;
use InvalidArgumentException;

/**
 * @implements ProviderInterface<CodeReviewOutput>
 */
class CodeReviewPatchProvider implements ProviderInterface
{
    /**
     * @inheritDoc
     * @return CodeReviewOutput[]
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): CodeReviewOutput
    {
        if ($operation instanceof Patch === false) {
            throw new InvalidArgumentException('Only Patch operation is supported');
        }

        return new CodeReviewOutput(
            5,
            6,
            'slug',
            'test',
            'test2',
            'url',
            'closed',
            'rejected',
            [],
            [],
            123,
            456
        );
    }
}
