<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\User;

use DR\Review\Entity\User\User;
use DR\Review\Form\User\RegistrationFormType;
use DR\Review\Tests\AbstractTestCase;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @coversDefaultClass \DR\Review\Form\User\RegistrationFormType
 */
class RegistrationFormTypeTest extends AbstractTestCase
{
    /**
     * @covers ::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new RegistrationFormType();
        $type->configureOptions($resolver);

        static::assertSame(User::class, $introspector->getDefault('data_class'));
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
                    ['name', TextType::class],
                    ['email', EmailType::class],
                    ['plainPassword', PasswordType::class],
                    ['register', SubmitType::class],
                )
            )
            ->willReturnSelf();

        $type = new RegistrationFormType();
        $type->buildForm($builder, []);
    }
}
