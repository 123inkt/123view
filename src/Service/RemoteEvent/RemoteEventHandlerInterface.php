<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent;

/**
 * @template T of object
 */
interface RemoteEventHandlerInterface
{
    public function supports(object $event): bool;

    /**
     * @param T $event
     */
    public function handle(object $event): void;
}
