<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\User;

use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Security\Role\Roles;
use DR\GitCommitNotification\Transformer\UserProfileRoleTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserProfileType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'roles',
            ChoiceType::class,
            [
                'required' => true,
                'label'    => false,
                'choices'  => array_flip(Roles::PROFILE_NAMES),
                'attr'     => ['class' => 'd-none', 'data-role' => 'user-profile']
            ]
        );
        $builder->get('roles')->addModelTransformer(new UserProfileRoleTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
