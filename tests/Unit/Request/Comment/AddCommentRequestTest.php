<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Request\Comment\AddCommentRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;

/**
 * @extends AbstractRequestTestCase<AddCommentRequest>
 * @coversDefaultClass \DR\Review\Request\Comment\AddCommentRequest
 */
class AddCommentRequestTest extends AbstractRequestTestCase
{
    /**
     * @covers ::getLineReference
     */
    public function testGetLineReference(): void
    {
        $this->request->query->set('filePath', 'filePath');
        $this->request->query->set('line', '123');
        $this->request->query->set('offset', '456');
        $this->request->query->set('lineAfter', '789');
        static::assertEquals(new LineReference('filePath', 123, 456, 789), $this->validatedRequest->getLineReference());
    }

    /**
     * @covers ::getValidationRules
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(
            [
                'query' => [
                    'filePath'  => 'required|string|filled',
                    'line'      => 'required|integer:min:1',
                    'offset'    => 'required|integer:min:0',
                    'lineAfter' => 'required|integer:min:1'
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
