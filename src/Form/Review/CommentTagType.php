<?php
declare(strict_types=1);

namespace DR\Review\Form\Review;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
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
                'getter'   => static function (object $comment): string {
                    assert($comment instanceof Comment || $comment instanceof CommentReply);
                    return $comment->getTag()?->value ?? '';
                },
                'setter'   => static function (object $comment, string $value): void {
                    assert($comment instanceof Comment || $comment instanceof CommentReply);
                    $comment->setTag($value === '' ? null : CommentTagEnum::from($value));
                },
                'label'    => false,
                'required' => false,
                'choices'  => $choices
            ]
        );
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
