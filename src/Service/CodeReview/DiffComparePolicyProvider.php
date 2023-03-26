<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Security\SessionKeys;
use Symfony\Component\HttpFoundation\RequestStack;

class DiffComparePolicyProvider
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function getComparePolicy(): DiffComparePolicy
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return DiffComparePolicy::ALL;
        }

        $value = $request->getSession()->get(SessionKeys::DIFF_COMPARISON_POLICY->value);
        if (is_string($value) === false) {
            return DiffComparePolicy::ALL;
        }

        return DiffComparePolicy::tryFrom($value) ?? DiffComparePolicy::ALL;
    }
}
