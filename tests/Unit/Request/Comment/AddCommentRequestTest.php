<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Review\LineReferenceStateEnum;
use DR\Review\Request\Comment\AddCommentRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<AddCommentRequest>
 */
#[CoversClass(AddCommentRequest::class)]
class AddCommentRequestTest extends AbstractRequestTestCase
{
    public function testGetLineReference(): void
    {
        $this->request->query->set('oldPath', 'oldPath');
        $this->request->query->set('newPath', 'newPath');
        $this->request->query->set('line', '123');
        $this->request->query->set('offset', '456');
        $this->request->query->set('lineAfter', '789');
        $this->request->query->set('headSha', 'sha');
        $this->request->query->set('state', 'A');
        static::assertEquals(
            new LineReference('oldPath', 'newPath', 123, 456, 789, 'sha', LineReferenceStateEnum::Added),
            $this->validatedRequest->getLineReference()
        );
    }

    public function testGetLineReferenceWithSameFilePath(): void
    {
        $this->request->query->set('oldPath', '');
        $this->request->query->set('newPath', 'path');
        $this->request->query->set('line', '123');
        $this->request->query->set('offset', '456');
        $this->request->query->set('lineAfter', '789');
        $this->request->query->set('state', 'A');
        static::assertEquals(
            new LineReference(null, 'path', 123, 456, 789, null, LineReferenceStateEnum::Added),
            $this->validatedRequest->getLineReference()
        );
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(
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
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return AddCommentRequest::class;
    }
}
