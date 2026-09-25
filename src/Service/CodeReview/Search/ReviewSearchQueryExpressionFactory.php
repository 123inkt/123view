<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Search;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\Expr;
use DR\Review\QueryParser\Term\Match\MatchFilter;
use DR\Review\QueryParser\Term\Match\MatchWord;
use DR\Review\QueryParser\Term\TermInterface;
use DR\Review\Repository\Expression\QueryExpressionFactory;
use DR\Review\Service\User\UserEntityProvider;

/**
 * @phpstan-import-type Params from QueryExpressionFactory
 */
class ReviewSearchQueryExpressionFactory extends QueryExpressionFactory
{
    private int $uniqueId = 0;

    public function __construct(private readonly UserEntityProvider $userProvider)
    {
        parent::__construct(
            [
                [$this, 'createReviewIdExpression'],
                [$this, 'createReviewStateExpression'],
                [$this, 'createReviewAuthorExpression'],
                [$this, 'createReviewReviewerExpression'],
                [$this, 'createSearchExpression'],
            ]
        );
    }

    /**
     * @phpstan-param Params $parameters
     */
    public function createReviewIdExpression(TermInterface $term, Collection $parameters): ?Expr\Comparison
    {
        if ($term instanceof MatchFilter === false || $term->prefix !== 'id') {
            return null;
        }

        $key = 'projectId' . $this->getNextUniqId();

        $parameters->set($key, $term->value);

        return new Expr\Comparison('r.projectId', '=', ':' . $key);
    }

    /**
     * @phpstan-param Params $parameters
     */
    public function createReviewStateExpression(TermInterface $term, Collection $parameters): ?Expr\Comparison
    {
        if ($term instanceof MatchFilter === false || $term->prefix !== 'state') {
            return null;
        }

        $key = 'state' . $this->getNextUniqId();

        $parameters->set($key, $term->value);

        return new Expr\Comparison('r.state', '=', ':' . $key);
    }

    /**
     * @phpstan-param Params $parameters
     */
    public function createReviewAuthorExpression(TermInterface $term, Collection $parameters): Expr\Comparison|Expr\Orx|null
    {
        if ($term instanceof MatchFilter === false || $term->prefix !== 'author') {
            return null;
        }

        $key = 'authorEmail' . $this->getNextUniqId();

        $user = $this->userProvider->getUser();
        if (strcasecmp($term->value, 'me') === 0 && $user !== null) {
            $parameters->set($key, $user->getEmail());

            return new Expr\Comparison('rv.authorEmail', '=', ':' . $key);
        }

        $parameters->set($key, '%' . addcslashes($term->value, '%_') . '%');

        return new Expr\Orx(
            [
                new Expr\Comparison('rv.authorEmail', 'LIKE', ':' . $key),
                new Expr\Comparison('rv.authorName', 'LIKE', ':' . $key)
            ]
        );
    }

    /**
     * @phpstan-param Params $parameters
     */
    public function createReviewReviewerExpression(TermInterface $term, Collection $parameters): Expr\Comparison|Expr\Orx|null
    {
        if ($term instanceof MatchFilter === false || $term->prefix !== 'reviewer') {
            return null;
        }

        $key = 'reviewerEmail' . $this->getNextUniqId();

        $user = $this->userProvider->getUser();
        if (strcasecmp($term->value, 'me') === 0 && $user !== null) {
            $parameters->set($key, $user->getEmail());

            return new Expr\Comparison('u.email', '=', ':' . $key);
        }

        $parameters->set($key, '%' . addcslashes($term->value, '%_') . '%');

        return new Expr\Orx(
            [
                new Expr\Comparison('u.email', 'LIKE', ':' . $key),
                new Expr\Comparison('u.name', 'LIKE', ':' . $key)
            ]
        );
    }

    /**
     * @phpstan-param Params $parameters
     */
    public function createSearchExpression(TermInterface $term, Collection $parameters): Expr\Comparison|Expr\Orx|null
    {
        if ($term instanceof MatchWord === false) {
            return null;
        }

        $projectIdKey = 'projectId' . $this->getNextUniqId();
        $titleKey     = 'title' . $this->getNextUniqId();

        $parameters->set($titleKey, '%' . addcslashes($term->query, "%_") . '%');

        if (preg_match('/^\d+$/', $term->query) !== 1) {
            return new Expr\Comparison('r.title', 'LIKE', ':' . $titleKey);
        }

        $parameters->set($projectIdKey, $term->query);

        return new Expr\Orx(
            [
                new Expr\Comparison('r.projectId', '=', ':' . $projectIdKey),
                new Expr\Comparison('r.title', 'LIKE', ':' . $titleKey),
            ]
        );
    }

    private function getNextUniqId(): int
    {
        ++$this->uniqueId;

        return $this->uniqueId;
    }
}
