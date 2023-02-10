<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Repository;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Form\Repository\RepositoryType;
use DR\Review\Form\Repository\RepositoryUrlType;
use DR\Review\Tests\AbstractTestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @coversDefaultClass \DR\Review\Form\Repository\RepositoryType
 */
class RepositoryTypeTest extends AbstractTestCase
{
    /**
     * @covers ::buildForm
     */
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects(self::exactly(8))
            ->method('add')
            ->withConsecutive(
                ['active', CheckboxType::class],
                ['favorite', CheckboxType::class],
                ['name', TextType::class],
                ['displayName', TextType::class],
                ['mainBranchName', TextType::class],
                ['url', RepositoryUrlType::class],
                ['updateRevisionsInterval', IntegerType::class],
                ['validateRevisionsInterval', IntegerType::class],
            )->willReturnSelf();

        $type = new RepositoryType();
        $type->buildForm($builder, []);
    }

    /**
     * @covers ::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new RepositoryType();
        $type->configureOptions($resolver);

        static::assertSame(Repository::class, $introspector->getDefault('data_class'));
    }
}
