<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Form\Filter;

use DR\GitCommitNotification\Entity\Config\Filter;
use DR\GitCommitNotification\Form\Filter\FilterType;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Form\Filter\FilterType
 */
class FilterTypeTest extends AbstractTestCase
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
                ['type', ChoiceType::class],
                ['pattern', TextType::class],
            )->willReturnSelf();

        $type = new FilterType();
        $type->buildForm($builder, []);
    }

    /**
     * @covers ::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new FilterType();
        $type->configureOptions($resolver);

        static::assertSame(Filter::class, $introspector->getDefault('data_class'));
    }
}
