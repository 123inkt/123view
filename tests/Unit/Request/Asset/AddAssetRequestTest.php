<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Asset;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Entity\Asset\Asset;
use DR\Review\Request\Asset\AddAssetRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @extends AbstractRequestTestCase<AddAssetRequest>
 */
#[CoversClass(AddAssetRequest::class)]
class AddAssetRequestTest extends AbstractRequestTestCase
{
    public function testGetMimeType(): void
    {
        $this->request->request->set('mimeType', 'mime-type');
        static::assertSame('mime-type', $this->validatedRequest->getMimeType());
    }

    public function testGetData(): void
    {
        $this->request->request->set('data', base64_encode('foobar'));
        static::assertSame('foobar', $this->validatedRequest->getData());
    }

    public function testGetDataInvalidBase64Decode(): void
    {
        $this->request->request->set('data', '#foobar');
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Data is not a valid base64 encoded string');
        $this->validatedRequest->getData();
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(
            [
                'request' => [
                    'mimeType' => 'required|string|in:' . implode(',', Asset::ALLOWED_MIMES),
                    'data'     => 'required|string|max:' . Asset::MAX_DATA_SIZE
                ]
            ]
        );
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return AddAssetRequest::class;
    }
}
