<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\OpenApi;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use Generator;
use IteratorAggregate;

class OpenApiParameterIterator implements IteratorAggregate
{
    public function __construct(private readonly OpenApi $openApi)
    {
    }

    /**
     * @return Generator<array{0: Operation, 1: Parameter}>
     */
    public function getIterator(): Generator
    {
        $pathItems = $this->openApi->getPaths()->getPaths();
        foreach ($pathItems as $pathItem) {
            foreach (PathItem::$methods as $method) {
                $getter = 'get' . ucfirst(strtolower($method));

                /** @var Operation $operation */
                $operation = $pathItem->$getter();
                if ($operation?->getParameters() === null) {
                    continue;
                }

                foreach ($operation->getParameters() as $parameter) {
                    if ($parameter->getDescription() !== '') {
                        continue;
                    }

                    yield [$operation, $parameter];
                }
            }
        }
    }
}
