<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\Review;

use DR\GitCommitNotification\Controller\App\Review\Reviewer\AddReviewerController;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Repository\Config\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AddReviewerFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator, private UserRepository $userRepository, private User $user)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'review' => null,
                'attr'   => ['id' => 'form-add-reviewer']
            ]
        );
        $resolver->addAllowedTypes('review', CodeReview::class);
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var CodeReview $review */
        $review = $options['review'];

        $builder->setAction($this->urlGenerator->generate(AddReviewerController::class, ['id' => $review->getId()]));
        $builder->setMethod('POST');

        $choices = $this->getUserChoices($review);
        if (count($choices) > 0) {
            $builder->add('user', ChoiceType::class, [
                'required'                  => false,
                'placeholder'               => 'add.reviewer',
                'label'                     => false,
                'choice_translation_domain' => false,
                'choice_label'              => static fn(?User $user) => (string)$user?->getName(),
                'choice_value'              => static fn(?User $user) => (string)$user?->getId(),
                'choices'                   => $choices,
                'preferred_choices'         => [$this->user],
                'multiple'                  => false,
                'expanded'                  => false,
                'attr'                      => [
                    'onchange' => "document.getElementById('form-add-reviewer').submit()",
                    'class'    => 'form-select-sm'
                ]
            ]);
        }
    }

    public function getBlockPrefix(): string
    {
        return '';
    }

    /**
     * @return User[]
     */
    private function getUserChoices(CodeReview $review): array
    {
        $builder = $this->userRepository->createQueryBuilder('u');

        // filter out users already on the review
        $userIds = array_map(static fn($reviewer) => (int)$reviewer->getUser()?->getId(), $review->getReviewers()->toArray());
        if (count($userIds) > 0) {
            $builder->where($builder->expr()->notIn('u.id', $userIds));
        }

        /** @var User[] $result */
        $result = $builder->orderBy('u.name', 'ASC')->getQuery()->getResult();

        return $result;
    }
}
