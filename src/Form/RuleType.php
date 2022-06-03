<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form;

use DR\GitCommitNotification\Entity\Recipient;
use DR\GitCommitNotification\Entity\Rule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RuleType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, ['required' => true]);
        $builder->add('active', CheckboxType::class, ['required' => false]);
        $builder->add('ruleOptions', RuleOptionsType::class);

        // recipient
        $builder->add(
            'recipients',
            CollectionType::class,
            [
                'entry_type' => RecipientType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => static fn(?Recipient $recipient) => $recipient?->getEmail() === null,
                'constraints' => [new Assert\Count(['min' => 1])]
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Rule::class,]);
    }
}
