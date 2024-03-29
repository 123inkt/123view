<?php
declare(strict_types=1);

namespace DR\Review\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Review\LineReferenceStateEnum;

class AddCommentRequest extends AbstractValidatedRequest
{
    public function getLineReference(): LineReference
    {
        $oldPath = $this->request->query->getString('oldPath');
        $newPath = $this->request->query->getString('newPath');

        return new LineReference(
            $oldPath === '' ? null : $oldPath,
            $newPath === '' ? null : $newPath,
            $this->request->query->getInt('line'),
            $this->request->query->getInt('offset'),
            $this->request->query->getInt('lineAfter'),
            $this->request->query->getString('headSha'),
            LineReferenceStateEnum::from($this->request->query->getString('state')),
        );
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(
            [
                'query' => [
                    'oldPath'   => 'required|string',
                    'newPath'   => 'required|string',
                    'line'      => 'required|integer:min:1',
                    'offset'    => 'required|integer:min:0',
                    'lineAfter' => 'required|integer:min:1',
                    'headSha'   => 'required|string|filled',
                    'state'     => 'required|in:' . implode(',', LineReferenceStateEnum::values())
                ]
            ]
        );
    }
}
