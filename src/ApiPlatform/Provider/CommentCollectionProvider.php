<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\Provider;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PaginatorInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use DR\Review\ApiPlatform\Factory\CommentOutputFactory;
use DR\Review\ApiPlatform\Output\CommentOutput;
use DR\Review\Entity\Review\Comment;
use Generator;
use InvalidArgumentException;

/**
 * @implements ProviderInterface<CommentOutput>
 */
class CommentCollectionProvider implements ProviderInterface
{
    /**
     * @param ProviderInterface<Comment> $collectionProvider
     */
    public function __construct(private readonly ProviderInterface $collectionProvider, private readonly CommentOutputFactory $commentOutputFactory)
    {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof GetCollection === false) {
            throw new InvalidArgumentException('Only GetCollection operation is supported');
        }

        $comments = $this->collectionProvider->provide($operation, $uriVariables, $context);
        if ($comments instanceof PaginatorInterface) {
            return new TraversablePaginator(
                $this->mapComments($comments),
                $comments->getCurrentPage(),
                $comments->getItemsPerPage(),
                $comments->getTotalItems(),
            );
        }

        if (is_iterable($comments)) {
            return $this->mapComments($comments);
        }

        throw new InvalidArgumentException('Collection provider must return an iterable.');
    }

    /**
     * @param iterable<mixed> $comments
     * @return Generator<int, CommentOutput>
     */
    private function mapComments(iterable $comments): Generator
    {
        foreach ($comments as $comment) {
            if ($comment instanceof Comment === false) {
                throw new InvalidArgumentException('Collection provider returned an invalid comment.');
            }

            yield $this->commentOutputFactory->create($comment);
        }
    }
}
