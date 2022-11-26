<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\User;

use DR\GitCommitNotification\Doctrine\Type\ColorThemeType;
use DR\GitCommitNotification\Entity\User\UserSetting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSettingType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'colorTheme',
            ChoiceType::class,
            [
                'required' => true,
                'label'    => false,
                'expanded' => true,
                'choices'  => [
                    'user.theme.auto.detect' => ColorThemeType::THEME_AUTO,
                    'user.theme.light'       => ColorThemeType::THEME_LIGHT,
                    'user.theme.dark'        => ColorThemeType::THEME_DARK,
                ]
            ]
        );
        $builder->add('mailCommentAdded', CheckboxType::class, ['required' => false, 'label' => 'form.label.mail.comment.added']);
        $builder->add('mailCommentReplied', CheckboxType::class, ['required' => false, 'label' => 'form.label.mail.comment.replied']);
        $builder->add('mailCommentResolved', CheckboxType::class, ['required' => false, 'label' => 'form.label.mail.comment.resolved']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => UserSetting::class]);
    }
}
