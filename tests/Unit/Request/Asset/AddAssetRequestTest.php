<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Request\Asset;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\GitCommitNotification\Entity\Asset\Asset;
use DR\GitCommitNotification\Request\Asset\AddAssetRequest;
use DR\GitCommitNotification\Tests\Unit\Request\AbstractRequestTestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @extends AbstractRequestTestCase<AddAssetRequest>
 * @coversDefaultClass \DR\GitCommitNotification\Request\Asset\AddAssetRequest
 * @covers ::__construct
 */
class AddAssetRequestTest extends AbstractRequestTestCase
{
    /**
     * @covers ::getMimeType
     */
    public function testGetMimeType(): void
    {
        $this->request->request->set('mimeType', 'mime-type');
        static::assertSame('mime-type', $this->validatedRequest->getMimeType());
    }

    /**
     * @covers ::getData
     */
    public function testGetData(): void
    {
        $this->request->request->set('data', base64_encode('foobar'));
        static::assertSame('foobar', $this->validatedRequest->getData());
    }

    /**
     * @covers ::getData
     */
    public function testGetDataInvalidBase64Decode(): void
    {
        $this->request->request->set('data', '#foobar');
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Data is not a valid base64 encoded string');
        $this->validatedRequest->getData();
    }

    /**
     * @covers ::getValidationRules
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
