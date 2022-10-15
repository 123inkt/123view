<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Form\Review;

use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Repository\Config\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AddReviewerFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator, private UserRepository $userRepository, private User $user)
    {
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setAction($this->urlGenerator->generate(ReviewController::class, ['id' => 5]));
        $builder->setMethod('POST');
        $builder->add('users', ChoiceType::class, [
            'required'                  => false,
            'label'                     => false,
            'choice_translation_domain' => false,
            'choice_label'              => static fn(?User $user) => (string)$user?->getName(),
            'choice_value'              => static fn(?User $user) => (string)$user?->getId(),
            'choices'                   => $this->getUserChoices(),
            'preferred_choices'         => [$this->user],
            'multiple'                  => false,
            'expanded'                  => false,
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function getUserChoices(): array
    {
        return $this->userRepository->findBy([], ['name' => 'ASC']);
    }
}
