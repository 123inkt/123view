<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\User;

use DR\Review\Form\User\LoginFormType;
use DR\Review\Tests\AbstractTestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @coversDefaultClass \DR\Review\Form\User\LoginFormType
 */
class LoginFormTypeTest extends AbstractTestCase
{
    /**
     * @covers ::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new LoginFormType();
        $type->configureOptions($resolver);

        static::assertSame('_csrf_token', $introspector->getDefault('csrf_field_name'));
        static::assertSame('authenticate', $introspector->getDefault('csrf_token_id'));
        static::assertSame(['string'], $introspector->getAllowedTypes('username'));
        static::assertSame(['string', 'null'], $introspector->getAllowedTypes('targetPath'));
    }

    /**
     * @covers ::buildForm
     */
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())->method('setMethod')->with('POST');
        $builder->expects(self::exactly(4))
            ->method('add')
            ->will(
                static::onConsecutiveCalls(
                    ['_username', EmailType::class],
                    ['_password', PasswordType::class],
                    ['_target_path', HiddenType::class],
                    ['loginBtn', SubmitType::class],
                )
            )
            ->willReturnSelf();

        $type = new LoginFormType();
        $type->buildForm($builder, ['targetPath' => 'path']);
    }

    /**
     * @covers ::buildForm
     */
    public function testBuildFormWithoutTargetPath(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->expects(self::once())->method('setMethod')->with('POST');
        $builder->expects(self::exactly(3))
            ->method('add')
            ->will(
                static::onConsecutiveCalls(
                    ['_username', EmailType::class],
                    ['_password', PasswordType::class],
                    ['loginBtn', SubmitType::class],
                )
            )
            ->willReturnSelf();

        $type = new LoginFormType();
        $type->buildForm($builder, []);
    }

    /**
     * @covers ::getBlockPrefix
     */
    public function testGetBlockPrefix(): void
    {
        $type = new LoginFormType();
        static::assertSame('', $type->getBlockPrefix());
    }
}
