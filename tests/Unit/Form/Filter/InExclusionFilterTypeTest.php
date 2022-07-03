<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Form\Filter;

use DR\GitCommitNotification\Form\Filter\FilterCollectionType;
use DR\GitCommitNotification\Form\Filter\InExclusionFilterType;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Transformer\FilterCollectionTransformer;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Form\Filter\InExclusionFilterType
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
            ->withConsecutive(
                ['inclusions', FilterCollectionType::class],
                ['exclusions', FilterCollectionType::class],
            )->willReturnSelf();
        $builder->expects(self::once())->method('addModelTransformer')->with(self::isInstanceOf(FilterCollectionTransformer::class));

        $type = new InExclusionFilterType();
        $type->buildForm($builder, []);
    }
}
