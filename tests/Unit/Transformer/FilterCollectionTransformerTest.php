<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use DR\Review\Entity\Notification\Filter;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Transformer\FilterCollectionTransformer;

/**
 * @coversDefaultClass \DR\Review\Transformer\FilterCollectionTransformer
 */
class FilterCollectionTransformerTest extends AbstractTestCase
{
    private FilterCollectionTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transformer = new FilterCollectionTransformer();
    }

    /**
     * @covers ::transform
     */
    public function testTransformValueMustBeCollection(): void
    {
        static::assertNull($this->transformer->transform(null));
    }

    /**
     * @covers ::transform
     */
    public function testTransformCollection(): void
    {
        $filterA    = (new Filter())->setInclusion(true);
        $filterB    = (new Filter())->setInclusion(false);
        $collection = new ArrayCollection([$filterA, $filterB]);

        $result = $this->transformer->transform($collection);
        $expect = [
            'inclusions' => new ArrayCollection([$filterA]),
            'exclusions' => new ArrayCollection([$filterB])
        ];

        static::assertEquals($expect, $result);
    }

    /**
     * @covers ::reverseTransform
     */
    public function testReverseTransformValueMustBeArray(): void
    {
        static::assertNull($this->transformer->reverseTransform(null));
    }

    /**
     * @covers ::reverseTransform
     */
    public function testReverseTransform(): void
    {
        $filterA = (new Filter())->setInclusion(true);
        $filterB = (new Filter())->setInclusion(false);
        $data    = [
            'inclusions' => new ArrayCollection([$filterA]),
            'exclusions' => new ArrayCollection([$filterB])
        ];

        $result = $this->transformer->reverseTransform($data);
        $expect = new ArrayCollection([$filterA, $filterB]);

        static::assertEquals($expect, $result);
    }
}
