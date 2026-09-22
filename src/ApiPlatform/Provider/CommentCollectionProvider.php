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
use DR\Utils\Assert;
use Generator;
use InvalidArgumentException;

/**
 * @implements ProviderInterface<CommentOutput>
 */
readonly class CommentCollectionProvider implements ProviderInterface
{
    /**
     * @param ProviderInterface<Comment> $collectionProvider
     */
    public function __construct(private ProviderInterface $collectionProvider, private CommentOutputFactory $commentOutputFactory)
    {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        Assert::isInstanceOf($operation, GetCollection::class, 'Only GetCollection operation is supported');

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
     * @param iterable<Comment> $comments
     * @return Generator<int, CommentOutput>
     */
    private function mapComments(iterable $comments): Generator
    {
        foreach ($comments as $comment) {
            yield $this->commentOutputFactory->create($comment);
        }
    }
}
