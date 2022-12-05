<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Form\User;

use DR\Review\Entity\User\UserSetting;
use DR\Review\Form\User\UserSettingType;
use DR\Review\Tests\AbstractTestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @coversDefaultClass \DR\Review\Form\User\UserSettingType
 */
class UserSettingTypeTest extends AbstractTestCase
{
    /**
     * @covers ::buildForm
     */
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects(self::exactly(4))
            ->method('add')
            ->withConsecutive(
                ['colorTheme', ChoiceType::class],
                ['mailCommentAdded', CheckboxType::class],
                ['mailCommentReplied', CheckboxType::class],
                ['mailCommentResolved', CheckboxType::class],
            )->willReturnSelf();

        $type = new UserSettingType();
        $type->buildForm($builder, []);
    }

    /**
     * @covers ::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $resolver     = new OptionsResolver();
        $introspector = new OptionsResolverIntrospector($resolver);

        $type = new UserSettingType();
        $type->configureOptions($resolver);

        static::assertSame(UserSetting::class, $introspector->getDefault('data_class'));
    }
}
