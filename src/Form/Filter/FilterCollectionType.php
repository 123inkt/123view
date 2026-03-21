<?php
declare(strict_types=1);

namespace DR\Review\Form\Filter;

use DR\Review\Entity\Notification\Filter;
use DR\Review\Validator\Filter\IsValidPattern;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<Filter[]>
 */
class FilterCollectionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'entry_type'    => FilterType::class,
                'entry_options' => ['constraints' => new IsValidPattern()],
                'allow_add'     => true,
                'allow_delete'  => true,
                'prototype'     => true,
                'delete_empty'  => static fn(?Filter $filter) => $filter?->getPattern() === null,
                'constraints'   => [new Assert\Count(max: 20, maxMessage: 'At most {{ limit }} filters can be set')]
            ]
        );
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }
}
