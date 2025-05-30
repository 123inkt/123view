<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Repository\Credential;

use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Form\Repository\Credential\BasicAuthCredentialType;
use DR\Review\Form\Repository\Credential\RepositoryCredentialType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(RepositoryCredentialType::class)]
class RepositoryCredentialTypeTest extends AbstractTestCase
{
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->exactly(3))
            ->method('add')
            ->with(
                ...consecutive(
                    ['name', TextType::class],
                    ['authType', ChoiceType::class],
                    ['credentials', BasicAuthCredentialType::class],
                )
            )->willReturnSelf();

        $type = new RepositoryCredentialType();
        $type->buildForm($builder, []);
    }

    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new RepositoryCredentialType();
        $type->configureOptions($resolver);

        static::assertSame(RepositoryCredential::class, $introspector->getDefault('data_class'));
    }
}
