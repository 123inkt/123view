<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Form\Rule;

use DR\GitCommitNotification\Entity\Config\RuleOptions;
use DR\GitCommitNotification\Form\Rule\RuleOptionsType;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Form\Rule\RuleOptionsType
 */
class RuleOptionsTypeTest extends AbstractTestCase
{
    /**
     * @covers ::buildForm
     */
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects(self::exactly(9))
            ->method('add')
            ->withConsecutive(
                ['frequency', ChoiceType::class,],
                ['theme', ChoiceType::class],
                ['subject', TextType::class],
                ['diffAlgorithm', ChoiceType::class],
                ['ignoreSpaceAtEol', CheckboxType::class],
                ['ignoreSpaceChange', CheckboxType::class],
                ['ignoreAllSpace', CheckboxType::class],
                ['ignoreBlankLines', CheckboxType::class],
                ['excludeMergeCommits', CheckboxType::class],
            )->willReturnSelf();

        $type = new RuleOptionsType();
        $type->buildForm($builder, []);
    }

    /**
     * @covers ::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new RuleOptionsType();
        $type->configureOptions($resolver);

        static::assertSame(RuleOptions::class, $introspector->getDefault('data_class'));
    }
}
