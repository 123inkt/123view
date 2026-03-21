<?php
declare(strict_types=1);

namespace DR\Review\Form\User;

use DR\Review\Controller\App\Admin\ChangeUserProfileController;
use DR\Review\Entity\User\User;
use DR\Review\Security\Role\Roles;
use DR\Review\Transformer\UserProfileRoleTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
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
        $builder->setMethod(Request::METHOD_POST);
        $builder->add(
            'roles',
            ChoiceType::class,
            [
                'required' => true,
                'label'    => false,
                'choices'  => array_flip(Roles::PROFILE_NAMES),
                'attr'     => ['data-controller' => 'form-submitter'],
            ]
        );
        $builder->get('roles')->addModelTransformer(new UserProfileRoleTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['user' => null, 'data_class' => User::class]);
        $resolver->addAllowedTypes('user', User::class);
    }
}
