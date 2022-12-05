<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Rule;

use DR\Review\Entity\Notification\Rule;
use DR\Review\Form\Filter\InExclusionFilterType;
use DR\Review\Form\Recipient\RecipientCollectionType;
use DR\Review\Form\Repository\RepositoryChoiceType;
use DR\Review\Form\Rule\RuleOptionsType;
use DR\Review\Form\Rule\RuleType;
use DR\Review\Tests\AbstractTestCase;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @coversDefaultClass \DR\Review\Form\Rule\RuleType
 * @covers ::__construct
 */
class RuleTypeTest extends AbstractTestCase
{
    /**
     * @covers ::buildForm
     */
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects(self::exactly(6))
            ->method('add')
            ->withConsecutive(
                ['name', TextType::class],
                ['active', CheckboxType::class],
                ['ruleOptions', RuleOptionsType::class],
                ['recipients', RecipientCollectionType::class],
                ['repositories', RepositoryChoiceType::class],
                ['filters', InExclusionFilterType::class],
            )->willReturnSelf();

        $type = new RuleType(true);
        $type->buildForm($builder, []);
    }

    /**
     * @covers ::buildForm
     */
    public function testBuildFormWithoutRecipients(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects(self::exactly(5))
            ->method('add')
            ->withConsecutive(
                ['name', TextType::class],
                ['active', CheckboxType::class],
                ['ruleOptions', RuleOptionsType::class],
                ['repositories', RepositoryChoiceType::class],
                ['filters', InExclusionFilterType::class],
            )->willReturnSelf();

        $builder->expects(self::once())->method('get')->with('repositories')->willReturnSelf();
        $builder->expects(self::once())->method('addModelTransformer')->with(self::isInstanceOf(CollectionToArrayTransformer::class));

        $type = new RuleType(false);
        $type->buildForm($builder, []);
    }

    /**
     * @covers ::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new RuleType(true);
        $type->configureOptions($resolver);

        static::assertSame(Rule::class, $introspector->getDefault('data_class'));
    }
}
