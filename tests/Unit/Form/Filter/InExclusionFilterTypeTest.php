<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Filter;

use DR\Review\Form\Filter\FilterCollectionType;
use DR\Review\Form\Filter\InExclusionFilterType;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Transformer\FilterCollectionTransformer;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\FormBuilderInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(InExclusionFilterType::class)]
class InExclusionFilterTypeTest extends AbstractTestCase
{
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->exactly(2))
            ->method('add')
            ->with(
                ...consecutive(
                    ['inclusions', FilterCollectionType::class],
                    ['exclusions', FilterCollectionType::class],
                )
            )->willReturnSelf();
        $builder->expects($this->once())->method('addModelTransformer')->with(self::isInstanceOf(FilterCollectionTransformer::class));

        $type = new InExclusionFilterType();
        $type->buildForm($builder, []);
    }
}
