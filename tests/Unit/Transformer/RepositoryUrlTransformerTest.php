<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Transformer;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Transformer\RepositoryUrlTransformer;

/**
 * @coversDefaultClass \DR\Review\Transformer\RepositoryUrlTransformer
 * @covers ::__construct
 */
class RepositoryUrlTransformerTest extends AbstractTestCase
{
    private RepositoryUrlTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transformer = new RepositoryUrlTransformer();
    }

    /**
     * @covers ::transform
     */
    public function testTransform(): void
    {
        $this->transformer->transform('https://sherlock:holmes@example.com/foobar?query#anchor');
    }

    /**
     * @covers ::reverseTransform
     */
    public function testReverseTransform(): void
    {
    }

}
