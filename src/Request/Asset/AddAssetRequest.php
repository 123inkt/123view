<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Request\Asset;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\GitCommitNotification\Entity\Asset\Asset;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AddAssetRequest extends AbstractValidatedRequest
{
    public function getMimeType(): string
    {
        return (string)$this->request->request->get('mimeType');
    }

    public function getData(): string
    {
        // data should be base64 encoded
        $decodedData = base64_decode((string)$this->request->request->get('data'), true);
        if ($decodedData === false) {
            throw new BadRequestHttpException('Data is not a valid base64 encoded string');
        }

        return $decodedData;
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(
            [
                'request' => [
                    'mimeType' => 'required|string|in:' . implode(',', Asset::ALLOWED_MIMES),
                    'data'     => 'required|string|max:' . Asset::MAX_DATA_SIZE
                ]
            ]
        );
    }
}
