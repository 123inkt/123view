<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\OpenApi;

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use DR\Review\ApiPlatform\OpenApi\OperationParameterDocumentor;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\ApiPlatform\OpenApi\OperationParameterDocumentor
 */
class OperationParameterDocumentorTest extends AbstractTestCase
{
    private OperationParameterDocumentor $documentor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->documentor = new OperationParameterDocumentor();
    }

    /**
     * @covers ::getDescription
     */
    public function testGetDescription(): void
    {
        $parameter = new Parameter('review.id', 'in');
        $operation = new Operation('api_code-review-activities_get_collection');

        $result = $this->documentor->getDescription($operation, $parameter);
        static::assertSame('Exact search for the review id of the activity', $result);
    }

    /**
     * @covers ::getDescription
     */
    public function testGetDescriptionMiss(): void
    {
        $parameter = new Parameter('foo', 'in');
        $operation = new Operation('bar');

        static::assertSame('', $this->documentor->getDescription($operation, $parameter));
    }
}
