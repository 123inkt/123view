<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\User;

use DR\GitCommitNotification\Controller\App\User\ChangeUserProfileController;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Security\Role\Roles;
use DR\GitCommitNotification\Transformer\UserProfileRoleTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserProfileFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var User $user */
        $user = $options['user'];

        $builder->setAction($this->urlGenerator->generate(ChangeUserProfileController::class, ['id' => $user->getId()]));
        $builder->setMethod('post');
        $builder->add(
            'roles',
            ChoiceType::class,
            [
                'required' => true,
                'label'    => false,
                'choices'  => array_flip(Roles::PROFILE_NAMES),
            ]
        );
        $builder->get('roles')->addModelTransformer(new UserProfileRoleTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'attr' => ['data-controller' => 'FormSubmitter'],
                'user' => null,
                'data_class' => User::class,
            ]
        );
        $resolver->addAllowedTypes('user', User::class);
    }
}
