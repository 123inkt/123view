<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Repository\Credential;

use DR\Review\Entity\Repository\Credential\SshKeyCredential;
use DR\Review\Form\Repository\Credential\SshKeyCredentialType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[CoversClass(SshKeyCredentialType::class)]
class SshKeyCredentialTypeTest extends AbstractTestCase
{
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->once())
            ->method('add')
            ->with('privateKey', TextareaType::class)
            ->willReturnSelf();

        $type = new SshKeyCredentialType();
        $type->buildForm($builder, []);
    }

    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new SshKeyCredentialType();
        $type->configureOptions($resolver);

        static::assertSame(SshKeyCredential::class, $introspector->getDefault('data_class'));
    }

    public function testSetPrivateKey(): void
    {
        $credential = new SshKeyCredential(null);
        $type       = new SshKeyCredentialType();

        // null: do not overwrite
        $type->setPrivateKey($credential, null);
        static::assertNull($credential->getPrivateKey());

        // empty string: do not overwrite
        $type->setPrivateKey($credential, '');
        static::assertNull($credential->getPrivateKey());

        // actual value: overwrite
        $type->setPrivateKey($credential, 'newkey');
        static::assertSame('newkey', $credential->getPrivateKey());
    }
}
