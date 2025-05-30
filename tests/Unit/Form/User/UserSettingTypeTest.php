<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\User;

use DR\Review\Entity\User\UserSetting;
use DR\Review\Form\User\UserSettingType;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(UserSettingType::class)]
class UserSettingTypeTest extends AbstractTestCase
{
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->exactly(6))
            ->method('add')
            ->with(
                ...consecutive(
                    ['colorTheme', ChoiceType::class],
                    ['mailCommentAdded', CheckboxType::class],
                    ['mailCommentReplied', CheckboxType::class],
                    ['mailCommentResolved', CheckboxType::class],
                    ['browserNotificationEvents', ChoiceType::class],
                    ['ideUrl', TextType::class]
                )
            )->willReturnSelf();

        $type = new UserSettingType('ide-url');
        $type->buildForm($builder, []);
    }

    public function testConfigureOptions(): void
    {
        $resolver = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new UserSettingType('ide-url');
        $type->configureOptions($resolver);

        static::assertSame(UserSetting::class, $introspector->getDefault('data_class'));
    }
}
