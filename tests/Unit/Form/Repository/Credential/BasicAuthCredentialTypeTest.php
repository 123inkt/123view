<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Repository\Credential;

use DR\Review\Entity\Repository\Credential\BasicAuthCredential;
use DR\Review\Form\Repository\Credential\BasicAuthCredentialType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(BasicAuthCredentialType::class)]
class BasicAuthCredentialTypeTest extends AbstractTestCase
{
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->exactly(2))
            ->method('add')
            ->with(
                ...consecutive(
                    ['username', TextType::class],
                    ['password', PasswordType::class],
                )
            )->willReturnSelf();

        $type = new BasicAuthCredentialType();
        $type->buildForm($builder, []);
    }

    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new BasicAuthCredentialType();
        $type->configureOptions($resolver);

        static::assertSame(BasicAuthCredential::class, $introspector->getDefault('data_class'));
    }

    public function testSetPassword(): void
    {
        $credential = new BasicAuthCredential('foo', 'bar');
        $type       = new BasicAuthCredentialType();

        // don't overwrite
        $type->setPassword($credential, null);
        static::assertSame('bar', $credential->getPassword());

        // overwrite
        $type->setPassword($credential, 'baz');
        static::assertSame('baz', $credential->getPassword());
    }
}
