<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Filter;

use DR\Review\Entity\Notification\Filter;
use DR\Review\Form\Filter\FilterType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(FilterType::class)]
class FilterTypeTest extends AbstractTestCase
{
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->exactly(2))
            ->method('add')
            ->with(...consecutive(['type', ChoiceType::class], ['pattern', TextType::class]))
            ->willReturnSelf();

        $type = new FilterType();
        $type->buildForm($builder, []);
    }

    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new FilterType();
        $type->configureOptions($resolver);

        static::assertSame(Filter::class, $introspector->getDefault('data_class'));
    }
}
