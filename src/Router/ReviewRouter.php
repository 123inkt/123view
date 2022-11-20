<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Router;

use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class ReviewRouter implements RouterInterface
{
    public function __construct(private readonly RouterInterface $router)
    {
    }

    public function setContext(RequestContext $context): void
    {
        $this->router->setContext($context);
    }

    public function getContext(): RequestContext
    {
        return $this->router->getContext();
    }

    public function getRouteCollection(): RouteCollection
    {
        return $this->router->getRouteCollection();
    }

    /**
     * @inheritDoc
     */
    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        if ($name === ReviewController::class) {
            if (isset($parameters['review']) === false || $parameters['review'] instanceof CodeReview === false) {
                throw new InvalidParameterException('Missing or invalid `review` in route parameters for ReviewController');
            }

            $review                       = $parameters['review'];
            $parameters['repositoryName'] = strtolower((string)$review->getRepository()?->getName());
            $parameters['reviewId']       = $review->getProjectId();
            unset($parameters['review']);
        }

        return $this->router->generate($name, $parameters, $referenceType);
    }

    /**
     * @inheritDoc
     */
    public function match(string $pathinfo): array
    {
        return $this->router->match($pathinfo);
    }
}
