<?php
declare(strict_types=1);

namespace DR\Review\Form\User;

use DR\Review\Doctrine\Type\ColorThemeType;
use DR\Review\Entity\User\UserSetting;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Message\Review\ReviewClosed;
use DR\Review\Message\Review\ReviewRejected;
use DR\Review\Message\Review\ReviewResumed;
use DR\Review\Message\Reviewer\ReviewerAdded;
use DR\Review\Message\Reviewer\ReviewerRemoved;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSettingType extends AbstractType
{
    public function __construct(private readonly string $ideUrlPattern)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'colorTheme',
            ChoiceType::class,
            [
                'required' => true,
                'label'    => false,
                'expanded' => true,
                'choices'  => [
                    'user.theme.auto.detect' => ColorThemeType::THEME_AUTO,
                    'user.theme.light'       => ColorThemeType::THEME_LIGHT,
                    'user.theme.dark'        => ColorThemeType::THEME_DARK,
                ]
            ]
        );
        $builder->add('mailCommentAdded', CheckboxType::class, ['required' => false, 'label' => 'form.label.mail.comment.added']);
        $builder->add('mailCommentReplied', CheckboxType::class, ['required' => false, 'label' => 'form.label.mail.comment.replied']);
        $builder->add('mailCommentResolved', CheckboxType::class, ['required' => false, 'label' => 'form.label.mail.comment.resolved']);
        $builder->add(
            'browserNotificationEvents',
            ChoiceType::class,
            [
                'label'       => false,
                'choices'     => [
                    'notification.comment.added'       => CommentAdded::NAME,
                    'notification.comment.reply.added' => CommentReplyAdded::NAME,
                    'notification.comment.resolved'    => CommentResolved::NAME,
                    'notification.reviewer.added'      => ReviewerAdded::NAME,
                    'notification.reviewer.removed'    => ReviewerRemoved::NAME,
                    'notification.review.accepted'     => ReviewAccepted::NAME,
                    'notification.review.rejected'     => ReviewRejected::NAME,
                    'notification.review.closed'       => ReviewClosed::NAME,
                    'notification.review.resumed'      => ReviewResumed::NAME
                ],
                'choice_attr' => static fn() => ['data-role' => 'notification-event', 'disabled' => true],
                'expanded'    => true,
                'multiple'    => true,
            ]
        );
        $builder->add(
            'ideUrl',
            TextType::class,
            [
                'required'  => false,
                'label'     => 'form.label.ide.url',
                'help'      => 'form.help.ide.url',
                'help_html' => true,
                'help_attr' => ['class' => 'form-text-visible'],
                'attr'      => ['placeholder' => $this->ideUrlPattern]
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => UserSetting::class]);
    }
}
