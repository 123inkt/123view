<?php
declare(strict_types=1);

namespace DR\Review\Form\Review;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CommentType extends AbstractType
{
    private const int MAX_LENGTH = 2000;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'label'       => false,
                'attr'        => ['autocomplete' => 'off', 'maxlength' => self::MAX_LENGTH],
                'constraints' => new Assert\Length(max: self::MAX_LENGTH)
            ]
        );
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }
}
