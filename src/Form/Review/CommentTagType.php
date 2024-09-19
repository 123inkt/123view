<?php
declare(strict_types=1);

namespace DR\Review\Form\Review;

use DR\Review\Entity\Review\CommentTagEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentTagType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $choices = [$this->translator->trans('tag.none') => ''];
        foreach (CommentTagEnum::cases() as $tag) {
            $choices[$this->translator->trans('tag.' . strtolower($tag->value))] = $tag->value;
        }

        $resolver->setDefaults(
            [
                'label'       => false,
                'required'    => false,
                'placeholder' => 'tag.none',
                'choices'     => $choices
            ]
        );
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
