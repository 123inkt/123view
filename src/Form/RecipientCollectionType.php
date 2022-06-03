<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form;

use DR\GitCommitNotification\Entity\Recipient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RecipientCollectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'entry_type'   => RecipientType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'prototype'    => true,
                'delete_empty' => static fn(?Recipient $recipient) => $recipient?->getEmail() === null,
                'constraints'  => [new Assert\Count(['min' => 1, 'max' => 10])]
            ]
        );
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }
}
