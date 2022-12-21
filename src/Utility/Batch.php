<?php
declare(strict_types=1);

namespace DR\Review\Utility;

/**
 * @template T
 */
class Batch
{
    /** @var T[] */
    private array $queue = [];

    /**
     * @param callable(T[] $entities): void $callback
     */
    public function __construct(private int $threshold, private $callback) // @codingStandardsIgnoreLine
    {
    }

    /**
     * @param T $entity
     */
    public function add(mixed $entity): void
    {
        $this->queue[] = $entity;

        if (count($this->queue) >= $this->threshold) {
            $this->flush();
        }
    }

    /**
     * @param T[] $entities
     */
    public function addAll(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->add($entity);
        }
    }

    public function flush(): void
    {
        if (count($this->queue) > 0) {
            ($this->callback)($this->queue);
        }
        $this->queue = [];
    }
}
