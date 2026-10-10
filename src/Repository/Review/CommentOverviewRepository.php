<?php
declare(strict_types=1);

namespace DR\Review\Repository\Review;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\ViewModel\App\Comment\CommentOverviewItem;
use DR\Utils\Assert;

class CommentOverviewRepository
{
    public const int PAGE_SIZE = 30;
    public const string ORDER_CREATE_TIMESTAMP = 'create-timestamp';
    public const string ORDER_UPDATE_TIMESTAMP = 'update-timestamp';

    private readonly CommentRepository $commentRepository;
    private readonly CommentReplyRepository $commentReplyRepository;
    private readonly Connection $connection;

    public function __construct(ManagerRegistry $registry, CommentRepository $commentRepository, CommentReplyRepository $commentReplyRepository)
    {
        $this->connection             = Assert::isInstanceOf($registry->getConnection(), Connection::class);
        $this->commentRepository      = $commentRepository;
        $this->commentReplyRepository = $commentReplyRepository;
    }

    /**
     * @return array{items: list<CommentOverviewItem>, total: int}
     */
    public function getByUser(User $user, int $page, string $search, string $orderBy): array
    {
        $page       = max(1, $page);
        $search     = trim($search);
        $parameters = ['userId' => $user->getId()];

        if ($search !== '') {
            $parameters['search'] = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        }

        $unionQuery = $this->getUnionQuery($search !== '');
        $connection = $this->connection;
        $total      = $this->getInteger($connection->executeQuery(
            'SELECT COUNT(*) FROM (' . $unionQuery . ') overview',
            $parameters
        )->fetchOne());

        $orderColumn = $orderBy === self::ORDER_UPDATE_TIMESTAMP ? 'update_timestamp' : 'create_timestamp';
        $offset      = ($page - 1) * self::PAGE_SIZE;
        $rows        = $connection->executeQuery(
            'SELECT item_id, item_type FROM (' . $unionQuery . ') overview
             ORDER BY overview.' . $orderColumn . ' DESC, overview.item_type ASC, overview.item_id DESC
             LIMIT ' . self::PAGE_SIZE . ' OFFSET ' . $offset,
            $parameters
        )->fetchAllAssociative();

        $commentIds = [];
        $replyIds   = [];
        foreach ($rows as $row) {
            $itemId = $this->getInteger($row['item_id']);
            if ($row['item_type'] === 'comment') {
                $commentIds[] = $itemId;
            } else {
                $replyIds[] = $itemId;
            }
        }

        /** @var Comment[] $comments */
        $comments = $this->commentRepository->findBy(['id' => $commentIds]);
        /** @var CommentReply[] $replies */
        $replies = $this->commentReplyRepository->findBy(['id' => $replyIds]);

        $commentsById = [];
        foreach ($comments as $comment) {
            $commentsById[$comment->getId()] = $comment;
        }

        $repliesById = [];
        foreach ($replies as $reply) {
            $repliesById[$reply->getId()] = $reply;
        }

        $items = [];
        foreach ($rows as $row) {
            $id = $this->getInteger($row['item_id']);
            if ($row['item_type'] === 'comment') {
                $items[] = new CommentOverviewItem(Assert::notNull($commentsById[$id] ?? null));
            } else {
                $items[] = new CommentOverviewItem(Assert::notNull($repliesById[$id] ?? null));
            }
        }

        return ['items' => $items, 'total' => $total];
    }

    private function getUnionQuery(bool $hasSearch): string
    {
        $searchFilter = $hasSearch ? " AND c.message LIKE :search ESCAPE '!'" : '';

        return "SELECT c.id AS item_id, 'comment' AS item_type, c.create_timestamp, c.update_timestamp
                FROM comment c
                WHERE c.user_id = :userId" . $searchFilter . "
                UNION ALL
                SELECT r.id AS item_id, 'reply' AS item_type, r.create_timestamp, r.update_timestamp
                FROM comment_reply r
                WHERE r.user_id = :userId" . ($hasSearch ? " AND r.message LIKE :search ESCAPE '!'" : '');
    }

    private function getInteger(mixed $value): int
    {
        return Assert::integer(filter_var($value, FILTER_VALIDATE_INT));
    }
}
