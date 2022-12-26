<?php
declare(strict_types=1);

namespace DR\Review\Form\User;

use DR\Review\Controller\Auth\LoginController;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LoginFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'username'        => null,
                'csrf_field_name' => '_csrf_token',
                'csrf_token_id'   => 'authenticate',
            ]
        );
        $resolver->addAllowedTypes('username', 'string');
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setAction($this->urlGenerator->generate(LoginController::class));
        $builder->setMethod('POST');
        $builder->add('_username', EmailType::class, ['label' => 'email']);
        $builder->add('_password', PasswordType::class, ['label' => 'password']);
        $builder->add('login', SubmitType::class, ['label' => 'login']);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
