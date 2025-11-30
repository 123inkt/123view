<?php
declare(strict_types=1);

namespace DR\Review\Form\User;

use DR\Review\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setMethod('POST');
        $builder
            ->add(
                'name',
                TextType::class,
                ['label' => 'full.name', 'required' => true, 'constraints' => [new Length(['max' => 255])]]
            )
            ->add('email', EmailType::class, ['label' => 'email'])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly, this is read and encoded in the controller
                'mapped'      => false,
                'attr'        => ['autocomplete' => 'new-password'],
                'label'       => 'password',
                'constraints' => [
                    new NotBlank(message: 'password.is.required'),
                    new Length(
                        [
                            'min'        => 10,
                            'minMessage' => 'password.minimum.length',
                            // max length allowed by Symfony for security reasons
                            'max'        => 4096,
                        ]
                    ),
                ],
            ])
            ->add('register', SubmitType::class, ['label' => 'register']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class]);
    }
}
