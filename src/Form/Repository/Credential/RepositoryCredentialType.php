<?php
declare(strict_types=1);

namespace DR\Review\Form\Repository\Credential;

use DR\Review\Doctrine\Type\AuthenticationType;
use DR\Review\Entity\Repository\RepositoryCredential;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<RepositoryCredential>
 */
class RepositoryCredentialType extends AbstractType
{
    public function __construct(private readonly CredentialTypeListener $listener)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, ['label' => 'name']);
        $builder->add(
            'authType',
            ChoiceType::class,
            [
                'label'   => 'authentication.type',
                'choices' => [
                    'auth.type.basic-auth' => AuthenticationType::BASIC_AUTH,
                    'auth.type.ssh-key'    => AuthenticationType::SSH_KEY,
                ],
            ]
        );
        $builder->addEventListener(FormEvents::PRE_SET_DATA, $this->listener);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, $this->listener);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => RepositoryCredential::class]);
    }
}
