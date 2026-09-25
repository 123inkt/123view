<?php
declare(strict_types=1);

namespace DR\Review\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginFormType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'username'        => null,
                'csrf_field_name' => '_csrf_token',
                'csrf_token_id'   => 'authenticate',
                'targetPath'      => null,
            ]
        );
        $resolver->addAllowedTypes('username', 'string');
        $resolver->addAllowedTypes('targetPath', ['string', 'null']);
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod(Request::METHOD_POST);
        $builder->add('_username', EmailType::class, ['label' => 'email', 'data' => $options['username'] ?? '']);
        $builder->add('_password', PasswordType::class, ['label' => 'password']);
        if (isset($options['targetPath'])) {
            $builder->add('_target_path', HiddenType::class, ['data' => $options['targetPath']]);
        }
        $builder->add('loginBtn', SubmitType::class, ['label' => 'login']);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
