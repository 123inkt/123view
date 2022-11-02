<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\Review;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CommentType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['label' => false, 'attr' => ['autocomplete' => 'off'], 'constraints' => new Assert\Length(max: 2000)]);
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }
}
