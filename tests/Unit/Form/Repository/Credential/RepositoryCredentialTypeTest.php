<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\Repository\Credential;

use DR\Review\Doctrine\Type\AuthenticationType;
use DR\Review\Entity\Repository\RepositoryCredential;
use DR\Review\Form\Repository\Credential\CredentialTypeListener;
use DR\Review\Form\Repository\Credential\RepositoryCredentialType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use stdClass;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(RepositoryCredentialType::class)]
class RepositoryCredentialTypeTest extends AbstractTestCase
{
    private Stub&CredentialTypeListener $listener;
    private RepositoryCredentialType $type;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listener = $this->createStub(CredentialTypeListener::class);
        $this->type     = new RepositoryCredentialType($this->listener);
    }

    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->exactly(2))
            ->method('add')
            ->with(
                ...consecutive(
                    ['name', TextType::class],
                    ['authType', ChoiceType::class],
                )
            )->willReturnSelf();

        $builder->expects($this->exactly(2))
            ->method('addEventListener')
            ->with(
                ...consecutive(
                    [FormEvents::PRE_SET_DATA, $this->listener],
                    [FormEvents::PRE_SUBMIT, $this->listener],
                )
            )->willReturnSelf();

        $this->type->buildForm($builder, []);
    }

    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $this->type->configureOptions($resolver);

        static::assertSame(RepositoryCredential::class, $introspector->getDefault('data_class'));
    }

    public function testChoicesContainBothAuthTypes(): void
    {
        $state          = new stdClass();
        $state->choices = null;

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->method('addEventListener')->willReturnSelf();
        $builder->expects($this->exactly(2))
            ->method('add')
            ->willReturnCallback(
                static function (string $name, string $type, array $options = []) use ($state, $builder): FormBuilderInterface {
                    if ($name === 'authType') {
                        $state->choices = $options['choices'] ?? null;
                    }

                    return $builder;
                }
            );

        $this->type->buildForm($builder, []);

        static::assertSame(
            [
                'auth.type.basic-auth' => AuthenticationType::BASIC_AUTH,
                'auth.type.ssh-key'    => AuthenticationType::SSH_KEY,
            ],
            $state->choices
        );
    }
}
