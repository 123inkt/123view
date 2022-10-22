<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Doctrine\Type\CodeReviewerStateType;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Form\Review\AddReviewerFormType;
use DR\GitCommitNotification\Message\ReviewerAdded;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class AddReviewerController extends AbstractController
{
    public function __construct(private CodeReviewRepository $codeReviewRepository, private MessageBusInterface $bus)
    {
    }

    #[Route('app/reviews/{id<\d+>}/reviewer', name: self::class, methods: 'POST')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review')]
    public function __invoke(Request $request, CodeReview $review): RedirectResponse
    {
        $form = $this->createForm(AddReviewerFormType::class, null, ['review' => $review]);
        $form->handleRequest($request);
        if ($form->isValid() === false) {
            return $this->refererRedirect(ReviewController::class, ['id' => $review->getId()]);
        }

        /** @var array<string, User|null> $data */
        $data = $form->getData();
        $user = $data['user'] ?? null;
        if ($user instanceof User === false) {
            return $this->refererRedirect(ReviewController::class, ['id' => $review->getId()]);
        }

        $reviewer = new CodeReviewer();
        $reviewer->setUser($user);
        $reviewer->setState(CodeReviewerStateType::OPEN);
        $reviewer->setStateTimestamp(time());
        $reviewer->setReview($review);
        $review->getReviewers()->add($reviewer);

        $this->codeReviewRepository->save($review, true);

        $this->bus->dispatch(new ReviewerAdded((int)$review->getId(), (int)$user->getId()));

        return $this->refererRedirect(ReviewController::class, ['id' => $review->getId()]);
    }
}
