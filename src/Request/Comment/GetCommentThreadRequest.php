<?php
declare(strict_types=1);

namespace DR\Review\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\Constraint\RequestConstraintFactory;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Model\Review\Action\AbstractReviewAction;
use DR\Review\Service\CodeReview\Activity\CodeReviewActionFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GetCommentThreadRequest extends AbstractValidatedRequest
{
    public function __construct(
        private readonly CodeReviewActionFactory $actionFactory,
        RequestStack $requestStack,
        ValidatorInterface $validator,
        RequestConstraintFactory $constraintFactory
    ) {
        parent::__construct($requestStack, $validator, $constraintFactory);
    }

    public function getAction(): ?AbstractReviewAction
    {
        return $this->actionFactory->createFromRequest($this->request);
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(['query' => ['action' => 'string']]);
    }
}
