<?php
declare(strict_types=1);

namespace DR\Review\Request\Review;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\Constraint\RequestConstraintFactory;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Model\Review\Action\AbstractReviewAction;
use DR\Review\Security\SessionKeys;
use DR\Review\Service\CodeReview\Activity\CodeReviewActionFactory;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
use DR\Utils\Assert;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReviewRequest extends AbstractValidatedRequest
{
    public function __construct(
        private readonly CodeReviewActionFactory $actionFactory,
        RequestStack $requestStack,
        ValidatorInterface $validator,
        RequestConstraintFactory $constraintFactory
    ) {
        parent::__construct($requestStack, $validator, $constraintFactory);
    }

    public function getFilePath(): ?string
    {
        return $this->request->query->get('filePath');
    }

    public function getTab(): string
    {
        return $this->request->query->get('tab', ReviewViewModel::SIDEBAR_TAB_OVERVIEW);
    }

    public function getVisibleLines(): ?int
    {
        $visibleLines = $this->request->query->get('visibleLines');
        if ($visibleLines === null && $this->request->hasSession()) {
            $visibleLines = $this->request->getSession()->get(SessionKeys::DIFF_VISIBLE_LINES->value);
        }
        if (is_numeric($visibleLines)) {
            $visibleLines = (int)$visibleLines;
        }

        return $visibleLines;
    }

    public function getComparisonPolicy(): DiffComparePolicy
    {
        $policy = $this->request->query->get('comparisonPolicy');
        if ($policy === null && $this->request->hasSession()) {
            $policy = $this->request->getSession()->get(SessionKeys::DIFF_COMPARISON_POLICY->value);
        }
        if ($policy === null) {
            return DiffComparePolicy::ALL;
        }

        $this->request->getSession()->set(SessionKeys::DIFF_COMPARISON_POLICY->value, $policy);

        return DiffComparePolicy::from(Assert::string($policy));
    }

    public function getDiffMode(): ReviewDiffModeEnum
    {
        $mode = $this->request->query->get('diff');
        if ($mode === null && $this->request->hasSession()) {
            $mode = $this->request->getSession()->get(SessionKeys::REVIEW_DIFF_MODE->value);
        }
        if ($mode === null) {
            return ReviewDiffModeEnum::INLINE;
        }

        $this->request->getSession()->set(SessionKeys::REVIEW_DIFF_MODE->value, $mode);

        return ReviewDiffModeEnum::from(Assert::string($mode));
    }

    public function getAction(): ?AbstractReviewAction
    {
        return $this->actionFactory->createFromRequest($this->request);
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(
            [
                'query' => [
                    'filePath'         => 'string|filled',
                    'tab'              => 'string|in:' . ReviewViewModel::SIDEBAR_TAB_REVISIONS . ',' . ReviewViewModel::SIDEBAR_TAB_OVERVIEW,
                    'comparisonPolicy' => 'string|in:' . implode(',', DiffComparePolicy::values()),
                    'diff'             => 'string|in:' . implode(',', ReviewDiffModeEnum::values()),
                    'visibleLines'     => 'int|min:0',
                    'action'           => 'string'
                ]
            ]
        );
    }
}
