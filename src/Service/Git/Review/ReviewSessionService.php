<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Review;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Security\SessionKeys;
use DR\Utils\Assert;
use Symfony\Component\HttpFoundation\RequestStack;

class ReviewSessionService
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function getDiffComparePolicyForUser(): DiffComparePolicy
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return DiffComparePolicy::ALL;
        }

        $policy = null;
        if ($request->hasSession()) {
            $policy = $request->getSession()->get(SessionKeys::DIFF_COMPARISON_POLICY->value);
        }
        if ($policy === null) {
            return DiffComparePolicy::ALL;
        }

        return DiffComparePolicy::from(Assert::string($policy));
    }
}
