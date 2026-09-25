<?php
declare(strict_types=1);

namespace DR\Review\Form\Review;

use DR\Review\Controller\App\Review\Reviewer\AddReviewerController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\User\UserEntityProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AddReviewerFormType extends AbstractType
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UserRepository $userRepository,
        private UserEntityProvider $userProvider
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['review' => null]);
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
        $builder->setMethod(Request::METHOD_POST);

        $choices = $this->getUserChoices($review);
        if (count($choices) > 0) {
            $builder->add('user', ChoiceType::class, [
                'required'                  => false,
                'placeholder'               => 'add.reviewer',
                'label'                     => false,
                'choice_translation_domain' => false,
                'choice_label'              => static fn(?User $user) => (string)$user?->getName(),
                'choice_value'              => static fn(?User $user) => (string)$user?->getId(),
                'choice_attr'               => static fn() => ['class' => 'user'],
                'choices'                   => $choices,
                'preferred_choices'         => [$this->userProvider->getCurrentUser()],
                'multiple'                  => false,
                'expanded'                  => false,
                'attr'                      => ['class' => 'form-select-sm add-reviewer-form-user', 'data-controller' => 'form-submitter']
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
        // filter out users already on the review
        $userIds = array_map(static fn($reviewer) => $reviewer->getUser()->getId(), $review->getReviewers()->toArray());

        return $this->userRepository->findUsersWithExclusion($userIds);
    }
}
