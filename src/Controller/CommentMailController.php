<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\NotificationStatus;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentMailController extends AbstractController
{
    public function __construct(private readonly CommentRepository $repository) { }

    #[Route('app/mail/{id<\d+>}', name: self::class, methods: 'GET')]
    //#[Template('mail//rules.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('comment')]
    public function __invoke(
        Comment $comment
    ): Response {
        $comment->getNotificationStatus()->addStatus(NotificationStatus::STATUS_CREATED);
        $this->repository->save($comment, true);

        return new Response('saved');
    }
}
