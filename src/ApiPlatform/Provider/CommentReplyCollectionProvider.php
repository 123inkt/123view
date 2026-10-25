<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\Provider;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use DR\Review\ApiPlatform\Factory\CommentReplyOutputFactory;
use DR\Review\ApiPlatform\Output\CommentReplyOutput;
use DR\Review\Entity\Review\CommentReply;
use DR\Utils\Assert;
use Generator;
use InvalidArgumentException;

/**
 * @implements ProviderInterface<CommentReplyOutput>
 */
readonly class CommentReplyCollectionProvider implements ProviderInterface
{
    /**
     * @param ProviderInterface<CommentReply> $collectionProvider
     */
    public function __construct(private ProviderInterface $collectionProvider, private CommentReplyOutputFactory $commentReplyOutputFactory)
    {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        Assert::isInstanceOf($operation, GetCollection::class, 'Only GetCollection operation is supported');

        $replies = $this->collectionProvider->provide($operation, $uriVariables, $context);
        if (is_iterable($replies) === false) {
            throw new InvalidArgumentException('Collection provider must return an iterable.');
        }

        $mappedReplies = $this->mapReplies($replies);
        if ($replies instanceof PaginatorInterface) {
            return new TraversablePaginator(
                $mappedReplies,
                $replies->getCurrentPage(),
                $replies->getItemsPerPage(),
                $replies->getTotalItems(),
            );
        }

        return $mappedReplies;
    }

    /**
     * @param iterable<CommentReply> $replies
     *
     * @return Generator<int, CommentReplyOutput>
     */
    private function mapReplies(iterable $replies): Generator
    {
        foreach ($replies as $reply) {
            yield $this->commentReplyOutputFactory->create($reply);
        }
    }
}
