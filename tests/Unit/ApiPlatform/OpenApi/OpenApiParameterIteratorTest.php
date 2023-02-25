<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\OpenApi;

use ApiPlatform\OpenApi\Model\Info;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\OpenApi;
use DR\Review\ApiPlatform\OpenApi\OpenApiParameterIterator;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\ApiPlatform\OpenApi\OpenApiParameterIterator
 * @covers ::__construct
 */
class OpenApiParameterIteratorTest extends AbstractTestCase
{
    /**
     * @covers ::getIterator
     */
    public function testGetIterator(): void
    {
        $parameterA = new Parameter('with', 'description', description: 'foobar');
        $parameterB = new Parameter('without', 'description');
        $operationA = new Operation();
        $operationB = new Operation(parameters: [$parameterA, $parameterB]);
        $paths      = new Paths();
        $paths->addPath('withoutParameters', new PathItem(get: $operationA));
        $paths->addPath('withParameters', new PathItem(get: $operationB));
        $openApi = new OpenApi(new Info('title', '1.0.0'), [], $paths);

        $iterator = new OpenApiParameterIterator($openApi);

        // execute test
        $results = iterator_to_array($iterator);

        // assert
        static::assertSame([[$operationB, $parameterB]], $results);
    }
}
