<?php
declare(strict_types=1);

namespace DR\Review\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginFormType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod(Request::METHOD_POST);
        $builder->add('username', EmailType::class, ['label' => 'email', 'attr' => ['autofocus' => true]]);
        $builder->add('password', PasswordType::class, ['label' => 'password']);
        $builder->add('loginBtn', SubmitType::class, ['label' => 'login']);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
