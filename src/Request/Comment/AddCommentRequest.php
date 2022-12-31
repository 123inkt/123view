<?php
declare(strict_types=1);

namespace DR\Review\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Entity\Review\LineReference;

class AddCommentRequest extends AbstractValidatedRequest
{
    public function getLineReference(): LineReference
    {
        return new LineReference(
            (string)$this->request->query->get('filePath'),
            $this->request->query->getInt('line'),
            $this->request->query->getInt('offset'),
            $this->request->query->getInt('lineAfter'),
        );
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(
            [
                'query' => [
                    'filePath'  => 'required|string|filled',
                    'line'      => 'required|integer:min:1',
                    'offset'    => 'required|integer:min:0',
                    'lineAfter' => 'required|integer:min:1'
                ]
            ]
        );
    }
}
