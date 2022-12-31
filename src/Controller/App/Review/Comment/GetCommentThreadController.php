<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Entity\Review\Comment;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GetCommentThreadController
{
    /**
     * @return array<string, bool|object|null>
     */
    #[Route('app/comments/{id<\d+>}', name: self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    #[Template('app/review/comment/comment.html.twig')]
    public function __invoke(#[MapEntity] Comment $comment): array
    {
        return [
            'comment'          => $comment,
            'detached'         => false,
            'editCommentForm'  => null,
            'replyCommentForm' => null,
        ];
    }
}
