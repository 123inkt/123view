<?php
declare(strict_types=1);

namespace DR\Review\Form\Review;

use DR\Review\Controller\App\Review\Reviewer\AddReviewerController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Service\Git\Branch\CacheableGitBranchService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

class ChangeBranchReviewBranchFormType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator, private CacheableGitBranchService $branchService)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['review' => null]);
        $resolver->addAllowedTypes('review', CodeReview::class);
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var CodeReview $review */
        $review = $options['review'];

        $builder->setAction($this->urlGenerator->generate(AddReviewerController::class, ['id' => $review->getId()]));
        $builder->setMethod('POST');

        $builder->add('branch', ChoiceType::class, [
            'required'          => false,
            'label'             => false,
            'choices'           => $this->getBranches($review),
            'preferred_choices' => [$review->getTargetBranch()],
            'multiple'          => false,
            'expanded'          => false,
            'attr'              => ['class' => 'form-select-sm d-inline-block w-auto', 'data-controller' => 'form-submitter']
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }

    /**
     * @return array<string, string>
     * @throws Throwable
     */
    private function getBranches(CodeReview $review): array
    {
        $branches = $this->branchService->getRemoteBranches($review->getRepository());

        // remove origin/ prefix
        $branches = array_map(static fn(string $branch): string => str_replace('origin/', '', $branch), $branches);

        // filter out HEAD
        $branches = array_filter($branches, static fn(string $branch): bool => $branch !== 'HEAD');

        return array_combine($branches, $branches);
    }
}
