<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Filter;

use DR\Review\Form\Filter\FilterCollectionType;
use DR\Review\Form\Filter\InExclusionFilterType;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Transformer\FilterCollectionTransformer;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @coversDefaultClass \DR\Review\Form\Filter\InExclusionFilterType
 */
class InExclusionFilterTypeTest extends AbstractTestCase
{
    /**
     * @covers ::buildForm
     */
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects(self::exactly(2))
            ->method('add')
            ->will(
                static::onConsecutiveCalls(
                    ['inclusions', FilterCollectionType::class],
                    ['exclusions', FilterCollectionType::class],
                )
            )->willReturnSelf();
        $builder->expects(self::once())->method('addModelTransformer')->with(self::isInstanceOf(FilterCollectionTransformer::class));

        $type = new InExclusionFilterType();
        $type->buildForm($builder, []);
    }
}
