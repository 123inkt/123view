<?php
declare(strict_types=1);

namespace DR\Review\Form\Review;

use DR\Review\Entity\Review\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CommentType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'label'       => false,
                'attr'        => ['autocomplete' => 'off', 'maxlength' => Comment::MAX_COMMENT_LENGTH],
                'constraints' => new Assert\Length(max: Comment::MAX_COMMENT_LENGTH)
            ]
        );
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }
}
