<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Form\User;

use DR\GitCommitNotification\Entity\User\UserSetting;
use DR\GitCommitNotification\Form\User\UserSettingType;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Debug\OptionsResolverIntrospector;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Form\User\UserSettingType
 * @covers ::__construct
 */
class UserSettingTypeTest extends AbstractTestCase
{
    /**
     * @covers ::buildForm
     */
    public function testBuildForm(): void
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects(self::exactly(3))
            ->method('add')
            ->withConsecutive(
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
