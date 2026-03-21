<?php
declare(strict_types=1);

namespace DR\Review\Request\Review;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\Constraint\RequestConstraintFactory;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Model\Review\Action\AbstractReviewAction;
use DR\Review\Service\CodeReview\Activity\CodeReviewActionFactory;
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
                    'action'           => 'string'
                ]
            ]
        );
    }
}
