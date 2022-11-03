<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\User;

use DR\GitCommitNotification\Entity\Config\UserSetting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSettingType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('mailCommentAdded', CheckboxType::class, ['required' => false]);
        $builder->add('mailCommentReplied', CheckboxType::class, ['required' => false]);
        $builder->add('mailCommentResolved', CheckboxType::class, ['required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => UserSetting::class]);
    }
}
