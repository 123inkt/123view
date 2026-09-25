<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Rule;

use DR\Review\Entity\Notification\RuleOptions;
use DR\Review\Form\Rule\RuleOptionsType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(RuleOptionsType::class)]
class RuleOptionsTypeTest extends AbstractTestCase
{
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->exactly(10))
            ->method('add')
            ->with(
                ...consecutive(
                    ['frequency', ChoiceType::class,],
                    ['theme', ChoiceType::class],
                    ['subject', TextType::class],
                    ['sendType', ChoiceType::class],
                    ['diffAlgorithm', ChoiceType::class],
                    ['ignoreSpaceAtEol', CheckboxType::class],
                    ['ignoreSpaceChange', CheckboxType::class],
                    ['ignoreAllSpace', CheckboxType::class],
                    ['ignoreBlankLines', CheckboxType::class],
                    ['excludeMergeCommits', CheckboxType::class],
                )
            )->willReturnSelf();

        $type = new RuleOptionsType();
        $type->buildForm($builder, []);
    }

    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new RuleOptionsType();
        $type->configureOptions($resolver);

        static::assertSame(RuleOptions::class, $introspector->getDefault('data_class'));
    }
}
