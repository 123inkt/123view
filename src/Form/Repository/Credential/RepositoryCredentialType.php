<?php
declare(strict_types=1);

namespace DR\Review\Form\Repository\Credential;

use DR\Review\Doctrine\Type\AuthenticationType;
use DR\Review\Entity\Repository\RepositoryCredential;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepositoryCredentialType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, ['label' => 'name']);
        $builder->add(
            'authType',
            ChoiceType::class,
            ['label' => 'authentication.type', 'choices' => ['BasicAuth' => AuthenticationType::BASIC_AUTH]]
        );
        $builder->add('credentials', BasicAuthCredentialType::class, ['label' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => RepositoryCredential::class,]);
    }
}
