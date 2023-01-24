<?php
declare(strict_types=1);

namespace DR\Review\Request\Review;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\Constraint\RequestConstraintFactory;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Model\Review\Action\AbstractReviewAction;
use DR\Review\Security\SessionKeys;
use DR\Review\Service\CodeReview\Activity\CodeReviewActionFactory;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModel\App\Review\ReviewViewModel;
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

        return ReviewDiffModeEnum::from($mode);
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
                    'filePath' => 'string|filled',
                    'tab'      => 'string|in:' . ReviewViewModel::SIDEBAR_TAB_REVISIONS . ',' . ReviewViewModel::SIDEBAR_TAB_OVERVIEW,
                    'diff'     => 'string|in:' . implode(',', ReviewDiffModeEnum::values()),
                    'action'   => 'string'
                ]
            ]
        );
    }
}
