<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\Filter;

use ApiPlatform\Doctrine\Orm\Filter\FilterInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use DR\Review\Entity\Review\Comment;

final class CommentFilepathFilter implements FilterInterface
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @param array<string, mixed> $context
     */
    public function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if ($resourceClass !== Comment::class) {
            return;
        }

        $alias   = $queryBuilder->getRootAliases()[0];
        $filters = $context['filters'] ?? null;
        if (is_array($filters) === false) {
            return;
        }

        $exactFilters = $filters['exact'] ?? null;
        if (is_array($exactFilters) && array_key_exists('filepath', $exactFilters)) {
            $filepath = $exactFilters['filepath'];
            if (is_string($filepath) === false) {
                throw new InvalidArgumentException('The exact filepath filter must be a string.');
            }

            $parameterName = $queryNameGenerator->generateParameterName('filepath');
            $queryBuilder
                ->andWhere(sprintf('%s.filePath = :%s', $alias, $parameterName))
                ->setParameter($parameterName, $filepath);
        }

        $orderFilters = $filters['order'] ?? null;
        if (!is_array($orderFilters) || array_key_exists('filepath', $orderFilters) === false) {
            return;
        }

        $direction = $orderFilters['filepath'];
        if (is_string($direction) === false) {
            return;
        }

        $direction = strtoupper($direction);
        if (in_array($direction, ['ASC', 'DESC'], true) === false) {
            return;
        }

        $queryBuilder
            ->addOrderBy($alias . '.filePath', $direction);
    }

    /**
     * @return array<string, array{
     *     property?: string,
     *     type?: string,
     *     required?: bool,
     *     description?: string,
     *     strategy?: string,
     *     is_collection?: bool,
     *     schema?: array<string, mixed>,
     * }>
     */
    public function getDescription(string $resourceClass): array
    {
        if ($resourceClass !== Comment::class) {
            return [];
        }

        return [
            'exact[filepath]' => [
                'property'    => 'filepath',
                'type'        => 'string',
                'required'    => false,
                'strategy'    => 'exact',
                'description' => 'Exact search for the comment filepath',
            ],
            'order[filepath]' => [
                'property'    => 'filepath',
                'type'        => 'string',
                'required'    => false,
                'description' => 'Order by comment filepath',
                'schema'      => [
                    'type' => 'string',
                    'enum' => ['asc', 'desc', 'ASC', 'DESC'],
                ],
            ],
        ];
    }
}
